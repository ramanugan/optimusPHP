<?
set_time_limit(0);

ob_start();

include('s_init.php');
include('s_events.php');
include('../func/fn_common.php');
include('../func/fn_cleanup.php');
include('../tools/gc_func.php');

$data = json_decode(file_get_contents("php://input"), true);

// #################################################
//  WILL BE DEPRECATED IN 4.0 VERSION
// #################################################

if (@$_GET["op"] == "sms_gateway_app") {
	if (!isset($_GET["identifier"])) {
		die;
	}

	if ($_GET["identifier"] == '') {
		die;
	}

	$format = strtolower(@$_GET["format"]);

	$q = "SELECT * FROM `gs_sms_gateway_app` WHERE `identifier`='" . $_GET["identifier"] . "' ORDER BY `dt_sms` ASC";
	$r = mysqli_query($ms, $q);

	if ($format == 'json') {
		$result = array();

		while ($row = mysqli_fetch_array($r)) {
			$result[] = array($row['dt_sms'], $row['number'], $row['message']);
		}

		echo json_encode($result);
	} else {
		$result = '';

		while ($row = mysqli_fetch_array($r)) {
			$result .= $row['dt_sms'] . chr(30) . $row['number'] . chr(30) . $row['message'] . chr(29);
		}

		echo $result;
	}

	$q2 = "DELETE FROM `gs_sms_gateway_app` WHERE `identifier`='" . $_GET['identifier'] . "'";
	$r2 = mysqli_query($ms, $q2);

	die;
}

if (@$_GET["op"] == "chat_new_messages") {
	$imei = $_GET["imei"];

	// get unread messages number
	$q = "SELECT * FROM `gs_object_chat` WHERE `imei`='" . $imei . "' AND `side`='S' AND `status`=0";
	$r = mysqli_query($ms, $q);
	$msg_num = mysqli_num_rows($r);

	// set messages to delivered
	$q = "UPDATE `gs_object_chat` SET `status`=1 WHERE `imei`='" . $imei . "' AND `side`='S' AND `status`=0";
	$r = mysqli_query($ms, $q);

	echo $msg_num;
	die;
}

if (@$_GET["op"] == "tasks_new") {
	$imei = $_GET["imei"];

	// get unread messages number
	$q = "SELECT * FROM `gs_object_tasks` WHERE `imei`='" . $imei . "' AND `delivered`=0";
	$r = mysqli_query($ms, $q);
	$task_num = mysqli_num_rows($r);

	// set tasks to delivered
	$q = "UPDATE `gs_object_tasks` SET `delivered`=1 WHERE `imei`='" . $imei . "' AND `delivered`=0";
	$r = mysqli_query($ms, $q);

	echo $task_num;
	die;
}

if (@$_GET["op"] == "push_new_events") {
	$identifier = $_GET["identifier"];

	// get unread messages number
	$q = "SELECT * FROM `gs_push_queue` WHERE `identifier`='" . $identifier . "' AND `type`='event' ORDER by id DESC LIMIT 1";
	$r = mysqli_query($ms, $q);
	$row = mysqli_fetch_array($r);

	if ($row) {
		echo $row['id'];
	} else {
		echo 0;
	}
	die;
}

if ((@$_GET["op"] == "object_exists_system") || (@$_GET["op"] == "check_object_exists_system")) {
	echo checkObjectExistsSystem($_GET["imei"]);
	die;
}

if (@$_GET["op"] == "cmd_exec_imei_get") {
	$format = strtolower(@$_GET["format"]);

	//$q = "SELECT * FROM `gs_object_cmd_exec` WHERE `status`='0' AND `imei`='".$_GET["imei"]."'";

	$q = "SELECT gs_objects.*, gs_object_cmd_exec.*
			FROM gs_objects
			INNER JOIN gs_object_cmd_exec ON gs_objects.imei = gs_object_cmd_exec.imei
			WHERE gs_object_cmd_exec.status='0' AND gs_object_cmd_exec.imei='" . $_GET["imei"] . "' ORDER BY gs_object_cmd_exec.cmd_id ASC";
	$r = mysqli_query($ms, $q);

	if ($format == 'json') {
		$result = array();

		while ($row = mysqli_fetch_array($r)) {
			$result[] = array($row['cmd_id'], $row['cmd']);

			$q2 = "UPDATE `gs_object_cmd_exec` SET `status`='1' WHERE `cmd_id`='" . $row["cmd_id"] . "'";
			$r2 = mysqli_query($ms, $q2);
		}

		echo json_encode($result);
	} else {
		$result = '';

		while ($row = mysqli_fetch_array($r)) {
			// translate new default commands to older android and iphone commands
			if (($row['protocol'] == 'android') || ($row['protocol'] == 'iphone')) {
				$row['cmd'] = str_replace("position_interval", 'interval', $row['cmd']);
				$row['cmd'] = str_replace("command_interval", 'cmd_interval', $row['cmd']);
			}

			$result .= $row['cmd_id'] . chr(30) . $row['cmd'] . chr(29);

			$q2 = "UPDATE `gs_object_cmd_exec` SET `status`='1' WHERE `cmd_id`='" . $row["cmd_id"] . "'";
			$r2 = mysqli_query($ms, $q2);
		}

		echo $result;
	}

	die;
}

// #################################################
//  END WILL BE DEPRECATED IN 4.0 VERSION
// #################################################

if ($gsValues['HW_KEY'] != @$data["key"]) {
	echo 'Incorrect hardware key.';
	die;
} else {
	if ((@$data["op"] != "get_cmd_exec") && (@$data["op"] != "set_cmd_exec")) {
		echo "OK";
	}
}

if (@$data["op"] == "clear_object_history") {
	clearObjectHistory($data['imei']);
}

if (@$data["op"] == "get_cmd_exec") {
	$q = "SELECT gs_objects.*, gs_object_cmd_exec.*
			FROM gs_objects
			INNER JOIN gs_object_cmd_exec ON gs_objects.imei = gs_object_cmd_exec.imei
			WHERE gs_object_cmd_exec.status='0' ORDER BY gs_object_cmd_exec.cmd_id ASC";
	$r = mysqli_query($ms, $q);

	$result = array();

	while ($row = mysqli_fetch_array($r)) {
		if (($row['protocol'] != 'android') && ($row['protocol'] != 'iphone')) {
			$result[] = array(
				"cmd_id" => intval($row['cmd_id']),
				"protocol" => $row['protocol'],
				"net_protocol" => $row['net_protocol'],
				"ip" => $row['ip'],
				"port" => intval($row['port']),
				"imei" => $row['imei'],
				"type" => $row['type'],
				"cmd" => $row['cmd']
			);
		}
	}

	header('Content-type: application/json');
	echo json_encode($result);
	die;
}

if (@$data["op"] == "set_cmd_exec") {
	if (isset($data["re_hex"])) {
		$q = "UPDATE `gs_object_cmd_exec` SET `status`='" . $data["status"] . "', `re_hex`='" . $data["re_hex"] . "' WHERE `cmd_id`='" . $data["cmd_id"] . "'";
	} else {
		$q = "UPDATE `gs_object_cmd_exec` SET `status`='" . $data["status"] . "' WHERE `cmd_id`='" . $data["cmd_id"] . "'";
	}

	$r = mysqli_query($ms, $q);

	echo "OK";
	die;
}

header("Connection: close");
header("Content-length: " . (string) ob_get_length());
ob_end_flush();

if (@$_GET["op"] == "service_12h") {
	cronCheckObjectReports();
	serviceClearVarious();
	serviceClearHistory();
	serviceServerCleanup();
	serviceGetConfigFota();
}

if (@$_GET["op"] == "service_1h") {
	serviceCheckAccountDateLimit();
	serviceCheckObjectDateLimit();
	serviceCheckSharePositionDateLimit();
}

