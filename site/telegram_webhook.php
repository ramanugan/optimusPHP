<?php
// ============================================================
// Telegram Webhook - Optimus GPS
// Soporta: /start TOKEN, /start (sin token), TOKEN manual
// Tabla: gs_user_events (telegram_link_token, telegram_chat_id, telegram_connected_at)
// NOTA: No incluir fn_connect.php (requiere sesión de usuario)
// ============================================================

// Cargar solo lo necesario: configuración y conexión a la BD
include('init.php');
include('func/fn_common.php');

$bot_token = '8483933785:AAFb_9tgPLiWrqCMUdUAezDoL9qxFx_aCGs';

// ── Leer payload ─────────────────────────────────────────────
$content = file_get_contents("php://input");
addRowBinnacle(0, 'telegram_webhook: payload recibido', $content);

$update = json_decode($content, true);

if (!$update || !isset($update["message"])) {
    exit;
}

$message = $update["message"];
$chat_id = $message["chat"]["id"];
$text    = isset($message["text"]) ? trim($message["text"]) : '';

addRowBinnacle(0, 'telegram_webhook: texto recibido', 'chat_id=' . $chat_id . ' | text=' . $text);

// ── Extraer token del texto ───────────────────────────────────
// Casos: "/start TOKEN", "/start", "TOKEN", o "TOKEN" pegado directamente
$token_candidate = '';

// Limpiar el texto de cualquier espacio, salto de línea o carácter invisible
$clean_text = preg_replace('/[\s\x00-\x1F\x7F\xC2\xA0]/u', ' ', $text);
$clean_text = trim($clean_text);

if (stripos($clean_text, '/start') === 0) {
    // Es un comando /start, buscamos si hay algo después
    $parts = preg_split('/\s+/', $clean_text, 2);
    if (isset($parts[1]) && trim($parts[1]) !== '') {
        $token_candidate = trim($parts[1]);
    }
} else {
    // No es /start, el texto completo podría ser el token
    $token_candidate = $clean_text;
}

// ── Validar formato del token (si hay candidato) ──────────────
// Formato válido: A-Z, a-z, 0-9, _, - | longitud 6-64
$token_valid_format = false;
if ($token_candidate !== '') {
    $token_valid_format = (bool) preg_match('/^[A-Za-z0-9_\-]{6,64}$/', $token_candidate);
}

// ── Caso 1: /start sin token → pedir token manual ────────────
if ($text === '/start' || ($token_candidate === '' && strpos($text, '/start') === 0)) {
    $reply = "Hola 👋\nPara vincular tu alerta de Optimus GPS, pega aquí tu código de vinculación.";
    sendTelegramReply($reply, $chat_id, $bot_token);
    exit;
}

// ── Caso 2: texto casual (no parece un token) ───────────────
// Si el texto no tiene formato de token (muy corto, contiene espacios, etc.)
// respondemos con aviso de que el chat es solo de envío
if (!$token_valid_format) {
    $reply = "\u{1F515} Este chat es únicamente de envío de alertas automáticas.\n"
           . "El bot no procesa respuestas ni mensajes de texto.\n\n"
           . "Si deseas vincular una alerta, utiliza el enlace de vinculación desde el panel de Optimus GPS.";
    sendTelegramReply($reply, $chat_id, $bot_token);
    addRowBinnacle(0, 'telegram_webhook: mensaje casual ignorado', 'text=' . $token_candidate);
    exit;
}

// ── Caso 3: token válido → buscar en gs_user_events ──────────
// Limpiar el token: quitar espacios, saltos de línea y caracteres invisibles
$token_candidate = preg_replace('/[\x00-\x1F\x7F\xC2\xA0]/u', '', $token_candidate);
$token_candidate = trim($token_candidate);

addRowBinnacle(0, 'telegram_webhook: buscando token', 'token_hex=' . bin2hex($token_candidate) . ' | len=' . strlen($token_candidate));

