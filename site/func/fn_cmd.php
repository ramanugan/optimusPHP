<?
session_start();
include('../init.php');
include('fn_common.php');
include('../tools/sms.php');
checkUserSession();

loadLanguage($_SESSION["language"], $_SESSION["units"]);

// check privileges
if ($_SESSION["privileges"] == 'subuser') {
	$user_id = $_SESSION["manager_id"];
} else {
	$user_id = $_SESSION["user_id"];
}

if (@$_GET['cmd'] == 'load_cmd_gprs_exec_list') {
	$page = $_GET['page']; // get the requested page
	$limit = $_GET['rows']; // get how many rows we want to have into the grid
	$sidx = $_GET['sidx']; // get index row - i.e. user click to sort
	$sord = $_GET['sord']; // get the direction

	if (!$sidx) $sidx = 1;

	// get records number
	if ($_SESSION["privileges"] == 'subuser') {
		$q = "SELECT * FROM `gs_object_cmd_exec` WHERE `imei` IN (" . $_SESSION["privileges_imei"] . ") AND `gateway`='gprs' and `user_id`='" . $user_id . "'";
	} else {
		$q = "SELECT * FROM `gs_object_cmd_exec` WHERE `imei` IN (" . getUserObjectIMEIs($user_id) . ") AND `gateway`='gprs' and `user_id`='" . $user_id . "'";
	}

	$r = mysqli_query($ms, $q);

	if (!$r) {
		die;
	}

	$count = mysqli_num_rows($r);

	if ($_SESSION["privileges"] == 'subuser') {
		$q = "SELECT * FROM `gs_object_cmd_exec` WHERE `imei` IN (" . $_SESSION["privileges_imei"] . ") AND `gateway`='gprs' and `user_id`='" . $user_id . "' ORDER BY $sidx $sord";
	} else {
		$q = "SELECT * FROM `gs_object_cmd_exec` WHERE `imei` IN (" . getUserObjectIMEIs($user_id) . ") AND `gateway`='gprs' and `user_id`='" . $user_id . "' ORDER BY $sidx $sord";
	}

	$r = mysqli_query($ms, $q);

	$response = new stdClass();
	$response->page = 1;
	//$response->total = $count;
	$response->records = $count;

	if ($r) {
		$i = 0;
		while ($row = mysqli_fetch_array($r)) {
			$cmd_id = $row['cmd_id'];
			$time = convUserTimezone($row['dt_cmd']);
			$object = getObjectName($row['imei']);

			$name = $row['name'];
			$type = strtoupper($row['type']);
			if ($_SESSION["cpanel_privileges"] == 'super_admin') { //para que no aparescan los comandos a los usuarios
				$cmd = $row['cmd'];
			} else {
				$cmd = "";
			} //

			if ($row['status'] == 0) {
				$status = '<span class="spinner" style="height: 3px;"></span>';
			} else if ($row['status'] == 1) {
				$status = '<img src="theme/images/tick-green.svg" />';
			}

			$re_hex = $row['re_hex'];

			// set modify buttons
			$modify = '<a href="#" onclick="cmdGPRSExecDelete(\'' . $cmd_id . '\');" title="' . $la['DELETE'] . '"><img src="theme/images/remove3.svg" /></a>';
			// set row
			$response->rows[$i]['id'] = $cmd_id;
			$response->rows[$i]['cell'] = array($time, $object, $name, $cmd, $status, $modify, $re_hex);
			$i++;
		}
	}

	header('Content-type: application/json');
	echo json_encode($response);
	die;
} // primera parte ok!

