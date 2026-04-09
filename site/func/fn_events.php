<?
session_start();
include('../init.php');
include('fn_common.php');
include('../server/s_init.php');
checkUserSession();

loadLanguage($_SESSION["language"], $_SESSION["units"]);

// check privileges
if ($_SESSION["privileges"] == 'subuser') {
	$user_id = $_SESSION["manager_id"];
} else {
	$user_id = $_SESSION["user_id"];
}

if (@$_POST['cmd'] == 'load_last_event') {
	$last_id = $_POST['last_id'];

	$result = array();

	// check privileges		
	if ($_SESSION["privileges"] == 'subuser' && !in_array($_SESSION["manager_id"], [159])) {
		$q = "SELECT guled.* FROM gs_user_last_events_data guled
			INNER JOIN gs_user_events gue ON gue.user_id = guled.user_id
			WHERE gue.user_id = '" . $user_id . "'
			AND gue.name = guled.event_desc
			AND FIND_IN_SET(" . $_SESSION["user_id"] . ",sub_accounts)
			AND guled.imei IN (" . $_SESSION["privileges_imei"] . ")";
	} else {
		$q = "SELECT * FROM `gs_user_last_events_data` guled WHERE `user_id`='" . $user_id . "'";
	}

	if ($last_id == -1) {
		$q .= " ORDER BY guled.event_id DESC LIMIT 1";
	} else {
		$q .= " AND guled.`event_id`>'" . $last_id . "' ORDER BY guled.event_id ASC";
	}

	$r = mysqli_query($ms, $q);

	if ($r) {
		while ($row = mysqli_fetch_array($r)) {
			if ($row['name'] == "") {
				$row['name'] = getObjectName($row['imei']);
			}

			$row['dt_server'] = convUserTimezone($row['dt_server']);
			$row['dt_tracker'] = convUserTimezone($row['dt_tracker']);

			$result[] = $row;
		}
	}

	header('Content-type: application/json');
	echo json_encode($result);
	die;
}

if (@$_POST['cmd'] == 'delete_all_events') {
	$q = "DELETE FROM `gs_user_last_events_data` WHERE `user_id`='" . $user_id . "'";
	$r = mysqli_query($ms, $q);

	$q = "DELETE FROM `gs_user_events_data` WHERE `user_id`='" . $user_id . "'";
	$r = mysqli_query($ms, $q);

	echo 'OK';
	die;
}

if (@$_POST['cmd'] == 'load_event_data') {
	$result = array();

	$event_id = $_POST['event_id'];

	// check privileges		
	if ($_SESSION["privileges"] == 'subuser') {
		$q = "SELECT * FROM `gs_user_last_events_data`
			WHERE `user_id`='" . $user_id . "' AND `event_id`='" . $event_id . "' AND `imei` IN (" . $_SESSION["privileges_imei"] . ") LIMIT 1";
	} else {
		$q = "SELECT * FROM `gs_user_last_events_data`
			WHERE `user_id`='" . $user_id . "' AND `event_id`='" . $event_id . "' LIMIT 1";
	}

	$r = mysqli_query($ms, $q);
	$row = mysqli_fetch_array($r);

	if ($row) {
		if ($row['name'] == "") {
			$row['name'] = getObjectName($row['imei']);
		}

		$row['speed'] = convSpeedUnits($row['speed'], 'km', $_SESSION["unit_distance"]);
		$row['altitude'] = convAltitudeUnits($row['altitude'], 'km', $_SESSION["unit_distance"]);

		$params = json_decode($row['params'], true);

		$result = array(
			'name' => $row['name'],
			'imei' => $row['imei'],
			'event_desc' => $row['event_desc'],
			'dt_server' => convUserTimezone($row['dt_server']),
			'dt_tracker' => convUserTimezone($row['dt_tracker']),
			'lat' => $row['lat'],
			'lng' => $row['lng'],
			'altitude' => $row['altitude'],
			'angle' => $row['angle'],
			'speed' => $row['speed'],
			'params' => $params
		);
	}

	header('Content-type: application/json');
	echo json_encode($result);
	die;
}

