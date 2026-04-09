<?
error_reporting(0);
ob_start();
echo "OK";
header("Connection: close");
header("Content-length: " . (string)ob_get_length());
ob_end_flush();
if (!isset($_GET["deviceid"])) {
    die;
}

//chdir('../');
include('../s_insert.php');



$mil = $_GET["fixtime"];
$seconds = $mil / 1000;
$fecha1 = date("Y-m-d H:i:s", $seconds);

$valid = $_GET["valid"];
if ($valid == "true") {
    $valid = 1;
} else {
    $valid = 0;
}

$contador = 0;
$evento = "";
$result = "";
$date = date("Y-m-d H:i:s");
$fecha_server = strtotime('-0 hour', strtotime($date));
$fecha_server = date('Y-m-j H:i:s', $fecha_server);
$speed = floor($_GET["speed"]);
$attributes = str_replace("\\", "", $_GET["attributes"]);

$eventos = json_decode($attributes, true);

if (isset($eventos['result'])) {
    $result = $eventos['result'];
} else {
    $result = 'n/a';
}


if ($contador == 0) {
    $contador++;
}


$attributes = paramsToArray($attributes);

if ($_GET['protocol'] == 'gt06') {
    $valid = 1;
}

if ($_GET['protocol'] == 'gl200') {

    if (isset($attributes['type']) && $attributes['type'] == "INF") {
        $attributes = array_intersect_key($attributes, array_flip(['type', 'raw', 'iccid']));
    }
    $imei = mysqli_real_escape_string($ms, $_GET["deviceid"]);

    $q_objects = "SELECT * FROM `gs_objects` WHERE `imei`='$imei'";
    $r_objects = mysqli_query($ms, $q_objects);
    $row_objects = mysqli_fetch_assoc($r_objects);
    $fordward_avl = $row_objects['fordward_avl'];
    $fordward_traccar = $row_objects['tail_points'];

    $envioPermitidoTraccar = false;
    if ($fordward_traccar == '1'){
        $envioPermitidoTraccar = true;
    }

    $q_user_objects = "SELECT user_id FROM gs_user_objects WHERE imei = '$imei'";
    $r_user_objects = mysqli_query($ms, $q_user_objects);

    $envioPermitido = false;
    $allowed_user_ids = array(257, 316, 320, 1079);
    while ($row_user_objects = mysqli_fetch_assoc($r_user_objects)) {
        if (in_array($row_user_objects['user_id'], $allowed_user_ids) && $fordward_avl == 'true') {
            $envioPermitido = true;
            break;
        }
    }

    if ($envioPermitidoTraccar == 'true') {

        $rawTrama = $attributes['raw'] . '240';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://localhost/server/http/forwardTCP.php?trama=" . $rawTrama . "&envio=" . $envioPermitidoTraccar);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
    }

    if ($envioPermitido) {
        $rawTrama = $attributes['raw'] . '240';
        $envio = pack("H*", $rawTrama);

        $elementos = explode(",", $envio);

        if (isset($elementos[8]) && floatval($elementos[8]) >= 81.0) {
            $elementos[8] = '80.0';
        }

        $envioModificado = implode(",", $elementos);
        $envioHex = bin2hex($envioModificado);

        $ch = curl_init();
        $url = "http://localhost/server/http/forwardTCP.php?trama=$envioHex";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
    } else if ($fordward_avl == 'true') {

        $rawTrama = $attributes['raw'] . '240';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://localhost/server/http/forwardTCP.php?trama=" . $rawTrama);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
    }
}

if ($_GET['protocol'] == 'gl200') {

    $rawTrama = $attributes['raw'];
    $envio = pack("H*", $rawTrama);
    $fw = '';

    $elementos = explode(",", $envio);
    $version = $elementos[1];
    if ($version == 'BD0219'){
        $version = 'EyesPro';
    }
    $attributes['version'] = $version;


    if (isset($attributes['type']) && $attributes['type'] == "FTP") {
        $alertadms = $elementos[5];
    
        $alertadms = pathinfo($alertadms, PATHINFO_FILENAME);
    
        $partes = explode("_", $alertadms);
    
        if (count($partes) == 4) {
            $codigoEvento = strtoupper($partes[2]);
            $idCamara = $partes[3];
    
            $attributes['alertaDMS'] = $codigoEvento . '_' . $idCamara;
    
            $valor_alertaDms = $attributes['alertaDMS'];
            
            if ($idCamara == '1') {
                alertaDms($imei, $valor_alertaDms);
            }
        } else {
            $attributes['alertaDMS'] = "Formato_invalido";
        }
    }
}