if (@$_GET['cmd'] == 'load_cmd_sms_exec_list') {
	$page = $_GET['page']; // get the requested page
	$limit = $_GET['rows']; // get how many rows we want to have into the grid
	$sidx = $_GET['sidx']; // get index row - i.e. user click to sort
	$sord = $_GET['sord']; // get the direction

	if (!$sidx) $sidx = 1;

	// get records number
	if ($_SESSION["privileges"] == 'subuser') {
		$q = "SELECT * FROM `gs_object_cmd_exec` WHERE `imei` IN (" . $_SESSION["privileges_imei"] . ") AND `gateway`='sms' and `user_id`='" . $user_id . "'";
	} else {
		$q = "SELECT * FROM `gs_object_cmd_exec` WHERE `imei` IN (" . getUserObjectIMEIs($user_id) . ") AND `gateway`='sms' and `user_id`='" . $user_id . "'";
	}

	$r = mysqli_query($ms, $q);

	if (!$r) {
		die;
	}

	$count = mysqli_num_rows($r);

	if ($_SESSION["privileges"] == 'subuser') {
		$q = "SELECT * FROM `gs_object_cmd_exec` WHERE `imei` IN (" . $_SESSION["privileges_imei"] . ") AND `gateway`='sms' and `user_id`='" . $user_id . "' ORDER BY $sidx $sord";
	} else {
		$q = "SELECT * FROM `gs_object_cmd_exec` WHERE `imei` IN (" . getUserObjectIMEIs($user_id) . ") AND `gateway`='sms' and `user_id`='" . $user_id . "' ORDER BY $sidx $sord";
	}

	$r = mysqli_query($ms, $q);

	$response = new stdClass();
	$response->page = 1;
	//$response->total = $count;
	$response->records = $count;

	if ($r) {
		$i = 0;
		while ($row = mysqli_fetch_array($r)) {
			$cmd_id = $row['cmd_id'];
			$time = convUserTimezone($row['dt_cmd']);
			$object = getObjectName($row['imei']);

			$name = $row['name'];
			$type = strtoupper($row['type']);
			if ($_SESSION["cpanel_privileges"] == 'super_admin') { //para que no aparescan los comandos sms a los usuarios
				$cmd = $row['cmd'];
			} else {
				$cmd = "";
			}

			if ($row['status'] == 0) {
				$status = '<span class="spinner" style="height: 3px;"></span>';
			} else if ($row['status'] == 1) {
				$status = '<img src="theme/images/tick-green.svg" />';
			}

			$re_hex = $row['re_hex'];

			// set modify buttons
			$modify = '<a href="#" onclick="cmdSMSExecDelete(\'' . $cmd_id . '\');" title="' . $la['DELETE'] . '"><img src="theme/images/remove3.svg" /></a>';
			// set row
			$response->rows[$i]['id'] = $cmd_id;
			$response->rows[$i]['cell'] = array($time, $object, $name, $cmd, $status, $modify, $re_hex);
			$i++;
		}
	}

	header('Content-type: application/json');
	echo json_encode($response);
	die;
} //segunda parte ok!

if (@$_POST['cmd'] == 'exec_cmd_gprs') {
	$result = false;
	$user = $_SESSION["cpanel_user_id"];

	$imei = $_POST["imei"];
	$name = $_POST["name"];
	$type = $_POST["type"];
	$cmd_ = $_POST["cmd_"];

	$imeis = explode(',', $imei);

	$alerts = [];

	for ($i = 0; $i < count($imeis); ++$i) {
		$imei = $imeis[$i];
		$object = getObjectName($imei);
	
		if ($_SESSION["cpanel_privileges"] != 'admin' && $_SESSION["cpanel_privileges"] != 'super_admin') {
			addRowBinnacle($_SESSION["user_id"], 'Envio de comando "gprs": ' . $name . " gps: " . $object, $cmd_);
		} elseif ($_SESSION["cpanel_privileges"] == 'admin') {
			addRowBinnacle($_SESSION["user_id"], 'Envio de comando "gprs": ' . $name . " gps: " . $object, $cmd_);
		}
	
		if ($cmd_ == 'BASICO' || $cmd_ == 'TANQUES' || $cmd_ == 'TEMP') {
			CreateCommandConfigGv300($user, $imei, $name, $type);
		} else {
			sendObjectGPRSCommand($user_id, $imei, $name, $type, $cmd_);
		}
	
		if (getObjectSevice($imei, $name) === 'true') {
			$alerts[] = $object;
		}
	}
	
	if (!empty($alerts)) {
		echo json_encode([
			'status' => 'ALERT_SERVICE',
			'objects' => $alerts
		]);
		die;
	}
	
	echo 'OK';
	die;
	
}