if (@$_GET['cmd'] == 'load_event_list') {
	$page = $_GET['page']; // get the requested page
	$limit = $_GET['rows']; // get how many rows we want to have into the grid
	$sidx = $_GET['sidx']; // get index row - i.e. user click to sort
	$sord = $_GET['sord']; // get the direction
	$search = caseToUpper(@$_GET['s']); // get search

	if (!$sidx) $sidx = 1;

	// check privileges
	if ($_SESSION["privileges"] == 'subuser') {
		$user_id = $_SESSION["manager_id"];
		$q = "SELECT * FROM `gs_user_events` WHERE `user_id`='" . $user_id . "' ORDER BY `name` ASC";
		$r = mysqli_query($ms, $q);
		$events_ids = array(); // contiene los eventos asignados a la subcuenta
		while ($r && $row = mysqli_fetch_assoc($r)) {
			$ids = explode(',', $row['sub_accounts']);
			if (in_array($_SESSION["user_id"], $ids)) {
				array_push($events_ids, $row['event_id']);
			}
		}

		$q = "SELECT * FROM `gs_user_events` WHERE `user_id`='" . $user_id . "' AND event_id IN (" . implode(',', $events_ids) . ") ORDER BY `name` ASC";
		$r = mysqli_query($ms, $q);
		$imeis_autorizados_subcuenta = array(); // contiene los imeis asigandos a la subcuenta y ademas tiene permisos para el evento
		while ($r && $row = mysqli_fetch_assoc($r)) {

			$privileges_imei = array_map(function ($imei) {
				return trim($imei, '"');
			}, explode(',', $_SESSION["privileges_imei"]));

			$imeis = explode(',', $row['imei']);
			foreach ($imeis as $imei) {
				$privileges_imei   = array_map('trim',  $privileges_imei);
				if (in_array($imei,  $privileges_imei)) {
					array_push($imeis_autorizados_subcuenta, $imei);
				}
			}
		}
		$imeisAutorizadosSubcuenta = array_filter($imeis_autorizados_subcuenta, function ($item) {
			if (!preg_match('/(\d+)_(\d+)/', $item, $result)) {
				return $item;
			}
		});
		$q = "SELECT guled.* FROM `gs_user_last_events_data` guled
			INNER JOIN gs_user_events gue ON gue.user_id = guled.user_id			
			WHERE guled.`user_id`='" . $user_id . "' 
			AND gue.name = guled.event_desc
			AND FIND_IN_SET(" . $_SESSION["user_id"] . ", sub_accounts)
			AND guled.`imei` IN (" . implode(',', $imeisAutorizadosSubcuenta) . ")";
	} else {
		$q = "SELECT * FROM `gs_user_last_events_data` WHERE `user_id`='" . $user_id . "'";
	}

	if ($search != '') {
		$q .= "AND (UPPER(`event_desc`) LIKE '%$search%' OR UPPER(`name`) LIKE '%$search%')";
	}

	$r = mysqli_query($ms, $q);
	$count = 0;
	if ($r) {
		$count = mysqli_num_rows($r);
	}

	if ($count > 0) {
		$total_pages = ceil($count / $limit);
	} else {
		$total_pages = 1;
	}

	if ($page > $total_pages) $page = $total_pages;
	$start = $limit * $page - $limit; // do not put $limit*($page - 1)

	$q .= " ORDER BY $sidx $sord LIMIT $start, $limit";
	$r = mysqli_query($ms, $q);

	$response = new stdClass();
	$response->page = $page;
	$response->total = $total_pages;
	$response->records = $count;

	if ($r) {
		$i = 0;
		while ($row = mysqli_fetch_array($r)) {
			if (checkObjectActive($row['imei']) == true) {
				$dt_tracker = convUserTimezone($row['dt_tracker']);

				if ($row['name'] == "") {
					$row['name'] = getObjectName($row['imei']);
				}

				$response->rows[$i]['id'] = $row['event_id'];
				$response->rows[$i]['cell'] = array($dt_tracker, $row['name'], $row['event_desc']);
				$i++;
			}
		}
	}

	header('Content-type: application/json');
	echo json_encode($response);
	die;
}

