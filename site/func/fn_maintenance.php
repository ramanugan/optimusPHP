<?
session_start();
include('../init.php');
include('fn_common.php');
checkUserSession();

loadLanguage($_SESSION["language"], $_SESSION["units"]);

// check privileges
if ($_SESSION["privileges"] == 'subuser') {
	$user_id = $_SESSION["manager_id"];
} else {
	$user_id = $_SESSION["user_id"];
}

if (@$_GET['cmd'] == 'load_maintenance_list') {
	$page = $_GET['page']; // get the requested page
	$limit = $_GET['rows']; // get how many rows we want to have into the grid
	$sidx = $_GET['sidx']; // get index row - i.e. user click to sort
	$sord = $_GET['sord']; // get the direction

	if (!$sidx) $sidx = 1;

	// get records number	
	if ($_SESSION["privileges"] == 'subuser') {
		$q = "SELECT gs_objects.*, gs_object_services.*
				FROM gs_objects
				INNER JOIN gs_object_services ON gs_objects.imei = gs_object_services.imei
				WHERE gs_object_services.imei IN (" . $_SESSION["privileges_imei"] . ")";
	} else {
		$q = "SELECT gs_objects.*, gs_object_services.*
				FROM gs_objects
				INNER JOIN gs_object_services ON gs_objects.imei = gs_object_services.imei
				WHERE gs_object_services.imei IN (" . getUserObjectIMEIs($user_id) . ")";
	}

	$r = mysqli_query($ms, $q);

	if (!$r) {
		die;
	}

	$count = mysqli_num_rows($r);

	if ($count > 0) {
		$total_pages = ceil($count / $limit);
	} else {
		$total_pages = 1;
	}

	if ($page > $total_pages) $page = $total_pages;
	$start = $limit * $page - $limit; // do not put $limit*($page - 1)

	if ($_SESSION["privileges"] == 'subuser') {
		$q = "SELECT gs_objects.*, gs_object_services.*
				FROM gs_objects
				INNER JOIN gs_object_services ON gs_objects.imei = gs_object_services.imei
				WHERE gs_object_services.imei IN (" . $_SESSION["privileges_imei"] . ")";
	} else {
		$q = "SELECT gs_objects.*, gs_object_services.*
				FROM gs_objects
				INNER JOIN gs_object_services ON gs_objects.imei = gs_object_services.imei
				WHERE gs_object_services.imei IN (" . getUserObjectIMEIs($user_id) . ")";
	}

	$q .=  " ORDER BY $sidx $sord LIMIT $start, $limit";
	$r = mysqli_query($ms, $q);

	$response = new stdClass();
	$response->page = $page;
	$response->total = $total_pages;
	$response->records = $count;

	if ($r) {
		$i = 0;
		while ($row = mysqli_fetch_array($r)) {
			$service_id = $row['service_id'];
			$imei = $row['imei'];
			$object_name = getObjectName($imei);
			$plan = $row['plan'];
			$name = $row['name'];

			$odometer = getObjectOdometer($imei);
			$odometer = floor(convDistanceUnits($odometer, 'km', $_SESSION["unit_distance"]));

			$odometer_left = '-';

			if ($row['odo'] == 'true') {
				$row['odo_interval'] = floor(convDistanceUnits($row['odo_interval'], 'km', $_SESSION["unit_distance"]));
				$row['odo_last'] = floor(convDistanceUnits($row['odo_last'], 'km', $_SESSION["unit_distance"]));

				$odo_diff = $odometer - $row['odo_last'];
				$odo_diff = $row['odo_interval'] - $odo_diff;

				if ($odo_diff <= 0) {
					$odo_diff = abs($odo_diff);
					$odometer_left = '<font color="red">' . $la["EXPIRED"] . ' (' . $odo_diff . ' ' . $la["UNIT_DISTANCE"] . ')</font>';
				} else {
					$odometer_left = $odo_diff . ' ' . $la["UNIT_DISTANCE"];
				}
			}

			$odometer = $odometer . ' ' . $la["UNIT_DISTANCE"];

			$engine_hours = getObjectEngineHours($imei, false);

			$engine_hours_left = '-';

			if ($row['engh'] == 'true') {
				$engh_diff = $engine_hours - $row['engh_last'];
				$engh_diff = $row['engh_interval'] - $engh_diff;

				if ($engh_diff <= 0) {
					$engh_diff = abs($engh_diff);
					$engine_hours_left = '<font color="red">' . $la["EXPIRED"] . ' (' . $engh_diff . ' ' . $la["UNIT_H"] . ')</font>';
				} else {
					$engine_hours_left = $engh_diff . ' ' . $la["UNIT_H"];
				}
			}

			$engine_hours = $engine_hours . ' ' . $la["UNIT_H"];

			$days = '-';
			$days_left = '-';
			$dia_limite = $row['days_left_num'];

			if ($row['days'] == 'true') {
				$days_diff = strtotime(gmdate("Y-m-d")) - (strtotime($row['days_last']));
				$days_diff = floor($days_diff / 3600 / 24);
				$days = $days_diff;
				$days_diff = $row['days_interval'] - $days_diff;

				if ($days_diff <= $dia_limite) {
					$days_left = abs($days_diff);
					$days_left = '<font color="red">' . $la["EXPIRED"] . ' (' . $days_left . ' ' . $la["UNIT_D"] . ')</font>';
				} else {
					$days_left = $days_diff;
				}
			}

			if (($row['odo_left'] == 'true') || ($row['engh_left'] == 'true') || ($row['days_left'] == 'true')) {
				$event = '<img src="theme/images/tick-green.svg" />';
			} else {
				$event = '<img src="theme/images/remove-red.svg" style="width:12px;" />';
			}

			// set modify buttons
			$modify = '<a href="#" onclick="maintenanceObjectServiceProperties(\'' . $imei . '\',\'' . $service_id . '\');" title="' . $la['EDIT'] . '"><img src="theme/images/edit.svg" />';
			$modify .= '</a><a href="#" onclick="maintenanceServiceDelete(\'' . $service_id . '\');" title="' . $la['DELETE'] . '"><img src="theme/images/remove3.svg" /></a>';
			// set row

			// set row
			$response->rows[$i]['id'] = $service_id;
			$response->rows[$i]['cell'] = array($object_name, $plan, $name, $odometer, $odometer_left, $engine_hours, $engine_hours_left, $days, $days_left, $event, $modify);
			$i++;
		}
	}

	header('Content-type: application/json');
	echo json_encode($response);
	die;
}