if (@$_GET["op"] == "service_30min") {
	if ($gsValues['REPORTS_SCHEDULE'] == 'true') {
		serviceSendReportDaily();
		serviceSendReportWeekly();
		serviceSendReportMonthly();
		serviceSendReportweek_days();
		serviceEventService();
	}
}

if (@$_GET["op"] == "service_5min") {
	serviceSendReportHoursDaily();
	serviceCMDSchedule();
	serviceMileageDaily();
	//event_notify_alert();
}

if (@$_GET["op"] == "service_1min") {
	serviceClearCounters();
	serviceEvents();
	serviceWebhookQueue();
	serviceEmailQueue();
	serviceSMSHTTPQueue();
	serviceObjectResetCount();
}

// service 24h
function event_notify_alert()
{
	global $ms, $gsValues;


	$email = $gsValues['DB_BACKUP_EMAIL'];
	$emailsArray = explode(',', $email);
	$emailsArray = array_map('trim', $emailsArray);

	if ($emailsArray == '') {
		die;
	}

	$q = "SELECT * FROM `gs_system` WHERE `key`='DB_BACKUP_TIME_LAST'";
	$r = mysqli_query($ms, $q);
	$row = mysqli_fetch_array($r);

	if ($row) {
		$dt_send = gmdate("Y-m-d") . ' ' . $gsValues['DB_BACKUP_TIME'] . ':00';

		if (strtotime($row['value']) < strtotime($dt_send)) {
			if (strtotime(gmdate('Y-m-d H:i:s')) < strtotime($dt_send)) {
				die;
			}
		} else {
			die;
		}
	}
	$q_objects = "SELECT * FROM `gs_object_services` ORDER BY service_id DESC";


	$r1 = mysqli_query($ms, $q_objects);

	while ($row = mysqli_fetch_array($r1)) {
		$plan = $row["plan"];
		$imei = $row["imei"];

		if ($plan == 'Garantia' || $plan == 'Mantenimiento') {

			$days_diff = strtotime(gmdate("Y-m-d")) - (strtotime($row['days_last']));
			$days_diff = floor($days_diff / 3600 / 24);
			$days_diff = $row['days_interval'] - $days_diff;

			$q2 = "SELECT name FROM `gs_objects` WHERE `imei`='$imei'";
			$r2 = mysqli_query($ms, $q2);
			$row2 = mysqli_fetch_array($r2);

			if ($days_diff <= $row['days_left_num']) {
				if ($plan == 'Garantia' || $plan == 'Mantenimiento') {
					$array1[] = array(
						'plan' => $row['plan'],
						'name' => $row2['name'],
						'imei' => $row['imei'],
						'fecha_last_mtto' => $row['days_last'],
						'dias_rest' => $days_diff
					);
				}
			}
		}
	}


	$infoArray = [];
	$notificationMessage = '<div style="text-align: center; font-weight: bold;">Total De Unidades Con Planes Próximos a Vencer</div><br><br>';
	foreach ($array1 as $device) {
		$planInfo = $device['plan'];
		$nameInfo = $device['name'];
		$fechaMtto = $device['fecha_last_mtto'];
		$imeiInfo = $device['imei'];
		$dias_rest = $device['dias_rest'];

		$notificationMessage .= '<div>Unidad: ' . $nameInfo . ', Imei: ' . $imeiInfo . ', Plan: ' . $planInfo . ', Ultimo Servicio ' . $fechaMtto . ', Días Restantes: ' . $dias_rest . '</div><br>';


		$infoArray[] = [
			'unidad' => $nameInfo,
			'imei' => $imeiInfo,
			'plan' => $planInfo,
			'ultimo Servicio' => $fechaMtto,
			'dias_rest' => $dias_rest
		];
	}

	usort($infoArray, function ($a, $b) {
		return $a['imei'] <=> $b['imei'];
	});

	$sortedMessagesArray = ['Total De Unidades Con Planes Próximos a Vencer:'];
	foreach ($infoArray as $info) {
		$sortedMessagesArray[] = "Unidad: {$info['unidad']}, Imei: {$info['imei']}, Plan: {$info['plan']}, Ultimo Servicio: {$info['ultimo Servicio']}, Días Restantes: {$info['dias_rest']}.";
	}

	$sortedMessage = implode("\n\n", $sortedMessagesArray);

	$subject = 'Unidades con Mtto/Garantias Próximos a Vencer';
	$message = $sortedMessage;
	$count = 0;
	$totalEmails = count($emailsArray);

	foreach ($emailsArray as $email) {
		sendEmail($email, $subject, $message);

		$count++;
		if ($count == $totalEmails) {
			sleep(1);
			$count = 0;

			$q = "SELECT * FROM `gs_system` WHERE `key`='DB_BACKUP_TIME_LAST'";
			$r = mysqli_query($ms, $q);
			$row = mysqli_fetch_array($r);

			if ($row) {
				$q = "UPDATE gs_system SET `value`='" . gmdate("Y-m-d H:i:s") . "' WHERE `key`='DB_BACKUP_TIME_LAST'";
				$r = mysqli_query($ms, $q);
			} else {
				$q = "INSERT INTO `gs_system`(`key`,`value`) VALUES ('DB_BACKUP_TIME_LAST', '" . gmdate("Y-m-d H:i:s") . "')";
				$r = mysqli_query($ms, $q);
			}
		}
	}
}

// service 12h
function serviceServerCleanup()
{
	global $ms, $gsValues;

	if ($gsValues['SERVER_CLEANUP_USERS_AE'] == "true") {
		$days = $gsValues['SERVER_CLEANUP_USERS_DAYS'];
		$result = serverCleanupUsers($days);
	}

	if ($gsValues['SERVER_CLEANUP_OBJECTS_NOT_ACTIVATED_AE'] == "true") {
		$days = $gsValues['SERVER_CLEANUP_OBJECTS_NOT_ACTIVATED_DAYS'];
		$result = serverCleanupObjectsNotActivated($days);
	}

	if ($gsValues['SERVER_CLEANUP_OBJECTS_NOT_USED_AE'] == "true") {
		$result = serverCleanupObjectsNotUsed();
	}

	if ($gsValues['SERVER_CLEANUP_DB_JUNK_AE'] == "true") {
		$result = serverCleanupDbJunk();
	}
}

// service 1h
function serviceCheckAccountDateLimit()
{
	global $ms, $gsValues, $la;

	// deactivate expired accounts
	$q = "UPDATE gs_users SET `active`='false' WHERE account_expire ='true' AND account_expire_dt <= UTC_DATE()";
	$r = mysqli_query($ms, $q);

	// remind about object expiry
	if ($gsValues['NOTIFY_ACCOUNT_EXPIRE'] == 'true') {
		$q = "SELECT * FROM `gs_users`";
		$r = mysqli_query($ms, $q);

		while ($ud = mysqli_fetch_array($r)) {
			$user_id = $ud["id"];
			$account_expire = $ud["account_expire"];
			$account_expire_dt = $ud["account_expire_dt"];
			$email = $ud["email"];
			$notify_account_expire = $ud['notify_account_expire'];

			if ($account_expire == 'true') {
				$notify = false;

				$diff = strtotime($account_expire_dt) - strtotime(gmdate("Y-m-d"));
				$days = $diff / 86400;

				if ($days <= $gsValues['NOTIFY_ACCOUNT_EXPIRE_PERIOD']) {
					$notify = true;
				}

				if ($notify == true) {
					if ($notify_account_expire != 'true') {
						$template = getDefaultTemplate('expiring_account', $ud["language"]);

						$subject = $template['subject'];
						$message = $template['message'];

						$subject = str_replace("%SERVER_NAME%", $gsValues['NAME'], $subject);
						$subject = str_replace("%URL_SHOP%", $gsValues['URL_SHOP'], $subject);

						$message = str_replace("%SERVER_NAME%", $gsValues['NAME'], $message);
						$message = str_replace("%URL_SHOP%", $gsValues['URL_SHOP'], $message);

						if (sendEmail($email, $subject, $message)) {
							$q4 = "UPDATE gs_users SET `notify_account_expire`='true' WHERE `id`='" . $user_id . "'";
							$r4 = mysqli_query($ms, $q4);
						}
					}
				} else {
					$q4 = "UPDATE gs_users SET `notify_account_expire`='false' WHERE `id`='" . $user_id . "'";
					$r4 = mysqli_query($ms, $q4);
				}
			}
		}
	}
}