if (@$_POST['cmd'] == 'load_event_data_managment') {
	$result = array();

	$event_id = $_POST['event_id'];

	// check privileges		
	if ($_SESSION["privileges"] == 'subuser') {
		$q = "SELECT * FROM `gs_user_last_events_data`
			WHERE `user_id`='" . $user_id . "' AND `event_id`='" . $event_id . "' AND `imei` IN (" . $_SESSION["privileges_imei"] . ") LIMIT 1";
	} else {
		$q = "SELECT * FROM `gs_user_last_events_data`
			WHERE `user_id`='" . $user_id . "' AND `event_id`='" . $event_id . "' LIMIT 1";
	}

	$r = mysqli_query($ms, $q);
	$row = mysqli_fetch_array($r);

	if ($row) {
		if ($row['name'] == "") {
			$row['name'] = getObjectName($row['imei']);
		}

		$q_ = "SELECT details, fecha, gs_users.username as username  FROM gs_user_last_events_data_details
			   INNER JOIN gs_users  on gs_users.id = gs_user_last_events_data_details.user_id
			   WHERE event_id='" . $row['event_id'] . "' ORDER BY fecha desc";
		$r_ = mysqli_query($ms, $q_);

		$details_list = '<br/>';
		while ($row_detail = mysqli_fetch_assoc($r_)) {
			$details_list .= "<strong>" . $row_detail['username'] . "</strong>";
			$details_list .=  " - ";
			$details_list .= "<strong>" . $row_detail['fecha'] . "</strong><br/>";
			$details_list .=  $row_detail['details'] . "<br/>";
			$details_list .= "<br/><br/>";
		}

		$result = array(
			'type' => $row['type'],
			'name' => $row['name'],
			'event_desc' => $row['event_desc'],
			'attended_status' => $row['attended_status'],
			'details' => $details_list,
		);
	}

	header('Content-type: application/json');
	echo json_encode($result);
	die;
}

