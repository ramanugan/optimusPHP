<? 
	session_start();
	include ('../init.php');
	include ('fn_common.php');
	checkUserSession();
	
	loadLanguage($_SESSION["language"], $_SESSION["units"]);
	
	if(@$_POST['cmd'] == 'load_event_data')
	{
		if ($_SESSION["manager_id"] > 0) {
			$user_id = $_SESSION["manager_id"];
			$q = "SELECT * FROM `gs_user_events` WHERE `user_id`='" . $user_id . "' ORDER BY `name` ASC";
			$r = mysqli_query($ms, $q);
			$events_ids= array();
			while($row = mysqli_fetch_assoc($r)) {
				$ids = explode(',',$row['sub_accounts']);
				if (in_array($_SESSION["user_id"], $ids)) {
				array_push($events_ids, $row['event_id']);
				}
			}
			$q = "SELECT * FROM `gs_user_events` WHERE `user_id`='" . $user_id . "' AND event_id IN (" . implode(',', $events_ids) . ") ORDER BY `name` ASC";
		} else {
			$user_id = $_SESSION["user_id"];
			$q = "SELECT * FROM `gs_user_events` WHERE `user_id`='" . $user_id . "' ORDER BY `name` ASC";
		}
		
		$r = mysqli_query($ms, $q);
		$result = array();
		
		while( $r && $row = mysqli_fetch_array($r))
		{
			$event_id = $row['event_id'];
			
			$day_time = json_decode($row['day_time'], true);
			
			if (($row['type'] == 'param') || ($row['type'] == 'sensor') || ($row['type'] == 'zone_in_stop_engine'))
			{
				$row['checked_value'] = json_decode($row['checked_value'], true);
				
				if ($row['checked_value'] == null)
				{
					$row['checked_value'] = array();	
				}
			}
			if (isset($_POST['id']) && $_POST['id'] !== '0') {
				$service_id = $_POST['id'];
			
				$q1 = "SELECT * FROM `gs_object_services` WHERE `service_id` = '" . mysqli_real_escape_string($ms, $service_id) . "'";
				$r1 = mysqli_query($ms, $q1);
			
				if ($r1 && mysqli_num_rows($r1) > 0) {
					$row1 = mysqli_fetch_array($r1);
					$odo_last = $row1['odo_last'];
			
					$result[$event_id]['odo_last'] = $odo_last;
				}
			}
			
			$result[$event_id] = array_merge(
				$result[$event_id] ?? [],
				array(
					'type' => $row['type'],
					'name' => $row['name'],
					'active' => $row['active'],
					'duration_from_last_event' => $row['duration_from_last_event'],
					'duration_from_last_event_minutes' => $row['duration_from_last_event_minutes'],
					'week_days' => $row['week_days'],
					'day_time' => $day_time,
					'imei' => $row['imei'],
					'sub_accounts' => $row['sub_accounts'],
					'checked_value' => $row['checked_value'],
					'route_trigger' => $row['route_trigger'],
					'zone_trigger' => $row['zone_trigger'],
					'routes' => $row['routes'],
					'zones' => $row['zones'],
					'notify_system' => $row['notify_system'],
					'notify_push' => $row['notify_push'],
					'notify_email' => $row['notify_email'],
					'notify_email_address' => $row['notify_email_address'],
					'notify_sms' => $row['notify_sms'],
					'notify_sms_number' => $row['notify_sms_number'],
					'notify_telegram' => $row['notify_telegram'],
					'telegram_chat_id' => $row['telegram_chat_id'],
					'telegram_link_token' => $row['telegram_link_token'],
					'email_template_id' => $row['email_template_id'],
					'sms_template_id' => $row['sms_template_id'],
					'notify_arrow' => $row['notify_arrow'],
					'notify_arrow_color' => $row['notify_arrow_color'],
					'notify_ohc' => $row['notify_ohc'],
					'notify_ohc_color' => $row['notify_ohc_color'],
					'webhook_send' => $row['webhook_send'],
					'webhook_url' => $row['webhook_url'],
					'cmd_send' => $row['cmd_send'],
					'cmd_gateway' => $row['cmd_gateway'],
					'cmd_type' => $row['cmd_type'],
					'cmd_string' => $row['cmd_string']
				)
			);
		}
		echo json_encode($result);
		die;
	}
	
	if(@$_GET['cmd'] == 'load_event_list')
	{ 
		$page = $_GET['page']; // get the requested page
		$limit = $_GET['rows']; // get how many rows we want to have into the grid
		$sidx = $_GET['sidx']; // get index row - i.e. user click to sort
		$sord = $_GET['sord']; // get the direction
		$search = caseToUpper(@$_GET['s']); // get search
		
		if ($_SESSION["manager_id"] > 0  ) {
			$user_id = $_SESSION["manager_id"];
		$q = "SELECT * FROM `gs_user_events` WHERE `user_id`='" . $user_id . "' ORDER BY `name` ASC";
		$r = mysqli_query($ms, $q);
		$events_ids = array();
		while ($row = mysqli_fetch_assoc($r)) {
			$ids = explode(',', $row['sub_accounts']);
			if (in_array($_SESSION["user_id"], $ids)) {
				array_push($events_ids, $row['event_id']);
			}
		}
		$q = "SELECT * FROM `gs_user_events` WHERE `user_id`='" . $user_id . "' AND event_id IN (" . implode(',', $events_ids) . ") ";
		} else {
			$user_id = $_SESSION["user_id"];
			$q = "SELECT * FROM `gs_user_events` WHERE `user_id`='" . $user_id . "' AND `maintenance_id` IS NULL";
		}
		
		if(!$sidx) $sidx =1;
		
		// get records number
		//$q = "SELECT * FROM `gs_user_events` WHERE `user_id`='".$user_id."'";
		
		if ($search != '')
		{
			$q .= " AND (UPPER(`name`) LIKE '%$search%')";	
		}
		
		$r = mysqli_query($ms, $q);
		$count = 0;
		if ($r) {
			$count = mysqli_num_rows($r);
		}
		
		if ($count > 0)
		{
			$total_pages = ceil($count/$limit);
		}
		else
		{
			$total_pages = 1;
		}
		
		if ($page > $total_pages) $page=$total_pages;
		$start = $limit*$page - $limit; // do not put $limit*($page - 1)
		
		$q .= " ORDER BY $sidx $sord LIMIT $start, $limit";	
		$r = mysqli_query($ms, $q);
		
		$response = new stdClass();
		$response->page = $page;
		$response->total = $total_pages;
		$response->records = $count;
		
		if ($r)
		{
			$i=0;
			while($row = mysqli_fetch_array($r))
			{
				$event_id = $row['event_id'];
				$name = $row['name'];
				
				if ($row['active'] == 'true')
				{
					$active = '<img src="theme/images/tick-green.svg" />';
				}
				else
				{
					$active = '<img src="theme/images/remove-red.svg" style="width:12px;" />';
				}
				
				$notify_system = explode(",", $row['notify_system']);
				
				if (@$notify_system[0] == 'true')
				{
					$notify_system = '<img src="theme/images/tick-green.svg" />';
				}
				else
				{
					$notify_system = '<img src="theme/images/remove-red.svg" style="width:12px;" />';
				}
				
				if ($row['notify_push'] == 'true')
				{
					$notify_push = '<img src="theme/images/tick-green.svg" />';
				}
				else
				{
					$notify_push = '<img src="theme/images/remove-red.svg" style="width:12px;" />';
				}
				
				if ($row['notify_email'] == 'true')
				{
					$notify_email = '<img src="theme/images/tick-green.svg" />';
				}
				else
				{
					$notify_email = '<img src="theme/images/remove-red.svg" style="width:12px;" />';
				}
				
				if ($row['notify_sms'] == 'true')
				{
					$notify_sms = '<img src="theme/images/tick-green.svg" />';
				}
				else
				{
					$notify_sms = '<img src="theme/images/remove-red.svg" style="width:12px;" />';
				}
				
				// set modify buttons
				$modify = '<a href="#" onclick="settingsEventProperties(\''.$event_id.'\');" title="'.$la['EDIT'].'"><img src="theme/images/edit.svg" />';
				$modify .= '</a><a href="#" onclick="settingsEventDelete(\''.$event_id.'\');"  title="'.$la['DELETE'].'"><img src="theme/images/remove3.svg" /></a>';
				// set row
				$response->rows[$i]['id']=$event_id;
				$response->rows[$i]['cell']=array($name,$active,$notify_system,$notify_push,$notify_email,$notify_sms,$modify);
				$i++;
			}
		}
		
		header('Content-type: application/json');
		echo json_encode($response);
		die;
	}
	
	if(@$_POST['cmd'] == 'delete_event')
	{
		if ($_SESSION["manager_id"] > 0) {
			$user_id = $_SESSION["manager_id"];
		} else {
			$user_id = $_SESSION["user_id"];
		}
		$event_id = $_POST["event_id"];

		$q0 = "SELECT event_id, user_id, type,name from  `gs_user_events` WHERE `event_id`='" . $event_id . "' AND `user_id`='" . $user_id . "'";
		$r = mysqli_query($ms, $q0);
		$event_dropped=json_encode( mysqli_fetch_assoc($r) );

		$q = "DELETE FROM `gs_user_events` WHERE `event_id`='".$event_id."' AND `user_id`='".$user_id."'";
		$r = mysqli_query($ms, $q);
		
		$q2 = "DELETE FROM `gs_user_events_status` WHERE `event_id`='".$event_id."'";
		$r = mysqli_query($ms, $q2);

		addRowBinnacle($_SESSION["user_id"], 'Eliminacion de evento', $event_dropped);
		echo 'OK';
		die;
	}
	
	if(@$_POST['cmd'] == 'delete_selected_events')
	{
		$items = $_POST["items"];
		$event_id = $_POST["event_id"];
		if ($_SESSION["manager_id"] > 0) {
			$user_id = $_SESSION["manager_id"];
		} else {
			$user_id = $_SESSION["user_id"];
		}

		$events_dropped = array();
		for ($i = 0; $i < count($items); ++$i)
		{
			$item = $items[$i];

			$q0 = "SELECT event_id, user_id, type,name from  `gs_user_events` WHERE `event_id`='" . $item . "' AND `user_id`='" . $user_id . "'";
			$r = mysqli_query($ms, $q0);
			array_push($events_dropped, mysqli_fetch_assoc($r));

			$q = "DELETE FROM `gs_user_events` WHERE `event_id`='".$item."' AND `user_id`='".$user_id."'";
			$r = mysqli_query($ms, $q);
			
			$q2 = "DELETE FROM `gs_user_events_status` WHERE `event_id`='".$item."'";
			$r = mysqli_query($ms, $q2);
		}
		addRowBinnacle($_SESSION["user_id"], 'Eliminacion de  varios eventos', json_encode( $events_dropped) );
		echo 'OK';
		die;
	}