if (@$_POST['cmd'] == 'exec_cmd_sms') {
	$result = false;

	$imei = $_POST["imei"];
	$imei = array_map(function ($i) {

		if (preg_match('/(\d+)_(\d+)/', $i, $result)) {
			$imei_correcto = $result[1];
		} else {
			$imei_correcto = $i;
		}
		return $imei_correcto;
	}, explode(",", $_POST["imei"]));


	$name = $_POST["name"];
	$cmd_ = $_POST["cmd_"];

	$imeis = $imei;

	$alerts = [];

	for ($i = 0; $i < count($imeis); ++$i) {
		$imei = $imeis[$i];
		$object = getObjectName($imei);
	
		if ($_SESSION["cpanel_privileges"] != 'admin' && $_SESSION["cpanel_privileges"] != 'super_admin') {
			addRowBinnacle($_SESSION["user_id"], 'Envio de comando "sms": ' . $name . " gps: " . $object, $cmd_);
		} elseif ($_SESSION["cpanel_privileges"] == 'admin') {
			addRowBinnacle($_SESSION["user_id"], 'Envio de comando "sms": ' . $name . " gps: " . $object, $cmd_);
		}
	
		$send_ok = sendObjectSMSCommand($user_id, $imei, $name, $cmd_);
	
		if (getObjectSevice($imei, $name) === 'true') {
			$alerts[] = $object;
		}
	}
	
	if (!empty($alerts)) {
        echo json_encode([
            'status'  => 'ALERT_SERVICE',
            'objects' => $alerts
        ]);
        exit;
    }

    if (!$send_ok) {
        echo json_encode([
            'status' => 'ERROR_NOT_SENT'
        ]);
        exit;
    }
	
    echo json_encode([
        'status' => 'OK'
    ]);
    exit;
}

if (@$_POST['cmd'] == 'exec_stream_cmd_sms') {
	$result = false;

	$imei = $_POST["imei"];
	$imei = array_map(function ($i) {

		if (preg_match('/(\d+)_(\d+)/', $i, $result)) {
			$imei_correcto = $result[1];
		} else {
			$imei_correcto = $i;
		}
		return $imei_correcto;
	}, explode(",", $_POST["imei"]));


	$name = $_POST["name"];
	$q1 = "SELECT * FROM `gs_user_cmd` WHERE `name`='$name' AND `gateway`='sms'";
	$q2 = "SELECT * FROM `gs_object_streams` WHERE `imei`='$imei[0]'";

	$r1 = mysqli_query($ms, $q1);
	$r2 = mysqli_query($ms, $q2);
	$row1 = mysqli_fetch_array($r1);
	$row2 = mysqli_fetch_array($r2);

	$cmd_ = $row1["cmd"];
	$url = $row2["url_stream"];

	$cmd_ = str_replace('URL', $url, $cmd_);

	$imeis = $imei;

	for ($i = 0; $i < count($imeis); ++$i) {
		$object = getObjectName($imeis[$i]);

		if ($_SESSION["cpanel_privileges"] != 'admin' && $_SESSION["cpanel_privileges"] != 'super_admin') {
			addRowBinnacle($_SESSION["user_id"], 'Envio de comando "sms": ' . $name . " gps: " . $object, $cmd_);
		}
		if ($_SESSION["cpanel_privileges"] == 'admin') {
			addRowBinnacle($_SESSION["user_id"], 'Envio de comando "sms": ' . $name . " gps: " . $object, $cmd_);
		}
		$result = sendObjectSMSCommand($user_id, $imeis[$i], $name, $cmd_);
		if ($result == false) {
			break;
		}
		if ($name == 'CloseStream') {
			$q1 = "SELECT * FROM `gs_user_cmd` WHERE `name`='CloseStream_1' AND `gateway`='sms'";
			$r1 = mysqli_query($ms, $q1);
			$row1 = mysqli_fetch_array($r1);
			$cmd_ = $row1["cmd"];
			$cmd_ = str_replace('URL', $url, $cmd_);

			$imeis = $imei;

			$result = sendObjectSMSCommand($user_id, $imeis[$i], $name, $cmd_);
			if ($result == false) {
				break;
			}
		}
	}

	if ($result == false) {
		echo 'ERROR_NOT_SENT';
		die;
	}

	echo 'OK';
	die;
}