function serviceCheckObjectDateLimit()
{
	global $ms, $gsValues, $la;

	// deactivate expired objects
	$q = "UPDATE gs_objects SET `active`='false' WHERE `active`='true' AND `object_expire`='true' AND object_expire_dt <= UTC_DATE()";
	$r = mysqli_query($ms, $q);

	// remind about object expiry
	if ($gsValues['NOTIFY_OBJ_EXPIRE'] == 'true') {
		$q = "SELECT * FROM `gs_users` WHERE `privileges` NOT LIKE ('%subuser%')";
		$r = mysqli_query($ms, $q);

		while ($ud = mysqli_fetch_array($r)) {
			$notify = false;

			$user_id = $ud["id"];
			$email = $ud["email"];

			$q2 = "SELECT * FROM `gs_user_objects` WHERE `user_id`='" . $user_id . "'";
			$r2 = mysqli_query($ms, $q2);

			while ($row2 = mysqli_fetch_array($r2)) {
				$imei = $row2['imei'];

				$q3 = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "' AND `active`='true' AND `object_expire`='true'";
				$r3 = mysqli_query($ms, $q3);
				$row3 = mysqli_fetch_array($r3);

				if ($row3) {
					$diff = strtotime($row3['object_expire_dt']) - strtotime(gmdate("Y-m-d"));
					$days = $diff / 86400;

					if ($days <= $gsValues['NOTIFY_OBJ_EXPIRE_PERIOD']) {
						$notify = true;
						
					}
				}
			}

			if ($notify == true) {
				if ($ud['notify_object_expire'] != 'true') {
					$template = getDefaultTemplate('expiring_objects', $ud["language"]);

					$subject = $template['subject'];
					$message = $template['message'];

					$subject = str_replace("%SERVER_NAME%", $gsValues['NAME'], $subject);
					$subject = str_replace("%URL_SHOP%", $gsValues['URL_SHOP'], $subject);

					$message = str_replace("%SERVER_NAME%", $gsValues['NAME'], $message);
					$message = str_replace("%URL_SHOP%", $gsValues['URL_SHOP'], $message);

					if (sendEmail($email, $subject, $message)) {
						$q4 = "UPDATE gs_users SET `notify_object_expire`='true' WHERE `id`='" . $user_id . "'";
						$r4 = mysqli_query($ms, $q4);
					}
				}
			} else {
				$q4 = "UPDATE gs_users SET `notify_object_expire`='false' WHERE `id`='" . $user_id . "'";
				$r4 = mysqli_query($ms, $q4);
			}
		}
	}
}

function serviceCheckSharePositionDateLimit()
{
	global $ms, $gsValues, $la;

	// deactivate expired objects
	$q = "UPDATE gs_user_share_position SET `active`='false' WHERE `active`='true' AND `expire`='true' AND expire_dt <= UTC_DATE()";
	$r = mysqli_query($ms, $q);
}

function serviceClearHistory()
{
	global $ms, $gsValues;

	if (!isset($gsValues['HISTORY_PERIOD'])) {
		die;
	}

	if ($gsValues['HISTORY_PERIOD'] < 30) {
		die;
	}

	$q = "SELECT * FROM `gs_objects` ORDER BY `imei` ASC";
	$r = mysqli_query($ms, $q);

	while ($row = mysqli_fetch_array($r)) {
		$q2 = "DELETE FROM `gs_object_data_" . $row['imei'] . "` WHERE dt_tracker < DATE_SUB(UTC_DATE(), INTERVAL " . $gsValues['HISTORY_PERIOD'] . " DAY)";
		$r2 = mysqli_query($ms, $q2);
	}
}
function serviceGetConfigFota()
{
	global $ms, $gsValues;

	$q = "SELECT imei FROM `gs_objects` WHERE `protocol`='teltonika'";
	$r = mysqli_query($ms, $q);

	while ($row = mysqli_fetch_array($r)) {
		$imei = $row['imei'];

		$fota = getObjectFota($imei);
		if (empty($fota)) {
			$fota = 'N/A';
		}

		$q_update = "UPDATE `gs_user_objects` SET `config_fota` = '$fota' WHERE `imei` = '$imei'";
		mysqli_query($ms, $q_update);
	}
}
function serviceClearVarious()
{
	global $ms, $gsValues;

	if (!isset($gsValues['HISTORY_PERIOD'])) {
		die;
	}

	if ($gsValues['HISTORY_PERIOD'] < 30) {
		die;
	}

	$q = "DELETE FROM `gs_user_failed_logins` WHERE dt_login < DATE_SUB(UTC_DATE(), INTERVAL 1 DAY)";
	$r = mysqli_query($ms, $q);

	$q = "DELETE FROM `gs_user_account_recover` WHERE dt_recover < DATE_SUB(UTC_DATE(), INTERVAL 1 DAY)";
	$r = mysqli_query($ms, $q);

	$q = "DELETE FROM `gs_user_usage` WHERE dt_usage < DATE_SUB(UTC_DATE(), INTERVAL 6 DAY)";
	$r = mysqli_query($ms, $q);

	$q = "DELETE FROM `gs_object_cmd_exec` WHERE dt_cmd < DATE_SUB(UTC_DATE(), INTERVAL 1 DAY)";
	$r = mysqli_query($ms, $q);

	$q = "DELETE FROM `gs_push_queue` WHERE dt_push < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 HOUR)";
	$r = mysqli_query($ms, $q);

	$q = "DELETE FROM `gs_email_queue` WHERE dt_email < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 HOUR)";
	$r = mysqli_query($ms, $q);

	$q = "DELETE FROM `gs_sms_queue` WHERE dt_sms < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 HOUR)";
	$r = mysqli_query($ms, $q);

	$q = "DELETE FROM `gs_webhook_queue` WHERE dt_webhook < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 HOUR)";
	$r = mysqli_query($ms, $q);

	$q = "DELETE FROM `gs_sms_gateway_app` WHERE dt_sms < DATE_SUB(UTC_DATE(), INTERVAL 1 HOUR)";
	$r = mysqli_query($ms, $q);

	$q = "SELECT * FROM `gs_user_reports_generated` WHERE dt_report < DATE_SUB(UTC_DATE(), INTERVAL 30 DAY)";
	$r = mysqli_query($ms, $q);

	while ($row = mysqli_fetch_array($r)) {
		$q2 = "DELETE FROM `gs_user_reports_generated` WHERE `report_id`='" . $row['report_id'] . "'";
		$r2 = mysqli_query($ms, $q2);

		$report_file = $gsValues['PATH_ROOT'] . 'data/user/reports/' . $row['report_file'];
		if (is_file($report_file)) {
			@unlink($report_file);
		}
	}

	$q = "DELETE FROM `gs_user_last_events_data` WHERE dt_tracker < DATE_SUB(UTC_DATE(), INTERVAL 6 DAY)";
	$r = mysqli_query($ms, $q);

	$q = "DELETE FROM `gs_user_events_data` WHERE dt_tracker < DATE_SUB(UTC_DATE(), INTERVAL " . $gsValues['HISTORY_PERIOD'] . " DAY)";
	$r = mysqli_query($ms, $q);

	$q = "DELETE FROM `gs_object_tasks` WHERE dt_task < DATE_SUB(UTC_DATE(), INTERVAL " . $gsValues['HISTORY_PERIOD'] . " DAY)";
	$r = mysqli_query($ms, $q);

	$q = "DELETE FROM `gs_rilogbook_data` WHERE dt_tracker < DATE_SUB(UTC_DATE(), INTERVAL " . $gsValues['HISTORY_PERIOD'] . " DAY)";
	$r = mysqli_query($ms, $q);

	$q = "DELETE FROM `gs_dtc_data` WHERE dt_tracker < DATE_SUB(UTC_DATE(), INTERVAL " . $gsValues['HISTORY_PERIOD'] . " DAY)";
	$r = mysqli_query($ms, $q);

	$q = "SELECT * FROM `gs_object_img` WHERE dt_tracker < DATE_SUB(UTC_DATE(), INTERVAL " . $gsValues['HISTORY_PERIOD'] . " DAY)";
	$r = mysqli_query($ms, $q);

	while ($row = mysqli_fetch_array($r)) {
		$q2 = "DELETE FROM `gs_object_img` WHERE `img_id`='" . $row['img_id'] . "'";
		$r2 = mysqli_query($ms, $q2);

		$img_file = $gsValues['PATH_ROOT'] . 'data/img/' . $row['img_file'];
		if (is_file($img_file)) {
			@unlink($img_file);
		}
	}

	$q = "SELECT * FROM `gs_object_chat` WHERE dt_server < DATE_SUB(UTC_DATE(), INTERVAL " . $gsValues['HISTORY_PERIOD'] . " DAY)";
	$r = mysqli_query($ms, $q);
}