if (@$_POST['cmd'] == 'save_event') {
	$event_id = $_POST["event_id"];
	if ($_SESSION["manager_id"] > 0) {
		$user_id = $_SESSION["manager_id"];
	} else {
		$user_id = $_SESSION["user_id"];
	}
	$type = $_POST["type"];
	$name = $_POST["name"];
	$active = $_POST["active"];
	$duration_from_last_event = $_POST["duration_from_last_event"];
	$duration_from_last_event_minutes = $_POST["duration_from_last_event_minutes"];
	$week_days = $_POST["week_days"];
	$day_time = $_POST["day_time"];
	$imei = $_POST["imei"];
	$subaccounts = $_POST["subAccounts"];
	$checked_value = $_POST["checked_value"];
	$route_trigger = $_POST["route_trigger"];
	$zone_trigger = $_POST["zone_trigger"];
	$routes = $_POST["routes"];
	$zones = $_POST["zones"];
	$notify_system = $_POST["notify_system"];
	$notify_push = $_POST["notify_push"];
	$notify_email = $_POST["notify_email"];
	$notify_email_address = $_POST["notify_email_address"];
	$notify_sms = $_POST["notify_sms"];
	$notify_sms_number = $_POST["notify_sms_number"];
	$notify_telegram = $_POST["notify_telegram"];
	$telegram_chat_id = mysqli_real_escape_string($ms, $_POST["telegram_chat_id"]);  // Chat IDs de Telegram (separados por comas)
	$email_template_id = $_POST["email_template_id"];
	$sms_template_id = $_POST["sms_template_id"];
	$notify_arrow = $_POST["notify_arrow"];
	$notify_arrow_color = $_POST["notify_arrow_color"];
	$notify_ohc = $_POST["notify_ohc"];
	$notify_ohc_color = $_POST["notify_ohc_color"];
	$webhook_send = $_POST["webhook_send"];
	$webhook_url = $_POST["webhook_url"];
	$cmd_send = $_POST["cmd_send"];
	$cmd_gateway = $_POST["cmd_gateway"];
	$cmd_type = $_POST["cmd_type"];
	$cmd_string = $_POST["cmd_string"];

	if ($event_id == 'false') {
		$q = "INSERT INTO `gs_user_events` (`user_id`,
								`type`,
								`name`,
								`active`,
								`duration_from_last_event`,
								`duration_from_last_event_minutes`,
								`week_days`,
								`day_time`,
								`imei`,
								`sub_accounts`,
								`checked_value`,
								`route_trigger`,
								`zone_trigger`,
								`routes`,
								`zones`,
								`notify_system`,
								`notify_push`,
								`notify_email`,
								`notify_email_address`,
								`notify_sms`,
								`notify_sms_number`,
								`notify_telegram`,
								`telegram_chat_id`,
								`email_template_id`,
								`sms_template_id`,
								`notify_arrow`,
								`notify_arrow_color`,
								`notify_ohc`,
								`notify_ohc_color`,
								`webhook_send`,
								`webhook_url`,
								`cmd_send`,
								`cmd_gateway`,
								`cmd_type`,
								`cmd_string`
								) VALUES (
								'" . $user_id . "',
								'" . $type . "',
								'" . $name . "',
								'" . $active . "',
								'" . $duration_from_last_event . "',
								'" . $duration_from_last_event_minutes . "',
								'" . $week_days . "',
								'" . $day_time . "',
								'" . $imei . "',
								'" . $subaccounts . "',
								'" . $checked_value . "',
								'" . $route_trigger . "',
								'" . $zone_trigger . "',
								'" . $routes . "',
								'" . $zones . "',
								'" . $notify_system . "',
								'" . $notify_push . "',
								'" . $notify_email . "',
								'" . $notify_email_address . "',
								'" . $notify_sms . "',												
								'" . $notify_sms_number . "',
								'" . $notify_telegram . "',
								'" . $telegram_chat_id . "',
								'" . $email_template_id . "',
								'" . $sms_template_id . "',
								'" . $notify_arrow . "',
								'" . $notify_arrow_color . "',
								'" . $notify_ohc . "',
								'" . $notify_ohc_color . "',
								'" . $webhook_send . "',
								'" . $webhook_url . "',
								'" . $cmd_send . "',
								'" . $cmd_gateway . "',
								'" . $cmd_type . "',
								'" . $cmd_string . "')";
		addRowBinnacle($_SESSION["user_id"], 'Creación de evento: ' . $name, $q);
	} else {
		$q = "UPDATE `gs_user_events` SET 	`type`='" . $type . "', 
								`name`='" . $name . "',
								`active`='" . $active . "',
								`duration_from_last_event`='" . $duration_from_last_event . "',
								`duration_from_last_event_minutes`='" . $duration_from_last_event_minutes . "',
								`week_days`='" . $week_days . "',
								`day_time`='" . $day_time . "',
								`imei`='" . $imei . "',
								`sub_accounts`='" . $subaccounts . "',
								`checked_value`='" . $checked_value . "',
								`route_trigger`='" . $route_trigger . "',
								`zone_trigger`='" . $zone_trigger . "',
								`routes`='" . $routes . "',
								`zones`='" . $zones . "',
								`notify_system`='" . $notify_system . "',
								`notify_push`='" . $notify_push . "',
								`notify_email`='" . $notify_email . "',
								`notify_email_address`='" . $notify_email_address . "',
								`notify_sms`='" . $notify_sms . "',
								`notify_sms_number`='" . $notify_sms_number . "',
								`notify_telegram`='" . $notify_telegram . "',
								`telegram_chat_id`='" . $telegram_chat_id . "',
								`email_template_id`='" . $email_template_id . "',
								`sms_template_id`='" . $sms_template_id . "',
								`notify_arrow`='" . $notify_arrow . "',
								`notify_arrow_color`='" . $notify_arrow_color . "',
								`notify_ohc`='" . $notify_ohc . "',
								`notify_ohc_color`='" . $notify_ohc_color . "',
								`webhook_send`='" . $webhook_send . "',
								`webhook_url`='" . $webhook_url . "',
								`cmd_send`='" . $cmd_send . "',
								`cmd_gateway`='" . $cmd_gateway . "',
								`cmd_type`='" . $cmd_type . "',
								`cmd_string`='" . $cmd_string . "'
								WHERE `event_id`='" . $event_id . "'";
		addRowBinnacle($_SESSION["user_id"], 'Actualización de evento: ' . $name, $q);
	}
	$r = mysqli_query($ms, $q);

	echo 'OK';
}

// Obtener el token de vinculación actual de un evento específico
if (@$_POST['cmd'] == 'get_event_token') {
	if (!isset($_SESSION['user_id'])) {
		echo json_encode(['success' => false]);
		die;
	}
	$user_id = ($_SESSION['manager_id'] > 0) ? (int)$_SESSION['manager_id'] : (int)$_SESSION['user_id'];
	$event_id = (int)$_POST['event_id'];
	$q = "SELECT `telegram_link_token`, `telegram_chat_id`, `notify_telegram` FROM `gs_user_events` WHERE `event_id`='" . $event_id . "' AND `user_id`='" . $user_id . "' LIMIT 1";
	$r = mysqli_query($ms, $q);
	$row = $r ? mysqli_fetch_assoc($r) : null;
	echo json_encode([
		'success' => true,
		'token' => $row ? ($row['telegram_link_token'] ?? '') : '',
		'chat_id' => $row ? ($row['telegram_chat_id'] ?? '') : '',
		'notify_telegram' => $row ? ($row['notify_telegram'] ?? '0') : '0'
	]);
	die;
}
?>