if (@$_POST['cmd'] == 'save_service') {
	$plan = $_POST["plan"];
	$name = $_POST["name"];
	$imei = $_POST["imei"];
	$data_list = $_POST["data_list"];
	$popup = $_POST["popup"];
	$odo = $_POST["odo"];
	$odo_interval = $_POST["odo_interval"];
	$odo_last = $_POST["odo_last"];
	$engh = $_POST["engh"];
	$engh_interval = $_POST["engh_interval"];
	$engh_last = $_POST["engh_last"];
	$days = $_POST["days"];
	$days_interval = $_POST["days_interval"];
	$days_last = $_POST["days_last"];

	$odo_left = $_POST["odo_left"];
	$odo_left_num = $_POST["odo_left_num"];
	$engh_left = $_POST["engh_left"];
	$engh_left_num = $_POST["engh_left_num"];
	$days_left = $_POST["days_left"];
	$days_left_num = $_POST["days_left_num"];
	$event_email = $_POST["service_email"];

	$update_last = $_POST["update_last"];
	$count = 0;

	if ($odo == 'true') {
		$tipo_servicio = 'odometer';
		$plantilla = '19';
	} elseif ($engh == 'true') {
		$tipo_servicio = 'hours';
		$plantilla = '20';
	} elseif ($days == 'true') {
		$tipo_servicio = 'days';
		$plantilla = '21';
	} else {
		$tipo_servicio = 'ninguno';
		$plantilla = '';
	}
	
	// save in km
	$odo_interval = convDistanceUnits($odo_interval, $_SESSION["unit_distance"], 'km');
	$odo_last = convDistanceUnits($odo_last, $_SESSION["unit_distance"], 'km');
	$odo_left_num = convDistanceUnits($odo_left_num, $_SESSION["unit_distance"], 'km');

	$imeis = explode(',', $imei);

	foreach ($imeis as &$singleImei) {
		$underscorePos = strpos($singleImei, '_');
	
		if ($underscorePos !== false) {
			$singleImei = substr($singleImei, 0, $underscorePos);
		}
	
		$qObjectServices = "INSERT INTO `gs_object_services` 
			(`imei`, `plan`, `name`, `data_list`, `popup`, `odo`, `odo_interval`, `odo_last`, `engh`, `engh_interval`,
			`engh_last`, `days`, `days_interval`, `days_last`, `odo_left`, `odo_left_num`, `engh_left`, `engh_left_num`,
			`days_left`, `days_left_num`, `count`, `update_last`)
			VALUES ('" . $singleImei . "', '" . $plan . "', '" . $name . "', '" . $data_list . "', '" . $popup . "',
			'" . $odo . "', '" . $odo_interval . "', '" . $odo_last . "', '" . $engh . "', '" . $engh_interval . "',
			'" . $engh_last . "', '" . $days . "', '" . $days_interval . "', '" . $days_last . "', '" . $odo_left . "',
			'" . $odo_left_num . "', '" . $engh_left . "', '" . $engh_left_num . "', '" . $days_left . "', '" . $days_left_num . "',
			'" . $count . "', '" . $update_last . "')";
	
		mysqli_query($ms, $qObjectServices);
		$last_id = mysqli_insert_id($ms);


		if($popup == 'false'){
			$alert = 'false,false,true,alarm1.mp3';
		} else{
			$alert = 'true,false,true,alarm1.mp3';
		}
	
		// Insertar en gs_user_events
		$qryUserEvents = "INSERT INTO gs_user_events (user_id, type, name, active, duration_from_last_event, duration_from_last_event_minutes, 
			week_days, day_time, imei, sub_accounts, checked_value, route_trigger, zone_trigger, routes, zones, notify_system, notify_push, 
			notify_email, notify_email_address, notify_sms, notify_sms_number, email_template_id, sms_template_id, notify_arrow, notify_arrow_color, 
			notify_ohc, notify_ohc_color, webhook_send, webhook_url, cmd_send, cmd_gateway, cmd_type, cmd_string, maintenance_id)
			VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', 
			'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s') ";
	
		$valores = [
			$user_id,
			'service',
			$name,
			'true',
			'false',
			0,
			'true,true,true,true,true,true,true',
			'{dt:false,mon:false,mon_from:00:00,mon_to:24:00,tue:false,tue_from:00:00,tue_to:24:00,wed:false,wed_from:00:00,wed_to:24:00,
			thu:false,thu_from:00:00,thu_to:24:00,fri:false,fri_from:00:00,fri_to:24:00,sat:false,sat_from:00:00,sat_to:24:00,sun:false,
			sun_from:00:00,sun_to:24:00}',
			$singleImei,
			'',
			'',
			'off',
			'off',
			'',
			'',
			$alert,
			'false',
			'true',
			$event_email,
			'false',
			'',
			$plantilla,
			'',
			'true',
			'arrow_red',
			'true',
			'#FF002B',
			'false',
			'',
			'false',
			'gprs',
			'ascii',
			'',
			'',
			$last_id
		];
	
		$qUserEvents = vsprintf($qryUserEvents, $valores);
		mysqli_query($ms, $qUserEvents);
		$event_id = mysqli_insert_id($ms);
		$object = getObjectName($singleImei);
		addRowBinnacle($_SESSION["user_id"], 'Creación Mtto: ' . $name . " gps: " . $object);
	}
	
	
	echo 'OK';
	die;
}