function serviceSendReportHoursDaily()
{
	global $ms, $gsValues;

	$q = "SELECT * FROM `gs_user_reports` WHERE schedule_period LIKE '%H%'";
	$r = mysqli_query($ms, $q);
	if (!$r) die;

	$reports = array();

	while ($report = mysqli_fetch_array($r)) {
		$fecha_actual = new DateTime('now', new DateTimeZone('UTC'));
		$fecha_actual->modify('-6 hours');

		$fecha_actual_local = $fecha_actual->format('Y-m-d H:i:s');
		$hora_actual_local = $fecha_actual->format('H:i');
		$dia_actual = strtolower($fecha_actual->format('D'));
		$hora_prog = substr($report['time'], 0, 5);
		$dias_semana = $report['week_days'];
		$metodo_envio = $report['schedule_period'];

		$tipo_envio = substr($metodo_envio, 0, 2);

		// ENVÍO SEMANAL
		if ($tipo_envio == 'Hw') {

			$now = new DateTime('now');
			$now->modify('-6 hours');

			if ($now->format('N') == 1) {
				$fecha_hoy = $now->format('Y-m-d');
				$fecha_last_envio = (new DateTime($report['dt_schedule_w']))->format('Y-m-d');

				if ($fecha_hoy === $fecha_last_envio) {
					continue;
				}
			} else {
				continue;
			}

			if ($hora_actual_local >= $hora_prog) {
				$previous_week = strtotime("-1 week +1 day");
				$start_week = strtotime("last monday", $previous_week);
				$end_week = strtotime("next monday", $start_week);

				$report['dtf'] = gmdate("Y-m-d H:i:s", $start_week);
				$report['dtt'] = gmdate("Y-m-d H:i:s", $end_week);


				$q2 = 'UPDATE gs_user_reports SET `dt_schedule_w` = "' . $fecha_actual_local . '" WHERE report_id="' . $report['report_id'] . '"';
				$r2 = mysqli_query($ms, $q2);
				if ($r2) {
					$reports[] = $report;
				}
			}
			continue;
		}

		// ENVÍO MENSUAL
		if ($tipo_envio == 'Hm') {

			$now = new DateTime('now');
			$now->modify('-6 hours');

			if ($now->format('j') == 01) {
				$fecha_hoy = $now->format('Y-m-d');
				$fecha_last_envio = (new DateTime($report['dt_schedule_m']))->format('Y-m-d');

				if ($fecha_hoy === $fecha_last_envio) {
					continue;
				}
			} else {
				continue;
			}

			if ($hora_actual_local >= $hora_prog) {
				$start_month = date('Y-m-01 00:00:00', strtotime('first day of last month'));
			
				$end_month = date('Y-m-t 23:59:59', strtotime('last day of last month'));
			
				$report['dtf'] = $start_month;
				$report['dtt'] = $end_month;
			
				$q2 = 'UPDATE gs_user_reports SET `dt_schedule_m` = "' . $fecha_actual_local . '" WHERE report_id="' . $report['report_id'] . '"';
				$r2 = mysqli_query($ms, $q2);
			
				if ($r2) {
					$reports[] = $report;
				}
			}
			continue;
		}

		// ENVÍO DIARIO
		if ($tipo_envio == 'Hd' || $tipo_envio == 'H') {

			$dias_permitidos = array_filter(array_map('trim', explode(',', $dias_semana)));
		
			if (!empty($dias_permitidos) && !in_array(strtolower($dia_actual), $dias_permitidos)) {
				continue;
			}
		
			$now = new DateTime('now');
			$now->modify('-6 hours');
		
			$fecha_hoy = $now->format('Y-m-d');
			$fecha_last_envio = (new DateTime($report['dt_schedule_d']))->format('Y-m-d');
		
			if ($fecha_hoy === $fecha_last_envio) {
				continue;
			}
		
			if ($hora_actual_local >= $hora_prog) {
				$report['dtf'] = gmdate('Y-m-d', strtotime("-1 days")) . ' 00:00:00';
				$report['dtt'] = gmdate('Y-m-d') . ' 00:00:00';
		
				$q2 = 'UPDATE gs_user_reports SET `dt_schedule_d` = "' . $fecha_actual_local . '" WHERE report_id="' . $report['report_id'] . '"';
				$r2 = mysqli_query($ms, $q2);
				if ($r2) {
					$reports[] = $report;
				}
			}
			continue;
		}

		// Envío anticipado si hay más de 4 reportes listos
		if (count($reports) > 4) {
			if ($gsValues['CURL']) {
				serviceSendReportsCURL($reports);
			} else {
				serviceSendReports($reports);
			}
			$reports = array();
		}
	}

	// Enviar los restantes
	if (count($reports) > 0) {
		if ($gsValues['CURL']) {
			serviceSendReportsCURL($reports);
		} else {
			serviceSendReports($reports);
		}
		$reports = array();
	}
}



function serviceSendReportMonthly()
{
	global $ms, $gsValues;

	// obtener los informes mensuales
	$q = "SELECT * FROM `gs_user_reports` WHERE schedule_period LIKE '%m%' AND MONTH(dt_schedule_m) != MONTH(UTC_DATE())";
	$r = mysqli_query($ms, $q);

	if (!$r) {
		die;
	}

	$reports = array();

	while ($report = mysqli_fetch_array($r)) {
		// verificar si ha pasado el día del usuario según la zona horaria establecida
		$dt = convUserIDTimezone($report['user_id'], gmdate("Y-m-d H:i:s"));
		if (date('d', strtotime($dt)) != date('d')) {
			continue;
		}

		// Obtener el primer día del mes
		$start_month = strtotime(date('Y-m-01'));

		// Obtener el último día del mes
		$end_month = strtotime(date('Y-m-t'));

		$report['dtf'] = date('Y-m-d', $start_month) . ' 00:00:00';
		$report['dtt'] = date('Y-m-d', $end_month) . ' 00:00:00';

		$dt_schedule_m = date('Y-m-d', $end_month) . ' 00:00:00';

		$q2 = 'UPDATE gs_user_reports SET `dt_schedule_m` = "' . $dt_schedule_m . '" WHERE report_id="' . $report['report_id'] . '"';
		$r2 = mysqli_query($ms, $q2);

		if ($r2) {
			$reports[] = $report;
		}

		// generar informes mensuales
		if (count($reports) > 4) {
			if ($gsValues['CURL'] == true) {
				serviceSendReportsCURL($reports);
			} else {
				serviceSendReports($reports);
			}

			// resetear los informes anteriores
			$reports = array();
		}
	}

	// generar los informes restantes
	if (count($reports) > 0) {
		if ($gsValues['CURL'] == true) {
			serviceSendReportsCURL($reports);
		} else {
			serviceSendReports($reports);
		}

		// resetear los informes anteriores
		$reports = array();
	}
}