if (@$_POST['cmd'] == 'delete_cmd_exec') {

    $cmd_id = $_POST["cmd_id"];

    $q_info = "SELECT name, cmd, imei FROM gs_object_cmd_exec WHERE cmd_id = '" . mysqli_real_escape_string($ms, $cmd_id) . "' AND user_id = '" . $user_id . "' LIMIT 1";

    $r_info = mysqli_query($ms, $q_info);
	$row = mysqli_fetch_array($r_info);

        $cmd_name = $row['name'];
        $cmd_text = $row['cmd'];
        $imei = $row['imei'];

    $object = getObjectName($imei);

    $q = "DELETE FROM gs_object_cmd_exec 
          WHERE cmd_id='" . mysqli_real_escape_string($ms, $cmd_id) . "' 
          AND user_id='" . $user_id . "'";

    $r = mysqli_query($ms, $q);

    if ($r) {
        addRowBinnacle($_SESSION["user_id"], 'Comando Eliminado: ' . $cmd_name . '" GPS: ' . $object, $cmd_text);
        echo 'OK';
    }

    die;
}

if (@$_POST['cmd'] == 'delete_selected_cmd_execs') {
    $items = $_POST["items"];

    for ($i = 0; $i < count($items); ++$i) {
        $cmd_id = mysqli_real_escape_string($ms, $items[$i]);

        $q_info = "SELECT name, cmd, imei FROM gs_object_cmd_exec WHERE cmd_id = '$cmd_id' AND user_id = '$user_id' LIMIT 1";
        $r_info = mysqli_query($ms, $q_info);

        if ($r_info && $row = mysqli_fetch_assoc($r_info)) {
            $cmd_name = $row['name'];
            $cmd_text = $row['cmd'];
            $imei = $row['imei'];

            $object = getObjectName($imei);

            $q_delete = "DELETE FROM gs_object_cmd_exec 
                         WHERE cmd_id='$cmd_id' AND user_id='$user_id'";
            $r_delete = mysqli_query($ms, $q_delete);

            if ($r_delete && mysqli_affected_rows($ms) > 0) {
                addRowBinnacle($_SESSION["user_id"], 'Comando Eliminado: "' . $cmd_name . '" GPS: ' . $object, $cmd_text);
            }
        }
    }

    echo 'OK';
    die;
}