if (@$_POST['cmd'] == 'save_event_data_managment') {

	global $ms, $gsValues;

	$user_id = $_SESSION["user_id"];
	$attended_status = $_POST["attended_status"];
	$id_event = $_POST["event_id"];
	$detail = $_POST["detail"];

	$email = $gsValues['DB_BACKUP_EMAIL'];
	$emailsArray = explode(',', $email);
	$emailsArray = array_map('trim', $emailsArray);

	if ($emailsArray == '') {
		die;
	}

	$q = "SELECT * FROM `gs_user_last_events_data` WHERE  event_id='" . $id_event . "'";
	$r = mysqli_query($ms, $q);
	$row = mysqli_fetch_assoc($r);
	$previous_status = $row['attended_status'];
	$name = $row['name'];
	$event = $row['event_desc'];
	$type = $row['type'];
	$imei = $row['imei'];
	$atendido = 'false';
	$date = $row['dt_tracker'];

	if ($previous_status != $attended_status) {
		$q = "UPDATE `gs_user_last_events_data` SET `attended_status`='" . $attended_status . "' WHERE `event_id`='" . $id_event . "'";
		$r = mysqli_query($ms, $q);
	}

	if ($previous_status == $attended_status) {
		$detail = '<i>[ ' . $attended_status . ']</i><br/>' . $detail;
	} else {
		$detail = '<i>[ ' . $previous_status . ' -> ' . $attended_status . ']</i><br/>' . $detail;
	}
	if ($previous_status == 'Agendar' && $attended_status == 'Atendido' && $type == 'service') {
		$atendido = 'true';
	}

	if (isset($_POST["detail"]) && strlen($_POST["detail"]) > 0) {
		$q = "INSERT INTO gs_user_last_events_data_details (event_id, details, fecha, user_id) values (" . $id_event . ",'" . $detail . "', now(), " . $user_id . " )";
		$r = mysqli_query($ms, $q);
	}



	if ($attended_status == 'Agendar' && $type == 'service') {

		$q = "SELECT * FROM `gs_object_services` WHERE  imei='" . $imei . "'";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_assoc($r);

		$servicio = $row['name'];
		$plan = $row['plan'];
		$dia_limite = $row['days_left_num'];

		$days_diff = strtotime(gmdate("Y-m-d")) - (strtotime($row['days_last']));
		$days_diff = floor($days_diff / 3600 / 24);
		$days_diff = $row['days_interval'] - $days_diff;
		$dias_rest = $dia_limite - $days_diff;

		if ($days_diff <= $dia_limite) {

			$q1 = "UPDATE gs_object_services SET `count` = '5', `notify_service_expire` = 'true' WHERE `imei`='" . $imei . "'";
			$r1 = mysqli_query($ms, $q1);

			$cliente = $_SESSION['username'];

			$detail = preg_replace("/<i>\[([^\]]*)\]<\/i>(.*)/u", "$1, Mensaje: $2", $detail);
			$detail = preg_replace("/<br\/?>/", "", $detail);

			$infoArray[] = [
				'unidad' => $name,
				'imei' => $imei,
				'plan' => $plan,
				'dias_vencidos' => $dias_rest,
				'dias_restantes' => $days_diff,
				'cliente' => $cliente,
				'detalles' => $detail
			];

			usort($infoArray, function ($a, $b) {
				return $a['imei'] <=> $b['imei'];
			});

			$sortedMessagesArray = ['Nueva Cita Por Agendar:'];
			foreach ($infoArray as $info) {
				$sortedMessagesArray[] = "Unidad: {$info['unidad']}, Imei: {$info['imei']}, Plan: {$info['plan']}, Días Vencidos: {$info['dias_vencidos']}, Días Restantes: {$info['dias_restantes']}, Cliente: {$info['cliente']}, Detalles: {$info['detalles']}.";
			}
			$sortedMessage = implode("\n\n", $sortedMessagesArray);

			$subject = 'Unidades con Mtto/Garantias Vencidas';
			$message = $sortedMessage;
			$count = 0;
			$totalEmails = count($emailsArray);

			foreach ($emailsArray as $email) {
				sendEmail($email, $subject, $message);

				$count++;
				if ($count == $totalEmails) {
					sleep(1);
					$count = 0;
				}
			}
		}
		
		header('Content-type: application/json');
		echo json_encode(array('result' => 'OK'));
		die;
	}

	if ($previous_status == 'Agendar' && $atendido == 'true') {

		$q = "SELECT * FROM `gs_object_services` WHERE  imei='" . $imei . "'";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_assoc($r);

		$servicio = $row['name'];
		$plan = $row['plan'];
		$dia_limite = $row['days_left_num'];

		$days_diff = strtotime(gmdate("Y-m-d")) - (strtotime($row['days_last']));
		$days_diff = floor($days_diff / 3600 / 24);
		$days_diff = $row['days_interval'] - $days_diff;
		$dias_rest = $dia_limite - $days_diff;

		$cliente = $_SESSION['username'];

		$detail = preg_replace("/<i>\[([^\]]*)\]<\/i>(.*)/u", "$1, Mensaje: $2", $detail);
		$detail = preg_replace("/<br\/?>/", "", $detail);

		$infoArray[] = [
			'unidad' => $name,
			'imei' => $imei,
			'plan' => $plan,
			'dias_vencidos' => $dias_rest,
			'dias_restantes' => $days_diff,
			'cliente' => $cliente,
			'detalles' => $detail
		];

		usort($infoArray, function ($a, $b) {
			return $a['imei'] <=> $b['imei'];
		});

		$sortedMessagesArray = ['Servicio Atendido:'];
		foreach ($infoArray as $info) {
			$sortedMessagesArray[] = "Unidad: {$info['unidad']}, Imei: {$info['imei']}, Plan: {$info['plan']}, Días Vencidos: {$info['dias_vencidos']}, Días Restantes: {$info['dias_restantes']}, Cliente: {$info['cliente']}, Detalles: {$info['detalles']}.";
		}
		$sortedMessage = implode("\n\n", $sortedMessagesArray);

		$subject = 'Mantenimiento de Unidades Atendidas';
		$message = $sortedMessage;
		$count = 0;
		$totalEmails = count($emailsArray);

		foreach ($emailsArray as $email) {
			sendEmail($email, $subject, $message);

			$count++;
			if ($count == $totalEmails) {
				sleep(1);
				$count = 0;
			}
		}
		
		header('Content-type: application/json');
		echo json_encode(array('result' => 'OK'));
		die;
	}

	if ($attended_status == 'Agendar' && $type != 'service') {

		$q = "SELECT * FROM `gs_user_last_events_data` WHERE  imei='" . $imei . "'";
		$r = mysqli_query($ms, $q);


		if ($row = mysqli_fetch_assoc($r)) {

			$cliente = $_SESSION['username'];
			$servicio = $row['name'];
			$plan = $row['plan'];
			if ($plan == "") {
				$plan = $event;
			}

			$detail = preg_replace("/<i>\[([^\]]*)\]<\/i>(.*)/u", "$1, Mensaje: $2", $detail);
			$detail = preg_replace("/<br\/?>/", "", $detail);

			$infoArray[] = [
				'unidad' => $name,
				'imei' => $imei,
				'plan' => $plan,
				'fecha' => $date,
				'cliente' => $cliente,
				'detalles' => $detail
			];

			usort($infoArray, function ($a, $b) {
				return $a['imei'] <=> $b['imei'];
			});

			$sortedMessagesArray = ['Detalles del Evento:'];
			foreach ($infoArray as $info) {
				$sortedMessagesArray[] = "Unidad: {$info['unidad']}, Imei: {$info['imei']}, Evento: {$info['plan']}, Fecha de Evento: {$info['fecha']}, Cliente: {$info['cliente']}, Detalles: {$info['detalles']}.";
			}
			$sortedMessage = implode("\n\n", $sortedMessagesArray);

			$subject = 'Solicitud de Servicio';
			$message = $sortedMessage;
			$count = 0;
			$totalEmails = count($emailsArray);

			foreach ($emailsArray as $email) {
				sendEmail($email, $subject, $message);

				$count++;
				if ($count == $totalEmails) {
					sleep(1);
					$count = 0;
				}
			}
		}

		header('Content-type: application/json');
		echo json_encode(array('result' => 'OK'));
		die;
	}

}