function serviceSendReportWeekly()
{
	global $ms, $gsValues;

	// get weekly reports
	$q = "SELECT * FROM `gs_user_reports` WHERE schedule_period LIKE '%w%' AND dt_schedule_w < DATE_SUB(UTC_DATE(), INTERVAL 6 DAY)";
	$r = mysqli_query($ms, $q);

	if (!$r) {
		die;
	}

	$reports = array();

	while ($report = mysqli_fetch_array($r)) {
		// check if user day passed depending on set timezone
		$dt = convUserIDTimezone($report['user_id'], gmdate("Y-m-d H:i:s"));
		if (strtotime($dt) < strtotime(gmdate('Y-m-d'))) {
			continue;
		}

		$previous_week = strtotime("-1 week +1 day");

		// get prev week monday
		$start_week = strtotime("last monday", $previous_week);

		// get next week monday
		$end_week = strtotime("next monday", $start_week);

		$report['dtf'] = gmdate("Y-m-d", $start_week) . ' 00:00:00';
		$report['dtt'] = gmdate("Y-m-d", $end_week) . ' 00:00:00';

		$dt_schedule_w = gmdate('Y-m-d', strtotime('monday')) . ' 00:00:00';

		$q2 = 'UPDATE gs_user_reports SET `dt_schedule_w` = "' . $dt_schedule_w . '" WHERE report_id="' . $report['report_id'] . '"';
		$r2 = mysqli_query($ms, $q2);

		if ($r2) {
			$reports[] = $report;
		}

		// generate 5 reports at once
		if (count($reports) > 4) {
			if ($gsValues['CURL'] == true) {
				serviceSendReportsCURL($reports);
			} else {
				serviceSendReports($reports);
			}

			// reset previous reports
			$reports = array();
		}
	}

	// generate left reports
	if (count($reports) > 0) {
		if ($gsValues['CURL'] == true) {
			serviceSendReportsCURL($reports);
		} else {
			serviceSendReports($reports);
		}

		// reset previous reports
		$reports = array();
	}
}

function serviceSendReportDaily()
{
	global $ms, $gsValues;

	// get daily reports
	$q = "SELECT * FROM `gs_user_reports` WHERE schedule_period LIKE '%d%' AND dt_schedule_d < UTC_DATE()";
	$r = mysqli_query($ms, $q);

	if (!$r) {
		die;
	}

	$reports = array();

	while ($report = mysqli_fetch_array($r)) {
		// check if user day passed depending on set timezone
		$dt = convUserIDTimezone($report['user_id'], gmdate("Y-m-d H:i:s"));
		if (strtotime($dt) < strtotime(gmdate('Y-m-d'))) {
			continue;
		}

		$report['dtf'] = gmdate('Y-m-d', strtotime("-1 days")) . ' 00:00:00'; // yesterday
		$report['dtt'] = gmdate('Y-m-d') . ' 00:00:00'; // today

		$dt_schedule_d = gmdate("Y-m-d H:i:s");

		$q2 = 'UPDATE gs_user_reports SET `dt_schedule_d` = "' . $dt_schedule_d . '" WHERE report_id="' . $report['report_id'] . '"';
		$r2 = mysqli_query($ms, $q2);

		if ($r2) {
			$reports[] = $report;
		}

		// generate 5 reports at once
		if (count($reports) > 4) {
			if ($gsValues['CURL'] == true) {
				serviceSendReportsCURL($reports);
			} else {
				serviceSendReports($reports);
			}

			// reset previous reports
			$reports = array();
		}
	}

	// generate left reports
	if (count($reports) > 0) {
		if ($gsValues['CURL'] == true) {
			serviceSendReportsCURL($reports);
		} else {
			serviceSendReports($reports);
		}

		// reset previous reports
		$reports = array();
	}
}
function serviceSendReportweek_days()
{
	global $ms, $gsValues;

	// Obtener el día actual de la semana en formato de tres letras en minúsculas (por ejemplo, "mon" para lunes)
	$currentDay = strtolower(date('D'));


	// Condición para buscar informes basados en los días de la semana y en el día actual
	$condition = "FIND_IN_SET('$currentDay', week_days) > 0";

	// Consulta para obtener informes
	$q = "SELECT * FROM `gs_user_reports` WHERE $condition AND dt_schedule_d < UTC_DATE()";
	$r = mysqli_query($ms, $q);

	if (!$r) {
		die;
	}

	// Procesar los informes obtenidos
	$reports = array();
	while ($report = mysqli_fetch_array($r)) {
		// Verificar si el día del usuario ha pasado dependiendo de la zona horaria establecida
		$dt = convUserIDTimezone($report['user_id'], gmdate("Y-m-d H:i:s"));
		if (strtotime($dt) < strtotime(gmdate('Y-m-d'))) {
			continue;
		}

		$report['dtf'] = gmdate('Y-m-d', strtotime("-1 days")) . ' 00:00:00'; // Ayer
		$report['dtt'] = gmdate('Y-m-d') . ' 00:00:00'; // Hoy

		$dt_schedule_d = gmdate("Y-m-d H:i:s");

		// Actualizar la fecha de programación del informe solo si el día actual coincide con los días especificados
		$q2 = 'UPDATE gs_user_reports SET `dt_schedule_d` = "' . $dt_schedule_d . '" WHERE report_id="' . $report['report_id'] . '"';
		$r2 = mysqli_query($ms, $q2);

		if ($r2) {
			$reports[] = $report;
		}

		// Generar 5 informes a la vez
		if (count($reports) > 4) {
			if ($gsValues['CURL'] == true) {
				serviceSendReportsCURL($reports);
			} else {
				serviceSendReports($reports);
			}

			// Resetear los informes procesados
			$reports = array();
		}
	}

	// Generar los informes restantes
	if (count($reports) > 0) {
		if ($gsValues['CURL'] == true) {
			serviceSendReportsCURL($reports);
		} else {
			serviceSendReports($reports);
		}

		// Resetear los informes procesados
		$reports = array();
	}
}




function serviceSendReports($reports)
{
	global $ms, $gsValues;

	$url = $gsValues['URL_ROOT'] . '/func/fn_reports.gen.php';

	$reports_count = count($reports);

	for ($i = 0; $i < $reports_count; $i++) {
		$postdata = http_build_query(
			array(
				'cmd' => 'report',
				'schedule' => true,
				'user_id' => $reports[$i]['user_id'],
				'email' => $reports[$i]['schedule_email_address'],
				'name' => $reports[$i]['name'],
				'type' => $reports[$i]['type'],
				'ignore_empty_reports' => $reports[$i]['ignore_empty_reports'],
				'format' => $reports[$i]['format'],
				'show_coordinates' => $reports[$i]['show_coordinates'],
				'show_addresses' => $reports[$i]['show_addresses'],
				'zones_addresses' => $reports[$i]['zones_addresses'],
				'stop_duration' => $reports[$i]['stop_duration'],
				'speed_limit' => $reports[$i]['speed_limit'],
				'imei' => $reports[$i]['imei'],
				'zone_ids' => $reports[$i]['zone_ids'],
				'sensor_names' => $reports[$i]['sensor_names'],
				'data_items' => $reports[$i]['data_items'],
				'other' => $reports[$i]['other'],
				'dtf' => $reports[$i]['dtf'],
				'dtt' => $reports[$i]['dtt']
			)
		);

		$opts = array(
			'http' => array(
				'method' => 'POST',
				'header' => 'Content-type: application/x-www-form-urlencoded',
				'content' => $postdata
			),
			'ssl' => array('verify_peer' => false)
		);

		$context = stream_context_create($opts);

		$result = file_get_contents($url, false, $context);

		$result = null;
		unset($result);
	}
}

