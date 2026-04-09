<?
session_start();
include('../init.php');
include('fn_common.php');
checkUserSession();

loadLanguage($_SESSION["language"], $_SESSION["units"]);

if (@$_POST['cmd'] == 'delete_object_service') {
	$service_id = $_POST["service_id"];
	$imei = $_POST["imei"];

	$q0 = "SELECT event_id, user_id, type,name from  `gs_user_events` WHERE `maintenance_id`='" . $service_id . "'";
	$r = mysqli_query($ms, $q0);
	$row = mysqli_fetch_assoc($r);

	$event_id = $row['event_id'];
	$user_id = $row['user_id'];

	$q = "DELETE FROM `gs_user_events` WHERE `event_id`='" . $event_id . "' AND `user_id`='" . $user_id . "'";
	$r = mysqli_query($ms, $q);

	$q2 = "DELETE FROM `gs_user_events_status` WHERE `event_id`='" . $event_id . "'";
	$r = mysqli_query($ms, $q2);

	$q = "DELETE FROM `gs_object_services` WHERE `service_id`='" . $service_id . "' AND `imei`='" . $imei . "'";
	$r = mysqli_query($ms, $q);

	echo 'OK';
	die;
}

if (@$_POST['cmd'] == 'delete_selected_object_services') {
	$items = $_POST["items"];
	$imei = $_POST["imei"];

	for ($i = 0; $i < count($items); ++$i) {
		$item = $items[$i];

		$q0 = "SELECT event_id, user_id, type,name from  `gs_user_events` WHERE `maintenance_id`='" . $item . "'";
		$r = mysqli_query($ms, $q0);
		$row = mysqli_fetch_assoc($r);

		$event_id = $row['event_id'];
		$user_id = $row['user_id'];

		$q = "DELETE FROM `gs_user_events` WHERE `event_id`='" . $event_id . "' AND `user_id`='" . $user_id . "'";
		$r = mysqli_query($ms, $q);

		$q2 = "DELETE FROM `gs_user_events_status` WHERE `event_id`='" . $event_id . "'";
		$r = mysqli_query($ms, $q2);

		$q = "DELETE FROM `gs_object_services` WHERE `service_id`='" . $item . "' AND `imei`='" . $imei . "'";
		$r = mysqli_query($ms, $q);
	}

	echo 'OK';
	die;
}