if (@$_POST['cmd'] == 'delete_service') {
	$service_id = $_POST["service_id"];

	$q0 = "SELECT event_id, user_id, type,name from  `gs_user_events` WHERE `maintenance_id`='" . $service_id . "'";
    $r = mysqli_query($ms, $q0);
    $row = mysqli_fetch_assoc($r);

    $event_id = $row['event_id'];
    $user_id = $row['user_id'];
    $imei = $row['imei'];
	$object = getObjectName($imei);

	$q = "DELETE FROM `gs_user_events` WHERE `event_id`='" . $event_id . "' AND `user_id`='" . $user_id . "'";
    $r = mysqli_query($ms, $q);

    $q2 = "DELETE FROM `gs_user_events_status` WHERE `event_id`='" . $event_id . "'";
    $r = mysqli_query($ms, $q2);

	$q = "DELETE FROM `gs_object_services` WHERE `service_id`='" . $service_id . "'";
	$r = mysqli_query($ms, $q);


	addRowBinnacle($_SESSION["user_id"], 'Se elimina Mtto: ' . $name . " gps: " . $object);

	echo 'OK';
	die;
}

if (@$_POST['cmd'] == 'delete_selected_services') {
	$items = $_POST["items"];

	for ($i = 0; $i < count($items); ++$i) {
		$item = $items[$i];
		$object = getObjectName($imeis[$i]);


		$q0 = "SELECT event_id, user_id, type, name from  `gs_user_events` WHERE `maintenance_id`='" . $item . "'";
        $r = mysqli_query($ms, $q0);
        $row = mysqli_fetch_assoc($r);

        $event_id = $row['event_id'];
		$user_id = $row['user_id'];

		$q = "DELETE FROM `gs_user_events` WHERE `event_id`='" . $event_id . "' AND `user_id`='" . $user_id . "'";
		$r = mysqli_query($ms, $q);
		
		$q2 = "DELETE FROM `gs_user_events_status` WHERE `event_id`='" . $event_id . "'";
		$r = mysqli_query($ms, $q2);

		$q = "DELETE FROM `gs_object_services` WHERE `service_id`='" . $item . "'";
		$r = mysqli_query($ms, $q);


	    addRowBinnacle($_SESSION["user_id"], 'Se elimina Mtto: ' . $name . " gps: " . $object);
	}

	echo 'OK';
	die;
}

if (@$_POST['cmd'] == 'send_email_selected_services') {
	$email = $_POST["email"];
	$id = $_POST["id"];

	for ($i = 0; $i < count($id); ++$i) {
		$item = $id[$i];

		$q0 = "SELECT * FROM  `gs_user_events` WHERE `maintenance_id`='" . $item . "'";
        $r = mysqli_query($ms, $q0);
        $row = mysqli_fetch_assoc($r);

        $event_id = $row['event_id'];
		$user_id = $row['user_id'];
		$imei = $row['imei'];
		$name = $row['name'];
		$object = getObjectName($imei);


		$q = "UPDATE gs_user_events SET `notify_email_address`='" . $email . "' WHERE `maintenance_id`='" . $item . "'";
		$r = mysqli_query($ms, $q);

	    addRowBinnacle($_SESSION["user_id"], 'Se actualiza e-mail de Mtto: ' . $name . " gps: " . $object . " E-mail: " . $email);
	}

	echo 'OK';
	die;
}