function serviceSendReportsCURL($reports)
{
	global $ms, $gsValues;

	$url = $gsValues['URL_ROOT'] . '/func/fn_reports.gen.php';

	$reports_count = count($reports);

	$curl_arr = array();
	$master = curl_multi_init();

	for ($i = 0; $i < $reports_count; $i++) {
		$postdata = http_build_query(
			array(
				'cmd' => 'report',
				'schedule' => true,
				'user_id' => $reports[$i]['user_id'],
				'email' => $reports[$i]['schedule_email_address'],
				'name' => $reports[$i]['name'],
				'type' => $reports[$i]['type'],
				'ignore_empty_reports' => $reports[$i]['ignore_empty_reports'],
				'format' => $reports[$i]['format'],
				'show_coordinates' => $reports[$i]['show_coordinates'],
				'show_addresses' => $reports[$i]['show_addresses'],
				'zones_addresses' => $reports[$i]['zones_addresses'],
				'stop_duration' => $reports[$i]['stop_duration'],
				'speed_limit' => $reports[$i]['speed_limit'],
				'imei' => $reports[$i]['imei'],
				'zone_ids' => $reports[$i]['zone_ids'],
				'sensor_names' => $reports[$i]['sensor_names'],
				'data_items' => $reports[$i]['data_items'],
				'other' => $reports[$i]['other'],
				'dtf' => $reports[$i]['dtf'],
				'dtt' => $reports[$i]['dtt']
			)
		);

		$curl_arr[$i] = curl_init($url);
		curl_setopt($curl_arr[$i], CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_arr[$i], CURLOPT_POST, true);
		curl_setopt($curl_arr[$i], CURLOPT_POSTFIELDS, $postdata);
		curl_setopt($curl_arr[$i], CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl_arr[$i], CURLOPT_SSL_VERIFYPEER, false);
		curl_multi_add_handle($master, $curl_arr[$i]);
	}

	do {
		curl_multi_exec($master, $running);
	} while ($running > 0);

	for ($i = 0; $i < $reports_count; $i++) {
		$result = curl_multi_getcontent($curl_arr[$i]);
	}

	unset($curl_arr);
}

// service 5min
function serviceMileageDaily()
{
	global $ms, $gsValues;

	$q = "SELECT * FROM `gs_objects` WHERE dt_mileage < UTC_DATE()";
	$r = mysqli_query($ms, $q);

	if (!$r) {
		die;
	}

	while ($row = mysqli_fetch_array($r)) {
		$q2 = "SELECT gs_users.*, gs_user_objects.*
                                FROM gs_users
                                INNER JOIN gs_user_objects ON gs_users.id = gs_user_objects.user_id
                                WHERE gs_user_objects.imei='" . $row['imei'] . "'";
		$r2 = mysqli_query($ms, $q2);

		if (!$r2) {
			die;
		}

		$row2 = mysqli_fetch_array($r2);

		// check if user day passed depending on set timezone
		$dt = convUserIDTimezone($row2['id'], gmdate("Y-m-d H:i:s"));
		if (strtotime($dt) < strtotime(gmdate('Y-m-d'))) {
			continue;
		}

		$q2 = 'UPDATE gs_objects SET    `mileage_1` = "0",
                                                        `mileage_2` = "' . $row['mileage_1'] . '",
                                                        `mileage_3` = "' . $row['mileage_2'] . '",
                                                        `mileage_4` = "' . $row['mileage_3'] . '",
                                                        `mileage_5` = "' . $row['mileage_4'] . '",
                                                        `dt_mileage` = "' . gmdate("Y-m-d H:i:s") . '"
                                                        WHERE imei="' . $row['imei'] . '"';
		$r2 = mysqli_query($ms, $q2);
	}
}

function serviceCMDSchedule()
{
	global $ms;

	$q = "SELECT * FROM `gs_user_cmd_schedule`";
	$r = mysqli_query($ms, $q);

	while ($row = mysqli_fetch_array($r)) {
		if ($row['active'] == 'true') {
			if ($row['exact_time'] == 'true') {
				$curr_dt = convUserIDTimezone($row['user_id'], gmdate("Y-m-d H:i:s"));

				if ((strtotime($row['dt_schedule_e']) < strtotime($row['exact_time_dt'])) && (strtotime($row['exact_time_dt']) <= strtotime($curr_dt))) {
					$imeis = explode(",", $row['imei']);

					for ($i = 0; $i < count($imeis); ++$i) {
						$imei = $imeis[$i];

						if ($row['gateway'] == 'gprs') {
							sendObjectGPRSCommand($row['user_id'], $imei, $row['name'], $row['type'], $row['cmd']);
						} else if ($row['gateway'] == 'sms') {
							sendObjectSMSCommand($row['user_id'], $imei, $row['name'], $row['cmd']);
						}
					}

					$q2 = 'UPDATE gs_user_cmd_schedule SET `dt_schedule_e` = "' . $curr_dt . '" WHERE cmd_id="' . $row['cmd_id'] . '"';
					$r2 = mysqli_query($ms, $q2);
				}
				if ((strpos($row['name'], 'Update_Fw') !== false && $row['dt_schedule_e'] !== '0000-00-00 00:00:00') || (strpos($row['name'], 'Configuración') !== false && $row['dt_schedule_e'] !== '0000-00-00 00:00:00')) {
					$q = "DELETE FROM `gs_user_cmd_schedule` WHERE `imei`='" . $row['imei'] . "'";
					$r = mysqli_query($ms, $q);
				}
			} else {
				$curr_dt = convUserIDTimezone($row['user_id'], gmdate("Y-m-d H:i:s"));

				$day_of_week = gmdate('w', strtotime($curr_dt));
				$day_time = json_decode($row['day_time'], true);

				if ($day_time != null) {
					if (($day_time['sun'] == true) && ($day_of_week == 0)) {
						$time = $day_time['sun_time'];
					} else if (($day_time['mon'] == true) && ($day_of_week == 1)) {
						$time = $day_time['mon_time'];
					} else if (($day_time['tue'] == true) && ($day_of_week == 2)) {
						$time = $day_time['tue_time'];
					} else if (($day_time['wed'] == true) && ($day_of_week == 3)) {
						$time = $day_time['wed_time'];
					} else if (($day_time['thu'] == true) && ($day_of_week == 4)) {
						$time = $day_time['thu_time'];
					} else if (($day_time['fri'] == true) && ($day_of_week == 5)) {
						$time = $day_time['fri_time'];
					} else if (($day_time['sat'] == true) && ($day_of_week == 6)) {
						$time = $day_time['sat_time'];
					} else {
						continue;
					}

					if (isset($time)) {
						$sched_ts = strtotime($row['dt_schedule_d']);
						$curr_ts = strtotime($curr_dt);
					
						$last_exec_date = date('Y-m-d', $sched_ts);
						$curr_date      = date('Y-m-d', $curr_ts);
					
						$time_scheduled = strtotime($time);
						$curr_time_only = strtotime(date("H:i", $curr_ts));
					
						if (
							$last_exec_date !== $curr_date &&
							$curr_time_only >= $time_scheduled
						) {
							$imeis = explode(",", $row['imei']);
					
							foreach ($imeis as $imei) {
								if ($row['gateway'] == 'gprs') {
									sendObjectGPRSCommand($row['user_id'], $imei, $row['name'], $row['type'], $row['cmd']);
								} else if ($row['gateway'] == 'sms') {
									sendObjectSMSCommand($row['user_id'], $imei, $row['name'], $row['cmd']);
								}
								//getObjectSevice($imei, 'Encendido de Motor');
							}
					
							$q2 = 'UPDATE gs_user_cmd_schedule SET `dt_schedule_d` = "' . $curr_dt . '" WHERE cmd_id="' . $row['cmd_id'] . '"';
							$r2 = mysqli_query($ms, $q2);
						}
					}
				}
			}
		}
	}
}