if (@$_GET['cmd'] == 'load_event_list_managment') {
	$page = $_GET['page']; // get the requested page
	$limit = $_GET['rows']; // get how many rows we want to have into the grid
	$sidx = $_GET['sidx']; // get index row - i.e. user click to sort
	$sord = $_GET['sord']; // get the direction
	$search = caseToUpper(@$_GET['s']); // get search

	if (!$sidx) $sidx = 1;

	// check privileges
	if ($_SESSION["privileges"] == 'subuser') {

		$user_id = $_SESSION["manager_id"];
		$q = "SELECT * FROM `gs_user_events` WHERE `user_id`='" . $user_id . "' ORDER BY `name` ASC";
		$r = mysqli_query($ms, $q);
		$events_ids = array(); // contiene los eventos asignados a la subcuenta
		while ($r && $row = mysqli_fetch_assoc($r)) {
			$ids = explode(',', $row['sub_accounts']);
			if (in_array($_SESSION["user_id"], $ids)) {
				array_push($events_ids, $row['event_id']);
			}
		}

		$q = "SELECT * FROM `gs_user_events` WHERE `user_id`='" . $user_id . "' AND event_id IN (" . implode(',', $events_ids) . ") ORDER BY `name` ASC";
		$r = mysqli_query($ms, $q);
		$imeis_autorizados_subcuenta = array(); // contiene los imeis asigandos a la subcuenta y ademas tiene permisos para el evento
		while ($r && $row = mysqli_fetch_assoc($r)) {

			$privileges_imei = array_map(function ($imei) {
				return trim($imei, '"');
			}, explode(',', $_SESSION["privileges_imei"]));

			$imeis = explode(',', $row['imei']);
			foreach ($imeis as $imei) {
				$privileges_imei   = array_map('trim',  $privileges_imei);
				if (in_array($imei,  $privileges_imei)) {
					array_push($imeis_autorizados_subcuenta, $imei);
				}
			}
		}

		$imeisAutorizadosSubcuenta = array_filter($imeis_autorizados_subcuenta, function ($item) {
			if (!preg_match('/(\d+)_(\d+)/', $item, $result)) {
				return $item;
			}
		});
		$q = "SELECT guled.* FROM `gs_user_last_events_data` guled 
		INNER JOIN gs_user_events gue ON gue.user_id = guled.user_id
		WHERE guled.`user_id`='" . $user_id . "'
		AND gue.name = guled.event_desc
		AND FIND_IN_SET(" . $_SESSION["user_id"] . ", sub_accounts) 
		AND guled.`imei` IN (" . implode(',', $imeisAutorizadosSubcuenta) . ") ";
	} else {
		$q = "SELECT * FROM `gs_user_last_events_data` WHERE `user_id`='" . $user_id . "'";
	}

	if ($search != '') {
		$q .= "AND (UPPER(`event_desc`) LIKE '%$search%' OR UPPER(`name`) LIKE '%$search%')";
	}

	$r = mysqli_query($ms, $q);
	$count = 0;
	if ($r) {
		$count = mysqli_num_rows($r);
	}
	if ($count > 0) {
		$total_pages = ceil($count / $limit);
	} else {
		$total_pages = 1;
	}

	if ($page > $total_pages) $page = $total_pages;
	$start = $limit * $page - $limit;
	$q .= " ORDER BY $sidx $sord LIMIT $start, $limit";
	$r = mysqli_query($ms, $q);

	$response = new stdClass();
	$response->page = $page;
	$response->total = $total_pages;
	$response->records = $count;
	

	if ($r) {
		$i = 0;
		while ($row = mysqli_fetch_array($r)) {
			if (checkObjectActive($row['imei']) == true) {
				$imei =$row['imei'];
			
				$q4 = "SELECT * FROM `gs_object_services` WHERE `imei`='$imei'";
				$r4 = mysqli_query($ms, $q4);
				
				$tipo_servicio = 'ninguno';
				
				while ($row4 = mysqli_fetch_array($r4)) {
					if ($row4['odo'] === 'true') {
						$tipo_servicio = 'odometer';
						break;
					} elseif ($row4['engh'] === 'true') {
						$tipo_servicio = 'hours';
						break;
					} elseif ($row4['days'] === 'true') {
						$tipo_servicio = 'days';
						break;
					}
				}
				$dt_tracker = convUserTimezone($row['dt_tracker']);
	
				if ($row['name'] == "") {
					$row['name'] = getObjectName($row['imei']);
				}
				if ($row['type'] == 'service' && $row['attended_status'] == 'Sin atender'){
					$row['attended_status'] = 'Service';
				}
	
				$response->rows[$i]['id'] = $row['event_id'];
				$response->rows[$i]['attended_status'] = $row['attended_status'];
				$response->rows[$i]['attended_description'] = 'description';
				$response->rows[$i]['tipo_servicio'] = $tipo_servicio;
	
				$modify = '<a href="#" onclick="eventsAttendedProperties(\'' . $row['event_id'] . '\');" title="' . $la['EDIT'] . '"><img src="theme/images/edit.svg" />';
				$response->rows[$i]['cell'] = array($dt_tracker, $row['name'], $row['event_desc'], $modify);
				$i++;
			}
		}
	}	

	header('Content-type: application/json');
	echo json_encode($response);
	die;
}