if (@$_POST['cmd'] == 'save_object_service') {
	$user_id = $_SESSION["user_id"];
	$service_id = $_POST["service_id"];
	$plan = $_POST["plan"];
	$imei = $_POST["imei"];
	$name = $_POST["name"];
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
	$service_email = $_POST["service_email"];

	$update_last = $_POST["update_last"];
	$count = 0;

	// save in km
	$odo_interval = convDistanceUnits($odo_interval, $_SESSION["unit_distance"], 'km');
	$odo_last = convDistanceUnits($odo_last, $_SESSION["unit_distance"], 'km');
	$odo_left_num = convDistanceUnits($odo_left_num, $_SESSION["unit_distance"], 'km');

	if ($service_id == 'false') {


		if ($popup == 'false') {
			$alert = 'false,false,true,alarm1.mp3';
		} else {
			$alert = 'true,false,true,alarm1.mp3';
		}

		$qryUserEvents = "INSERT INTO gs_user_events (user_id,type,name,active,duration_from_last_event,duration_from_last_event_minutes,week_days,day_time,imei,sub_accounts,checked_value,route_trigger,zone_trigger,routes,zones,notify_system,notify_push,notify_email,notify_email_address,notify_sms,notify_sms_number,email_template_id,sms_template_id,notify_arrow,notify_arrow_color,notify_ohc,notify_ohc_color,webhook_send,webhook_url,cmd_send,cmd_gateway,cmd_type,cmd_string,maintenance_id)
	    VALUES ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s') ";


		$valores = [
			$user_id,
			'service',
			$name,
			'true',
			'false',
			0,
			'true,true,true,true,true,true,true',
			'{dt:false,mon:false,mon_from:00:00,mon_to:24:00,tue:false,tue_from:00:00,tue_to:24:00,wed:false,wed_from:00:00,wed_to:24:00,thu:false,thu_from:00:00,thu_to:24:00,fri:false,fri_from:00:00,fri_to:24:00,sat:false,sat_from:00:00,sat_to:24:00,sun:false,sun_from:00:00,sun_to:24:00}',
			$imei,
			'',
			'',
			'off',
			'off',
			'',
			'',
			$alert,
			'false',
			'false',
			'',
			'false',
			'',
			'false',
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

		$q = "INSERT INTO `gs_object_services` 	(`imei`,
			`plan`,
			`name`,
			`data_list`,
			`popup`,
			`odo`,
			`odo_interval`, 
			`odo_last`,
			`engh`,
			`engh_interval`,
			`engh_last`,
			`days`,
			`days_interval`,
			`days_last`,
			`odo_left`,
			`odo_left_num`,
			`engh_left`,
			`engh_left_num`,
			`days_left`,
			`days_left_num`,
			`count`,
			`update_last`)
			VALUES
			('" . $imei . "',
			'" . $plan . "',
			'" . $name . "',
			'" . $data_list . "',
			'" . $popup . "',
			'" . $odo . "',
			'" . $odo_interval . "',
			'" . $odo_last . "',
			'" . $engh . "',
			'" . $engh_interval . "',
			'" . $engh_last . "',
			'" . $days . "',
			'" . $days_interval . "',
			'" . $days_last . "',
			'" . $odo_left . "',
			'" . $odo_left_num . "',
			'" . $engh_left . "',
			'" . $engh_left_num . "',
			'" . $days_left . "',
			'" . $days_left_num . "',
			'" . $count . "',
			'" . $update_last . "')";


		$q1s = vsprintf($qryUserEvents, $valores);
		mysqli_query($ms, $q1s);
		$event_id = mysqli_insert_id($ms);
		$r = mysqli_query($ms, $q);
		$last_id = mysqli_insert_id($ms);


	} else {

		if ($popup == 'false') {
			$alert = 'false,false,true,alarm1.mp3';
		} else {
			$alert = 'true,false,true,alarm1.mp3';
		}

		$q = "UPDATE `gs_object_services` SET
            `plan`='" . $plan . "',
            `name`='" . $name . "',
            `data_list`='" . $data_list . "',
            `popup`='" . $popup . "',
            `odo`='" . $odo . "',
            `odo_interval`='" . $odo_interval . "',
            `odo_last`='" . $odo_last . "',
            `engh`='" . $engh . "',
            `engh_interval`='" . $engh_interval . "',
            `engh_last`='" . $engh_last . "',
            `days`='" . $days . "',
            `days_interval`='" . $days_interval . "',
            `days_last`='" . $days_last . "',
            `odo_left`='" . $odo_left . "',
            `odo_left_num`='" . $odo_left_num . "',
            `engh_left`='" . $engh_left . "',
            `engh_left_num`='" . $engh_left_num . "',
            `days_left`='" . $days_left . "',
            `days_left_num`='" . $days_left_num . "',
            `count`='" . $count . "',
            `update_last`='" . $update_last . "'
            WHERE `service_id`='" . $service_id . "';";

		$q .= "UPDATE `gs_user_events` SET
            `type`= 'service',
            `name`='" . $name . "',
			`notify_system`='" . $alert . "',
			`notify_email_address`='" . $service_email . "',
            `active`='true'
            WHERE `maintenance_id`='" . $service_id . "'";

		$r = mysqli_multi_query($ms, $q);
	}


	echo 'OK';
	die;
}