function serviceEventService()
{
    global $ms;

    $event_query = mysqli_query($ms, "SELECT * FROM `gs_user_events` WHERE `type`='service' AND `active`='true'");

    while ($ed = mysqli_fetch_array($event_query)) {
        $user_id = $ed['user_id'];
        $user_data = mysqli_fetch_array(mysqli_query($ms, "SELECT * FROM `gs_users` WHERE `id`='$user_id'"));
        
        $imeis = explode(",", $ed['imei']);
		$imeis_in = "'" . implode("','", array_map(function($imei) use ($ms) {
			return mysqli_real_escape_string($ms, $imei);
		}, $imeis)) . "'";
		
        $object_query = mysqli_query($ms, "
            SELECT go.*, guo.* 
            FROM gs_objects go
            INNER JOIN gs_user_objects guo ON go.imei = guo.imei
            WHERE guo.user_id='$user_id' AND guo.imei IN ($imeis_in)");

        while ($od = mysqli_fetch_array($object_query)) {
            $imei = $od['imei'];
            $services_query = mysqli_query($ms, "SELECT * FROM `gs_object_services` WHERE `imei`='$imei'");

            while ($sd = mysqli_fetch_array($services_query)) {
                procesarServicio($ms, $ed, $user_data, $od, $sd);
            }
        }
    }
}

function procesarServicio($ms, $eventData, $userData, $objectData, $serviceData)
{
    $imei = $objectData['imei'];
    $type = '';
    $diff = 0;
    $should_notify = false;
    $now = new DateTime('now', new DateTimeZone('UTC'));
    $now->modify('-6 hours');
    $hora = $now->format('H:i');
    $fecha = $now->format('Y-m-d');

    $tipos = [
        'odo' => ['label' => 'odometer', 'current' => getObjectOdometer($imei), 'interval' => 'odo_interval', 'last' => 'odo_last', 'left' => 'odo_left', 'left_num' => 'odo_left_num'],
        'engh' => ['label' => 'horas', 'current' => getObjectEngineHours($imei, false), 'interval' => 'engh_interval', 'last' => 'engh_last', 'left' => 'engh_left', 'left_num' => 'engh_left_num'],
        'days' => ['label' => 'dias', 'current' => null, 'interval' => 'days_interval', 'last' => 'days_last', 'left' => 'days_left', 'left_num' => 'days_left_num']
    ];

    foreach ($tipos as $campo => $cfg) {
		if ($type === 'odometer') {
			$eventData['odo_diff'] = $diff;
		} else if ($type === 'horas') {
			$eventData['engh_diff'] = $diff;
		} else if ($type === 'dias') {
			$eventData['days_diff'] = $diff;
		}
        if ($serviceData[$campo] === 'true' && $serviceData[$cfg['left']] === 'true') {
            $type = $cfg['label'];

            if ($campo === 'days') {
                $days_passed = floor((strtotime(gmdate("Y-m-d")) - strtotime($serviceData[$cfg['last']])) / 86400);
                $diff = $serviceData[$cfg['interval']] - $days_passed;
            } else {
                $diff = $serviceData[$cfg['interval']] - ($cfg['current'] - $serviceData[$cfg['last']]);
            }

            if ($diff <= $serviceData[$cfg['left_num']] && ($campo !== 'days' || $serviceData['plan'] === 'Mantenimiento')) {
                $name_plan = $serviceData['name'] . ' / ' . $serviceData['plan'];

                $row = mysqli_fetch_array(mysqli_query($ms, "
                    SELECT * FROM `gs_user_last_events_data`
                    WHERE `type`='service' AND `imei`='$imei' AND `event_desc`='" . mysqli_real_escape_string($ms, $name_plan) . "'
                    ORDER BY `event_id` DESC LIMIT 1"));

                $attended_status = $row['attended_status'] ?? '';
                $last_dt = $row['dt_server'] ?? null;
                $event_id = $row['event_id'] ?? null;

                $details = '';
                if ($event_id) {
                    $detail_row = mysqli_fetch_array(mysqli_query($ms, "SELECT * FROM `gs_user_last_events_data_details` WHERE `event_id`='$event_id'"));
                    $details = $detail_row['details'] ?? '';
                }

                if ($attended_status === 'Atendido') {
                    if ($serviceData['update_last'] === 'true') {
                        $update_field = ($campo === 'days') ? "`{$cfg['last']}` = '" . gmdate("Y-m-d") . "'" : "`{$cfg['last']}` = {$cfg['current']}";
                        mysqli_query($ms, "UPDATE gs_object_services SET $update_field WHERE `service_id`='{$serviceData['service_id']}'");
                    } else {
                        mysqli_query($ms, "UPDATE gs_object_services SET `data_list`='false', `popup`='false', `notify_service_expire`='false' WHERE `service_id`='{$serviceData['service_id']}'");
                    }

                    $plan = 'Servicio Realizado';
                    $next_val = ($campo === 'days') ? date('Y-m-d', strtotime("+{$serviceData[$cfg['interval']]} days")) : ($serviceData[$cfg['interval']] + $cfg['current']);
                    $loc = obtenerUbicacionActual($ms, $imei);

                    $eventData['event_desc'] = $serviceData['name'] . ' / ' . $plan;
                    $eventData['details'] = $details;
                    $eventData['mtto'] = $type;
                    $eventData['next_' . $campo] = $next_val;
                    $eventData[$campo . '_diff'] = $serviceData[$cfg['interval']];

                    event_notify($eventData, $userData, $objectData, $loc);
                    return;
                }

                $hoy = $fecha;
                if (!$last_dt) {
                    if ($hora >= '10:00') $should_notify = true;
                } else {
                    $last_alert_day = (new DateTime($last_dt, new DateTimeZone('UTC')))->modify('-6 hours')->format("Y-m-d");
                    if ($hoy !== $last_alert_day && $hora >= '10:00') $should_notify = true;
                }
            }

            if ($diff <= 0 && $serviceData['notify_service_expire'] != 'true') {
                mysqli_query($ms, "UPDATE gs_object_services SET `notify_service_expire`='true' WHERE `service_id`='{$serviceData['service_id']}'");

                $loc = obtenerUbicacionActual($ms, $imei);
                $eventData['event_desc'] = $serviceData['name'] . ' / Servicio Vencido';
                $eventData[$campo . '_diff'] = $diff;
                $eventData['mtto'] = $type;

                event_notify($eventData, $userData, $objectData, $loc);
                return;
            }
        }
    }
	if ($type === 'odometer') {
		$eventData['odo_diff'] = $diff;
	} else if ($type === 'horas') {
		$eventData['engh_diff'] = $diff;
	} else if ($type === 'dias') {
		$eventData['days_diff'] = $diff;
	}
	
    if ($should_notify) {
        $plan = $serviceData['plan'];
        $loc = obtenerUbicacionActual($ms, $imei);

        $eventData['event_desc'] = $serviceData['name'] . ' / ' . $plan;
        $eventData['mtto'] = $type;

        event_notify($eventData, $userData, $objectData, $loc);
    }
}

function obtenerUbicacionActual($ms, $imei)
{
    $loc = mysqli_fetch_array(mysqli_query($ms, "SELECT * FROM `gs_objects` WHERE `imei`='$imei'"));
    $loc['dt_server'] = gmdate("Y-m-d H:i:s");
    $loc['dt_tracker'] = $loc['dt_server'];
    $loc['params'] = json_decode($loc['params'], true);
    return $loc;
}

// service 1 minute
function serviceClearCounters()
{
	global $ms;

	$q = "SELECT * FROM `gs_users` WHERE dt_usage_d < UTC_DATE()";
	$r = mysqli_query($ms, $q);

	while ($row = mysqli_fetch_array($r)) {
		$user_id = $row['id'];

		$q2 = "UPDATE gs_users SET 	usage_email_daily_cnt=0,
										usage_sms_daily_cnt=0,
										usage_webhook_daily_cnt=0,
										usage_api_daily_cnt=0,
										`dt_usage_d`='" . gmdate("Y-m-d") . "'
										WHERE id='" . $user_id . "'";
		$r2 = mysqli_query($ms, $q2);

		$q2 = "INSERT INTO `gs_user_usage`(`user_id`,
												`dt_usage`,
												`login`,
												`email`,
												`sms`,
												`webhook`,
												`api`)
												VALUES
												('" . $user_id . "',
												'" . gmdate("Y-m-d") . "',
												'0',
												'0',
												'0',
												'0',
												'0')";
		$r2 = mysqli_query($ms, $q2);
	}
}

function serviceEvents()
{
	global $ms;

	// get all imeis which sent data during last 24 hours
	$q = "SELECT * FROM `gs_objects` WHERE dt_server > DATE_SUB(UTC_DATE(), INTERVAL 1 DAY)";
	$r = mysqli_query($ms, $q);

	while ($loc = mysqli_fetch_array($r)) {
		$loc['params'] = json_decode($loc['params'], true);

		check_events($loc, false, false, false, true);
	}
}

function serviceWebhookQueue()
{
	global $ms, $gsValues;

	$webhook_limit = 150;
	$webhooks = array();
	$last_id = 0;

	$q = "SELECT * FROM `gs_webhook_queue` ORDER BY `id` ASC LIMIT " . $webhook_limit;
	$r = mysqli_query($ms, $q);

	while ($row = mysqli_fetch_array($r)) {
		$webhooks[] = $row['webhook_url'];
		$last_id = $row['id'];
	}

	$q = "DELETE FROM `gs_webhook_queue` WHERE `id`<='" . $last_id . "'";
	$r = mysqli_query($ms, $q);

	if ($gsValues['CURL'] == true) {
		$max_curl_requests = 10;
		$webhooks_curl = array();
		$j = 0;

		for ($i = 0; $i < count($webhooks); $i++) {
			$webhooks_curl[] = $webhooks[$i];
			$j++;

			if ($j == $max_curl_requests) {
				sendWebhookCURL($webhooks_curl);
				$webhooks_curl = array();
				$j = 0;
			} else {
				if ($i == count($webhooks) - 1) {
					sendWebhookCURL($webhooks_curl);
				}
			}
		}
	} else {
		for ($i = 0; $i < count($webhooks); $i++) {
			$result = sendWebhook($webhooks[$i]);
		}
	}
}

function serviceEmailQueue()
{
	global $ms;

	$q = "SELECT * FROM `gs_email_queue` ORDER BY `id` ASC";
	$r = mysqli_query($ms, $q);

	while ($row = mysqli_fetch_array($r)) {
		$q2 = "DELETE FROM `gs_email_queue` WHERE `id`='" . $row['id'] . "'";
		$r2 = mysqli_query($ms, $q2);

		$result = sendEmail($row['email'], $row['subject'], $row['message'], $row['no_reply']);
	}
}

function serviceSMSHTTPQueue()
{
	global $ms;

	$q = "SELECT * FROM `gs_sms_queue` ORDER BY `id` ASC";
	$r = mysqli_query($ms, $q);

	while ($row = mysqli_fetch_array($r)) {
		$q2 = "DELETE FROM `gs_sms_queue` WHERE `id`='" . $row['id'] . "'";
		$r2 = mysqli_query($ms, $q2);

		$result = sendSMSHTTP($row['gateway_url'], $row['filter'], $row['number'], $row['message']);
	}
}

function serviceObjectResetCount()
{
	global $ms;

	$q = "SELECT * FROM `gs_objects`ORDER BY `imei` ASC";
	$r = mysqli_query($ms, $q);
	$row = mysqli_fetch_array($r);

	date_default_timezone_set("Mexico/General");
	$dt_now = date("Y-m-d H:i:s");
	$dia = date("j");
	$hora = date("H:i:s");
	$cuenta_padre = $row['cuenta_padre'];
	$last_connection = $row['dt_server'];
	$hora_reset = null;
	$hora_inicio = null;
	$imei = $row['imei'];
	$last_connection = convUserTimezone($last_connection);

	if ($cuenta_padre == "1") {
		$dia_corte = '27';
		$hora_reset = "23:55:00";
		$hora_inicio = "23:59:59";
	}
	if ($cuenta_padre == "2") {
		$dia_corte = '6';
		$hora_reset = '23:55:00';
		$hora_inicio = '23:59:59';
	}
	if ($hora_reset && $hora > $hora_reset) {
		$reinicio = 'true';
	}
	if ($hora_inicio && $hora > $hora_inicio) {
		$reinicio = 'false';
	}

	if ($cuenta_padre == "1" && $dia == $dia_corte && $reinicio == "true") {
		$q1 = "UPDATE gs_objects o
            LEFT JOIN gs_objects_reports r ON o.imei = r.imei SET o.seguimiento = 'false', o.contador = '0',
            r.seguimiento = '" . $dt_now . "', r.usuario = '0', r.repetidor = 'false'
            WHERE o.cuenta_padre = '1'";

		$r1 = mysqli_query($ms, $q1);

		$q = "UPDATE `gs_object_data` SET `contador`='0' WHERE `imei`='" . $imei . "'";
		$r = mysqli_query($ms, $q);
	} else if ($cuenta_padre == "2" && $dia == $dia_corte && $reinicio == "true") {
		$q1 = "UPDATE gs_objects o
            LEFT JOIN gs_objects_reports r ON o.imei = r.imei SET o.seguimiento = 'false', o.contador = '0',
            r.seguimiento = '" . $dt_now . "', r.usuario = '0', r.repetidor = 'false'
            WHERE o.cuenta_padre = '2'";
		$r1 = mysqli_query($ms, $q1);

		$q = "UPDATE `gs_object_data` SET `contador`='0' WHERE `imei`='" . $imei . "'";
		$r = mysqli_query($ms, $q);
	}
}

function cronCheckObjectReports() {
    global $ms, $email_client;

    $excluded_ids = "(1, 171, 172, 290, 311, 316, 320, 345, 689, 621, 720, 723, 766, 767, 768, 769, 770, 772, 1024, 1046, 1049, 1050, 1051, 1052, 1053, 1054, 1059, 1060, 1066, 1067, 1140, 1441, 1150, 1167, 1171, 1219, 1599, 1669)";
    $dt_now = gmdate("Y-m-d H:i:s");

    $q = "SELECT gs_objects.*, gs_user_objects.user_id, gs_users.email AS user_email
          FROM gs_objects
          LEFT JOIN gs_user_objects ON gs_objects.imei = gs_user_objects.imei
          INNER JOIN gs_users ON gs_user_objects.user_id = gs_users.id
          WHERE gs_users.manager_id = 0
          AND gs_objects.sim_number != '0'
          AND gs_user_objects.user_id IS NOT NULL
          AND gs_user_objects.user_id NOT IN $excluded_ids";

    $r = mysqli_query($ms, $q);

    while ($row = mysqli_fetch_array($r)) {
        $imei = $row['imei'];
        $name = $row['name'];
        $contador = $row['contador'];
        $user_id = $row['user_id'];
        $user_email = $row['user_email'];
        $last_connection = $row['dt_server'];
        $dt_difference = strtotime($dt_now) - strtotime($last_connection);

        if ($dt_difference >= 86400) {
            $days = round($dt_difference / 86400, 1);
            $event = "Sin reportar por $days días";

            addObjectReport($name, $imei, $dt_now, $contador, $event, $user_id, $user_email, $email_client);
        } else {
            clearObjectReportIfRecovered($imei);
        }
    }
}