if (@$_GET['cmd'] == 'load_cmd_schedule_list') {
	$page = $_GET['page']; // get the requested page
	$limit = $_GET['rows']; // get how many rows we want to have into the grid
	$sidx = $_GET['sidx']; // get index row - i.e. user click to sort
	$sord = $_GET['sord']; // get the direction

	if (!$sidx) $sidx = 1;

	// get records number
	$q = "SELECT * FROM `gs_user_cmd_schedule` WHERE `user_id`='" . $user_id . "'";
	$r = mysqli_query($ms, $q);
	$count = mysqli_num_rows($r);

	if ($count > 0) {
		$total_pages = ceil($count / $limit);
	} else {
		$total_pages = 1;
	}

	if ($page > $total_pages) $page = $total_pages;
	$start = $limit * $page - $limit; // do not put $limit*($page - 1)

	$q = "SELECT * FROM `gs_user_cmd_schedule` WHERE `user_id`='" . $user_id . "' ORDER BY $sidx $sord LIMIT $start, $limit";
	$r = mysqli_query($ms, $q);

	$response = new stdClass();
	$response->page = $page;
	$response->total = $total_pages;
	$response->records = $count;

	if ($r) {
		$i = 0;
		while ($row = mysqli_fetch_array($r)) {
			$cmd_id = $row['cmd_id'];
			$name = $row['name'];

			if ($row['exact_time'] == 'true') {
				$schedule = $la['EXACT_TIME'];
			} else {
				$schedule = $la['RECURRING'];
			}

			$gateway = strtoupper($row['gateway']);
			$type = strtoupper($row['type']);
			$cmd = $row['cmd'];

			if ($row['active'] == 'true') {
				$active = '<img src="theme/images/tick-green.svg" />';
			} else {
				$active = '<img src="theme/images/remove-red.svg" style="width:12px;" />';
			}

			// set modify buttons
			$modify = '<a href="#" onclick="cmdScheduleProperties(\'' . $cmd_id . '\');" title="' . $la['EDIT'] . '"><img src="theme/images/edit.svg" />';
			$modify .= '<a href="#" onclick="cmdScheduleDelete(\'' . $cmd_id . '\');" title="' . $la['DELETE'] . '"><img src="theme/images/remove3.svg" /></a>';
			// set row
			$response->rows[$i]['id'] = $cmd_id;
			$response->rows[$i]['cell'] = array($name, $active, $schedule, $gateway, $type, $cmd, $modify);
			$i++;
		}
	}

	header('Content-type: application/json');
	echo json_encode($response);
	die;
} // tercera parte ok!

if (@$_POST['cmd'] == 'load_cmd_schedule') {
	$result = array();

	$cmd_id = $_POST['cmd_id'];

	$q = "SELECT * FROM `gs_user_cmd_schedule` WHERE `cmd_id`='" . $cmd_id . "'";
	$r = mysqli_query($ms, $q);
	$row = mysqli_fetch_array($r);

	$day_time = json_decode($row['day_time'], true);

	$result = array(
		'name' => $row['name'],
		'active' => $row['active'],
		'exact_time' => $row['exact_time'],
		'exact_time_dt' => $row['exact_time_dt'],
		'day_time' => $day_time,
		'protocol' => $row['protocol'],
		'imei' => $row['imei'],
		'gateway' => $row['gateway'],
		'type' => $row['type']
	);
		$result['cmd'] = $row['cmd'];


	echo json_encode($result);
	die;
}

if (@$_POST['cmd'] == 'save_cmd_schedule') {
	$cmd_id = $_POST["cmd_id"];
	$name = $_POST["name"];
	$active = $_POST["active"];
	$exact_time = $_POST["exact_time"];
	$exact_time_dt = $_POST["exact_time_dt"];
	$day_time = $_POST["day_time"];
	$protocol = $_POST["protocol"];
	$imei = $_POST["imei"];
	$gateway = $_POST["gateway"];
	$type = $_POST["type"];
	$cmd_ = $_POST["cmd_"];

	if ($cmd_id == 'false') {
		$q = "INSERT INTO `gs_user_cmd_schedule` (`user_id`, `name`, `active`, `exact_time`, `exact_time_dt`, `day_time`, `protocol`, `imei`, `gateway`, `type`, `cmd`)
			  VALUES ('$user_id', '$name', '$active', '$exact_time', '$exact_time_dt', '$day_time', '$protocol', '$imei', '$gateway', '$type', '$cmd_')";
			mysqli_query($ms, $q);
	} else {
		$q_check = "SELECT * FROM `gs_user_cmd_schedule` WHERE `cmd_id` = '$cmd_id'";
		$r_check = mysqli_query($ms, $q_check);
		$row = mysqli_fetch_assoc($r_check);

		$fields = [];

		if ($row['name'] !== $name) $fields[] = "`name` = '$name'";
		if ($row['active'] !== $active) $fields[] = "`active` = '$active'";
		if ($row['exact_time'] !== $exact_time) $fields[] = "`exact_time` = '$exact_time'";
		if ($row['exact_time_dt'] !== $exact_time_dt) $fields[] = "`exact_time_dt` = '$exact_time_dt'";
		if ($row['day_time'] !== $day_time) $fields[] = "`day_time` = '$day_time'";
		if ($row['protocol'] !== $protocol) $fields[] = "`protocol` = '$protocol'";
		if ($row['imei'] !== $imei) $fields[] = "`imei` = '$imei'";
		if ($row['gateway'] !== $gateway) $fields[] = "`gateway` = '$gateway'";
		if ($row['type'] !== $type) $fields[] = "`type` = '$type'";
		if ($row['cmd'] !== $cmd_) $fields[] = "`cmd` = '$cmd_'";

		if (!empty($fields)) {
			$set_clause = implode(", ", $fields);
			$q = "UPDATE `gs_user_cmd_schedule` SET $set_clause WHERE `cmd_id` = '$cmd_id'";
			mysqli_query($ms, $q);
		}
	}

	echo 'OK';
	die;
}