if (@$_GET['cmd'] == 'load_object_service_list') {
	$page = $_GET['page']; // get the requested page
	$limit = $_GET['rows']; // get how many rows we want to have into the grid
	$sidx = $_GET['sidx']; // get index row - i.e. user click to sort
	$sord = $_GET['sord']; // get the direction

	$imei = $_GET['imei'];

	if (!$sidx) $sidx = 1;

	// get records number
	$q = "SELECT * FROM `gs_object_services` WHERE `imei`='" . $imei . "'";
	$r = mysqli_query($ms, $q);
	$count = mysqli_num_rows($r);

	$q = "SELECT * FROM `gs_object_services` WHERE `imei`='" . $imei . "' ORDER BY $sidx $sord";
	$r = mysqli_query($ms, $q);

	$response = new stdClass();
	$response->page = 1;
	//$response->total = $count;
	$response->records = $count;

	if ($r) {
		// get real odometer and engine hours
		$odometer = getObjectOdometer($imei);
		$odometer = floor(convDistanceUnits($odometer, 'km', $_SESSION["unit_distance"]));

		$engine_hours = getObjectEngineHours($imei, false);

		$i = 0;
		while ($row = mysqli_fetch_array($r)) {
			$service_id = $row["service_id"];
			$name = $row['name'];
			$plan = $row['plan'];


			$status_arr = array();

			if ($row['odo'] == 'true') {
				$row['odo_interval'] = floor(convDistanceUnits($row['odo_interval'], 'km', $_SESSION["unit_distance"]));
				$row['odo_last'] = floor(convDistanceUnits($row['odo_last'], 'km', $_SESSION["unit_distance"]));

				$odo_diff = $odometer - $row['odo_last'];
				$odo_diff = $row['odo_interval'] - $odo_diff;

				if ($odo_diff <= 0) {
					$odo_diff = abs($odo_diff);
					$status_arr[] = '<font color="red">' . $la['ODOMETER_EXPIRED'] . ' (' . $odo_diff . ' ' . $la["UNIT_DISTANCE"] . ')</font>';
				} else {
					$status_arr[] = $la['ODOMETER_LEFT'] . ' (' . $odo_diff . ' ' . $la["UNIT_DISTANCE"] . ')';
				}
			}

			if ($row['engh'] == 'true') {
				$engh_diff = $engine_hours - $row['engh_last'];
				$engh_diff = $row['engh_interval'] - $engh_diff;

				if ($engh_diff <= 0) {
					$engh_diff = abs($engh_diff);
					$status_arr[] = '<font color="red">' . $la['ENGINE_HOURS_EXPIRED'] . ' (' . $engh_diff . ' ' . $la["UNIT_H"] . ')</font>';
				} else {
					$status_arr[] = $la['ENGINE_HOURS_LEFT'] . ' (' . $engh_diff . ' ' . $la["UNIT_H"] . ')';
				}
			}

			if ($row['days'] == 'true') {
				$days_diff = strtotime(gmdate("Y-m-d")) - (strtotime($row['days_last']));
				$days_diff = floor($days_diff / 3600 / 24);
				$days_diff = $row['days_interval'] - $days_diff;

				if ($days_diff <= 0) {
					$days_diff = abs($days_diff);
					$status_arr[] = '<font color="red">' . $la['DAYS_EXPIRED'] . ' (' . $days_diff . ')</font>';
				} else {
					$status_arr[] = $la['DAYS_LEFT'] . ' (' . $days_diff . ')';
				}
			}
			$status_arr[] = $la['TYPE'] . ' (' . $plan . ')';
			$status = caseToLower(implode(", ", $status_arr));

			// set modify buttons
			$modify = '<a href="#" onclick="settingsObjectServiceProperties(\'' . $service_id . '\');" title="' . $la['EDIT'] . '"><img src="theme/images/edit.svg" />';
			$modify .= '</a><a href="#" onclick="settingsObjectServiceDelete(\'' . $service_id . '\');" title="' . $la['DELETE'] . '"><img src="theme/images/remove3.svg" /></a>';
			// set row
			$response->rows[$i]['id'] = $service_id;
			$response->rows[$i]['cell'] = array($name, $status, $modify);
			$i++;
		}
	}

	header('Content-type: application/json');
	echo json_encode($response);
	die;
}