if ($_GET['protocol'] == 'teltonika') {
    function toSignedShort($v) { return ($v > 32767) ? $v - 65536 : $v; }
    $remap = [
        'io9' => 'adc1', 'io6' => 'adc2', 'io113' => 'batteryLevel',
        'io10800' => 'TempBT1', 'io10801' => 'TempBT2', 'io10802' => 'TempBT3',
        'io10804' => 'HumBT1', 'io10805' => 'HumBT2', 'io10806' => 'HumBT3',
        'io10824' => 'BatBT1', 'io10825' => 'BatBT2', 'io10826' => 'BatBT3',
        'io29' => 'BatAdcBT1', 'io20' => 'BatAdcBT2',
        'io270' => 'AdcBT1', 'io273' => 'AdcBT2',
        'bleTemp1' => 'TempAdcBT1', 'bleTemp2' => 'TempAdcBT2', 'bleTemp3' => 'TempAdcBT3',
        'io381' => 'SOS', 'io10809' => 'Door', 'io72' => 'Temp1', 'io73' => 'Temp2', 'io74' => 'Temp3', 
        'io75' => 'Temp4',

        // Nuevos OBD
        'io81' => 'vel',                    // Velocidad
        'io82' => 'pedalAcel',              // Pedal de aceleración
        'io85' => 'rpm',                    // RPM del motor
        'io84' => 'fuelLvl',                // Nivel de combustible
        'io87' => 'kmTot',                  // Kilometraje total
        'io89' => 'fuelPerc',               // % de combustible
        'io103' => 'hrsMotorOn',            // Horas con motor encendido
        'io107' => 'fuelUsed',              // Combustible consumido
        'io115' => 'tempMotor',             // Temperatura del motor
        'io653' => 'frenoMano',             // Freno de mano
        'io654' => 'doorFrIzq',             // Puerta delantera izquierda
        'io655' => 'doorFrDer',             // Puerta delantera derecha
        'io656' => 'doorTrIzq',             // Puerta trasera izquierda
        'io657' => 'doorTrDer',             // Puerta trasera derecha
        'io658' => 'maletero',              // Puerta maletero
        'io662' => 'vehCerrado',            // Coche cerrado
        'io866' => 'io866',                 // No definido
        'io910' => 'frenoPie',              // Freno de pie
        'io911' => 'embrague',              // Embrague
        'io928' => 'lucesEstac',            // Luces de estacionamiento
        'io929' => 'lucesCruce',            // Luces de cruce delanteras
        'io932' => 'lucesAntiNie',          // Luces antiniebla delanteras
        'io940' => 'cinturon',              // Cinturón de seguridad
    ];
    foreach ($remap as $from => $to) {
        if (isset($attributes[$from])) {
            $attributes[$to] = in_array($from, ['io10800', 'io10801', 'io10802']) ? toSignedShort($attributes[$from]) : $attributes[$from];
            unset($attributes[$from]);
        }
    }

    if (isset($attributes['SOS'])) {
        if ((int)$attributes['SOS'] === 1) {
            $attributes['type'] = isset($attributes['type']) && $attributes['type'] !== ''
                ? $attributes['type'] . '|SOS'
                : 'SOS';
        }
        unset($attributes['SOS']);
    }
}
if (isset($_GET['protocol']) && $_GET['protocol'] === 'ruptela') 
{
    $remap = [
        'io27'  => 'gsmSignal',
        'io28'  => 'currentProfile',
        'io70'  => 'tempSensor0Id',
        'io71'  => 'tempSensor1Id',
        'io72'  => 'tempSensor2Id',
        'io73'  => 'tempSensor3Id',
        'io178' => 'fridgeBatteryVoltage',
        'io179' => 'fridgeTotalElectricHours',
        'io180' => 'fridgeTotalVehicleHours',
        'io181' => 'fridgeTotalEngineHours',
        'io184' => 'fridgeReturnAirTemp',
        'io185' => 'fridgeDischargeAirTemp',
        'io186' => 'fridgeSetpointTemp',
        'io187' => 'fridgeEvaporatorTemp',
        'io188' => 'fridgeOperatingMode',
        'io189' => 'fridgeCycleMode',
        'io191' => 'fridgeDoorStatus',
        'io418' => 'gprsStatus',
        'io840' => 'tkCoolantTemp',
        'io841' => 'tkEngineRpm',
        'io842' => 'tkAmbientTemp',
        'io843' => 'tkZoneTemp2',
        'io844' => 'tkZoneTemp3',
        'io846' => 'tkAlarmCount',
        'io886' => 'fridgeOperatingMode2',
        'io887' => 'fridgeDischargeAirTemp2',
        'io888' => 'fridgeSetpointTemp2',
        'io902' => 'fridgeOperatingMode3',
        'io903' => 'fridgeDischargeAirTemp3',
        'io904' => 'fridgeSetpointTemp3',
    ];

    foreach ($remap as $oldKey => $newKey) {
        if (array_key_exists($oldKey, $attributes)) {
            $attributes[$newKey] = $attributes[$oldKey];
            unset($attributes[$oldKey]);
        }
    }
}
$rawValue = substr($attributes['raw'], 0, 18);
$attributes['raw'] = $rawValue;

$loc = array();
$loc['imei'] = $_GET["deviceid"];
$loc['protocol'] = $_GET["protocol"];
$loc['dt_server'] = $fecha_server;
$loc['dt_tracker'] = $fecha1;
$loc['fixtime'] = $_GET["fixtime"];
$loc['lat'] = (float)sprintf('%0.6f', $_GET["latitude"]);
$loc['lng'] = (float)sprintf('%0.6f', $_GET["longitude"]);
$loc['altitude'] = floor($_GET["altitude"]);
$loc['angle'] = floor($_GET["course"]);
$loc['speed'] = $speed * 1.852;
$loc['loc_valid'] = $valid;
$loc['params'] = $attributes;
$loc['result'] = $result;
$loc['event'] = $evento;
$loc['net_protocol'] = '';
$loc['ip'] = '';
$loc['port'] = '';
$loc['count'] = $contador;


//$loc['positionid'] = $_GET['positionid'];

if (($loc['lat'] == 0) || ($loc['lng'] == 0)) {
    $valid = 0;
} else if (($loc['lat'] == '0') || ($loc['lng'] == '0')) {
    $valid = 0;
}

//insert_db_loc($loc);
if (@$loc['loc_valid'] == 1) {
    insert_db_loc($loc);
} else if (@$loc['loc_valid'] == 0) {
    insert_db_noloc($loc);
}





// mysqli_close($ms);
die;