// #################################################
// TELEGRAM FUNCTIONS
// #################################################

// Devuelve los eventos del usuario que NO tienen telegram_chat_id vinculado
if (@$_POST['cmd'] == 'load_telegram_events')
{
    if (!isset($_SESSION["user_id"])) {
        echo json_encode(['success' => false, 'error' => 'Sesión no válida']);
        die;
    }

    $user_id = ($_SESSION["manager_id"] > 0) ? (int)$_SESSION["manager_id"] : (int)$_SESSION["user_id"];

    $q = "SELECT `event_id`, `name` FROM `gs_user_events`
          WHERE `user_id`='" . $user_id . "'
          AND (`telegram_chat_id` IS NULL OR `telegram_chat_id` = '')
          ORDER BY `name` ASC";
    $r = mysqli_query($ms, $q);

    $events = [];
    while ($r && $row = mysqli_fetch_assoc($r)) {
        $events[] = ['event_id' => $row['event_id'], 'name' => $row['name']];
    }

    echo json_encode(['success' => true, 'events' => $events]);
    die;
}

// Genera un token ÚNICO por cada evento seleccionado y devuelve el mapa event_id => token
if (@$_POST['cmd'] == 'generate_telegram_token')
{
    if (!isset($_SESSION["user_id"])) {
        echo json_encode(['success' => false, 'error' => 'Sesión no válida']);
        die;
    }

    if (!$ms) {
        echo json_encode(['success' => false, 'error' => 'Sin conexión a la base de datos']);
        die;
    }

    $user_id = ($_SESSION["manager_id"] > 0) ? (int)$_SESSION["manager_id"] : (int)$_SESSION["user_id"];

    // event_ids es un array de IDs enviados desde el frontend
    $event_ids_raw = isset($_POST['event_ids']) ? $_POST['event_ids'] : [];

    if (empty($event_ids_raw)) {
        echo json_encode(['success' => false, 'error' => 'No se seleccionaron eventos']);
        die;
    }

    // Sanitizar IDs
    $event_ids_safe = array_filter(array_map('intval', (array)$event_ids_raw));

    // Generar o mantener tokens, desactivar SMS y activar Telegram
    $tokens = [];
    foreach ($event_ids_safe as $eid) {
        // Primero verificamos si ya tiene un token
        $q_check = "SELECT `telegram_link_token` FROM `gs_user_events` WHERE `event_id`='" . $eid . "' LIMIT 1";
        $r_check = mysqli_query($ms, $q_check);
        $row_check = mysqli_fetch_assoc($r_check);
        $token = ($row_check && !empty($row_check['telegram_link_token'])) ? $row_check['telegram_link_token'] : bin2hex(random_bytes(16));

        // Actualizar el token (nuevo o viejo), desactivar SMS y activar Telegram
        $q = "UPDATE `gs_user_events`
              SET `telegram_link_token`='" . mysqli_real_escape_string($ms, $token) . "',
                  `notify_sms`='false',
                  `notify_telegram`='true'
              WHERE `user_id`='" . $user_id . "'
              AND `event_id`='" . $eid . "'";
        mysqli_query($ms, $q);
        $tokens[$eid] = $token;
    }

    echo json_encode(['success' => true, 'tokens' => $tokens]);
    die;
}
// Estado de Telegram del usuario (para el panel de la barra superior)
// Devuelve TODOS los eventos con su estado de vinculación (filtrado por chat_id)
if (@$_POST['cmd'] == 'load_telegram_status')
{
    if (!isset($_SESSION["user_id"])) {
        echo json_encode(['success' => false, 'error' => 'Sesión no válida']);
        die;
    }

    $user_id = ($_SESSION["manager_id"] > 0) ? (int)$_SESSION["manager_id"] : (int)$_SESSION["user_id"];

    // Verificar si hay al menos un evento con chat_id (= hay alguno vinculado)
    $q_conn = "SELECT COUNT(*) as cnt FROM `gs_user_events`
               WHERE `user_id`='" . $user_id . "'
               AND `telegram_chat_id` IS NOT NULL AND `telegram_chat_id` != ''";
    $r_conn = mysqli_query($ms, $q_conn);
    $row_conn = $r_conn ? mysqli_fetch_assoc($r_conn) : null;
    $connected = ($row_conn && $row_conn['cnt'] > 0);

    // Todos los eventos del usuario con su token y chat_id
    $q_events = "SELECT `event_id`, `name`, `telegram_link_token`, `telegram_chat_id`
                 FROM `gs_user_events`
                 WHERE `user_id`='" . $user_id . "'
                 AND `maintenance_id` IS NULL
                 ORDER BY `name` ASC";
    $r_events = mysqli_query($ms, $q_events);
    $events = [];
    while ($r_events && $ev = mysqli_fetch_assoc($r_events)) {
        // Contar cuántos chat_ids tiene este evento (separados por coma)
        $chat_ids = array_filter(array_map('trim', explode(',', $ev['telegram_chat_id'] ?? '')));
        $events[] = [
            'event_id'  => $ev['event_id'],
            'name'      => $ev['name'],
            'token'     => $ev['telegram_link_token'] ?? '',
            'linked'    => count($chat_ids) > 0,
            'id_count'  => count($chat_ids)
        ];
    }

    echo json_encode([
        'success'   => true,
        'connected' => $connected,
        'events'    => $events
    ]);
    die;
}

// Desvincular Telegram: limpiar chat_id y token de todos los eventos del usuario
if (@$_POST['cmd'] == 'disconnect_telegram')
{
    if (!isset($_SESSION["user_id"])) {
        echo json_encode(['success' => false, 'error' => 'Sesión no válida']);
        die;
    }

    $user_id = ($_SESSION["manager_id"] > 0) ? (int)$_SESSION["manager_id"] : (int)$_SESSION["user_id"];

    $q = "UPDATE `gs_user_events`
          SET `telegram_chat_id`='', `telegram_link_token`='', `telegram_connected_at`=NULL
          WHERE `user_id`='" . $user_id . "'";
    $r = mysqli_query($ms, $q);

    echo json_encode(['success' => ($r !== false)]);
    die;
}