if (@$_POST['cmd'] == 'delete_cmd_schedule') {
	$cmd_id = $_POST["cmd_id"];

	$q = "DELETE FROM `gs_user_cmd_schedule` WHERE `cmd_id`='" . $cmd_id . "' AND `user_id`='" . $user_id . "'";
	$r = mysqli_query($ms, $q);

	echo 'OK';
	die;
}

if (@$_POST['cmd'] == 'delete_selected_cmd_schedules') {
	$items = $_POST["items"];

	for ($i = 0; $i < count($items); ++$i) {
		$item = $items[$i];

		$q = "DELETE FROM `gs_user_cmd_schedule` WHERE `cmd_id`='" . $item . "' AND `user_id`='" . $user_id . "'";
		$r = mysqli_query($ms, $q);
	}

	echo 'OK';
	die;
}

if (@$_GET['cmd'] == 'load_cmd_template_list') {
	$page = $_GET['page']; // get the requested page
	$limit = $_GET['rows']; // get how many rows we want to have into the grid
	$sidx = $_GET['sidx']; // get index row - i.e. user click to sort
	$sord = $_GET['sord']; // get the direction

	if (!$sidx) $sidx = 1;

	// get records number
	$q = "SELECT * FROM `gs_user_cmd` WHERE `user_id`='" . $user_id . "' or `user_id` = '1' group by cmd"; // Rivera
	$r = mysqli_query($ms, $q);
	$count = mysqli_num_rows($r);

	if ($count > 0) {
		$total_pages = ceil($count / $limit);
	} else {
		$total_pages = 1;
	}

	if ($page > $total_pages) $page = $total_pages;
	$start = $limit * $page - $limit; // do not put $limit*($page - 1)

	$q = "SELECT * FROM `gs_user_cmd` WHERE `user_id`='" . $user_id . "' or `user_id` = '1' group by cmd ORDER BY $sidx $sord LIMIT $start, $limit";
	$r = mysqli_query($ms, $q);

	$response = new stdClass();
	$response->page = $page;
	$response->total = $total_pages;
	$response->records = $count;

	if ($r) {
		$i = 0;
		while ($row = mysqli_fetch_array($r)) {
			if ($_SESSION["cpanel_privileges"] == 'super_admin') {
				$cmd_id = $row['cmd_id'];
				$name = $row['name'];
				$protocol = $row['protocol'];
				$gateway = strtoupper($row['gateway']);
				$type = strtoupper($row['type']);
				$cmd = $row['cmd'];
				// set modify buttons
				$modify = '<a href="#" onclick="cmdTemplateProperties(\'' . $cmd_id . '\');" title="' . $la['EDIT'] . '"><img src="theme/images/edit.svg" />';
				$modify .= '<a href="#" onclick="cmdTemplateDelete(\'' . $cmd_id . '\');" title="' . $la['DELETE'] . '"><img src="theme/images/remove3.svg" /></a>';
				// set row
				$response->rows[$i]['id'] = $cmd_id;
				$response->rows[$i]['cell'] = array($name, $protocol, $gateway, $type, $cmd, $modify);
				$i++;
			} else {
				$cmd_id = "";
				$name = "";
				$protocol = "";
				$gateway = "";
				$type = "";
				$cmd = "";
			}
		}
	}

	header('Content-type: application/json');
	echo json_encode($response);
	die;
}