$token_safe = mysqli_real_escape_string($ms, $token_candidate);
$q = "SELECT * FROM `gs_user_events` WHERE `telegram_link_token`='" . $token_safe . "' LIMIT 1";
$r = mysqli_query($ms, $q);

if (!$r) {
    $sql_error = mysqli_error($ms);
    addRowBinnacle(0, 'telegram_webhook: error SQL al buscar token', $sql_error . ' | query=' . $q);
    $reply = "El código no es válido o ya expiró.";
    sendTelegramReply($reply, $chat_id, $bot_token);
    exit;
}

// Debug: contar cuántos tokens existen en la BD
$q_debug = "SELECT COUNT(*) as total, GROUP_CONCAT(SUBSTRING(`telegram_link_token`,1,8)) as tokens_preview FROM `gs_user_events` WHERE `telegram_link_token` IS NOT NULL AND `telegram_link_token` != ''";
$r_debug = mysqli_query($ms, $q_debug);
$row_debug = $r_debug ? mysqli_fetch_assoc($r_debug) : null;
addRowBinnacle(0, 'telegram_webhook: tokens en BD', 'total=' . ($row_debug ? $row_debug['total'] : 'err') . ' | preview=' . ($row_debug ? $row_debug['tokens_preview'] : 'err'));

if ($row = mysqli_fetch_array($r)) {
    $event_id   = $row['event_id'];
    $chat_id_safe = mysqli_real_escape_string($ms, $chat_id);

    // Agregar chat_id a la lista separada por comas (sin duplicados)
    $existing_ids = array_filter(array_map('trim', explode(',', $row['telegram_chat_id'])));
    if (!in_array((string)$chat_id, $existing_ids)) {
        $existing_ids[] = (string)$chat_id;
    }
    $new_chat_ids = implode(',', $existing_ids);
    $new_chat_ids_safe = mysqli_real_escape_string($ms, $new_chat_ids);

    // Guardar chat_ids (NO limpiar el token para que otros usuarios puedan vincularse)
    $q_update = "UPDATE `gs_user_events`
                 SET `telegram_chat_id`='" . $new_chat_ids_safe . "',
                     `telegram_connected_at`=NOW()
                 WHERE `event_id`='" . $event_id . "'";
    $r_update = mysqli_query($ms, $q_update);

    if (!$r_update) {
        $sql_error = mysqli_error($ms);
        addRowBinnacle(0, 'telegram_webhook: error SQL al actualizar chat_id', $sql_error);
        $reply = "Ocurrió un error al vincular tu cuenta. Por favor intenta de nuevo.";
        sendTelegramReply($reply, $chat_id, $bot_token);
        exit;
    }

    addRowBinnacle($row['user_id'], 'telegram_webhook: vinculación exitosa', 'event_id=' . $event_id . ' | chat_id=' . $chat_id . ' | total_ids=' . count($existing_ids));

    $reply = "✅ <b>Tu alerta de Optimus GPS ha sido vinculada correctamente.</b>\n\n"
             . "🔕 <b>Aviso importante:</b> Este chat es únicamente de envío de alertas automáticas. "
             . "El bot <b>no procesa respuestas</b> de tu parte. Si contestas un mensaje, no recibirás ninguna respuesta. "
             . "Solo te notificaremos cuando se active una alerta en tu vehículo.";
    $tg_response = sendTelegramReply($reply, $chat_id, $bot_token);
    addRowBinnacle($row['user_id'], 'telegram_webhook: respuesta Telegram', $tg_response);

} else {
    addRowBinnacle(0, 'telegram_webhook: token no encontrado', 'token=' . $token_candidate);
    $reply = "El código no es válido o ya expiró.";
    sendTelegramReply($reply, $chat_id, $bot_token);
}

// ── Función de envío ──────────────────────────────────────────
function sendTelegramReply($message, $chat_id, $bot_token)
{
    $url  = "https://api.telegram.org/bot" . $bot_token . "/sendMessage";
    $data = [
        'chat_id'    => $chat_id,
        'text'       => $message,
        'parse_mode' => 'HTML'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}
?>