if (@$_POST['cmd'] == 'load_cmd_template_data') {

	$result = array();
	if ($_SESSION["cpanel_privileges"] != 'admin' && $_SESSION["cpanel_privileges"] != 'super_admin') {
		$q = "SELECT * FROM `gs_user_cmd` WHERE `user_id` = '1' ORDER BY `cmd_id` ASC";
		$r = mysqli_query($ms, $q);


		while ($row = mysqli_fetch_array($r)) {
			$cmd_id = $row['cmd_id'];
			$result[$cmd_id] = array(
				'name' => $row['name'],
				'protocol' => $row['protocol'],
				'gateway' => $row['gateway'],
				'type' => $row['type'],
				'user' => $row['user_id'],
				'cmd' => $row['cmd']
			);
		}
	} else {
		$q = "SELECT * FROM `gs_user_cmd` WHERE `user_id` = '1' OR `user_id` = '400' OR `user_id` = '500' OR `user_id` = '300' ORDER BY `cmd_id` ASC";
		$r = mysqli_query($ms, $q);

		while ($row = mysqli_fetch_array($r)) {
			$cmd_id = $row['cmd_id'];
			if ($row['user_id'] == '400' || $row['user_id'] == '500' || $row['user_id'] == '300') {
				$name = '**' . strtoupper($row['name']) . '**';
			} else {
				$name = $row['name'];
			}
			$result[$cmd_id] = array(
				'name' => $name,
				'protocol' => $row['protocol'],
				'gateway' => $row['gateway'],
				'type' => $row['type'],
				'user' => $row['user_id'],
				'cmd' => $row['cmd']
			);
		}
	}

	echo json_encode($result);
	die;
}

if (@$_POST['cmd'] == 'save_cmd_template') {
	$cmd_id = $_POST["cmd_id"];
	$name = $_POST["name"];
	$protocol = $_POST["protocol"];
	$gateway = $_POST["gateway"];
	$type = $_POST["type"];
	$cmd_ = $_POST["cmd_"];

	if ($cmd_id == 'false') {
		$q = "INSERT INTO `gs_user_cmd`(`user_id`,
							`name`,
							`protocol`,
							`gateway`,
							`type`,
							`cmd`)
							VALUES
							('" . $user_id . "',
							'" . $name . "',
							'" . $protocol . "',
							'" . $gateway . "',
							'" . $type . "',
							'" . $cmd_ . "')";
	} else {
		$q = "UPDATE `gs_user_cmd` SET 	`name`='" . $name . "',
							`protocol`='" . $protocol . "',
							`gateway`='" . $gateway . "',
							`type`='" . $type . "',
							`cmd`='" . $cmd_ . "'
							WHERE `cmd_id`='" . $cmd_id . "'";
	}

	$r = mysqli_query($ms, $q);

	echo 'OK';
	die;
}

if (@$_POST['cmd'] == 'delete_cmd_template') {
	$cmd_id = $_POST["cmd_id"];

	$q = "DELETE FROM `gs_user_cmd` WHERE `cmd_id`='" . $cmd_id . "' AND `user_id`='" . $user_id . "'";
	$r = mysqli_query($ms, $q);

	echo 'OK';
	die;
}

if (@$_POST['cmd'] == 'delete_selected_cmd_templates') {
	$items = $_POST["items"];

	for ($i = 0; $i < count($items); ++$i) {
		$item = $items[$i];

		$q = "DELETE FROM `gs_user_cmd` WHERE `cmd_id`='" . $item . "' AND `user_id`='" . $user_id . "'";
		$r = mysqli_query($ms, $q);
	}

	echo 'OK';
	die;
} //cuarta parte ok!
