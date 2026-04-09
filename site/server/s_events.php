<?
// $loc - location data array
// $ed - event data array
// $ud - user data array
// $od - object data array

function check_events($loc, $loc_prev, $loc_events, $params_events, $service_events)
{
	global $ms;

	$q = "SELECT gs_objects.*, gs_user_objects.*
				FROM gs_objects
				INNER JOIN gs_user_objects ON gs_objects.imei = gs_user_objects.imei
				WHERE gs_user_objects.imei='" . $loc['imei'] . "'";

	$r = mysqli_query($ms, $q);

	while ($od = mysqli_fetch_array($r)) {
		// get user data
		$q2 = "SELECT * FROM `gs_users` WHERE `id`='" . $od['user_id'] . "'";
		$r2 = mysqli_query($ms, $q2);
		$ud = mysqli_fetch_array($r2);

		// events loop
		$q2 = "SELECT * FROM `gs_user_events` WHERE `user_id`='" . $od['user_id'] . "' AND UPPER(`imei`) LIKE '%" . $loc['imei'] . "%'";
		$r2 = mysqli_query($ms, $q2);


		while ($ed = mysqli_fetch_array($r2)) {
			if ($ed['active'] == 'true') {

				// check for loc events
				if ($loc_events == true) {
					if ($ed['type'] == 'overspeed') {
						event_overspeed($ed, $ud, $od, $loc);
					}
					if ($ed['type'] == 'underspeed') {
						event_underspeed($ed, $ud, $od, $loc);
					}
					if ($ed['type'] == 'route_in') {
						event_route_in($ed, $ud, $od, $loc);
					}
					if ($ed['type'] == 'route_out') {
						event_route_out($ed, $ud, $od, $loc);
					}
					if ($ed['type'] == 'zone_in') {
						event_zone_in($ed, $ud, $od, $loc);
					}
					if ($ed['type'] == 'zone_out') {
						event_zone_out($ed, $ud, $od, $loc);
					}
				}

				// check for params events
				if ($params_events == true) {
					if ($ed['type'] == 'param') {
						event_param($ed, $ud, $od, $loc, $loc_prev);
					}

					if ($ed['type'] == 'sensor') {
						event_sensor($ed, $ud, $od, $loc, $loc_prev);
					}

					if ($ed['type'] == 'driverch') {
						event_driver_change($ed, $ud, $od, $loc, $loc_prev);
					}

					if ($ed['type'] == 'trailerch') {
						event_trailer_change($ed, $ud, $od, $loc, $loc_prev);
					}
				}

				// check for service events
				if ($service_events == true) {
					if (($ed['type'] == 'connyes') || ($ed['type'] == 'connno')) {
						event_connection($ed, $ud, $od, $loc);
					}

					if (($ed['type'] == 'gpsyes') || ($ed['type'] == 'gpsno')) {
						event_gps($ed, $ud, $od, $loc);
					}

					if (($ed['type'] == 'stopped') || ($ed['type'] == 'moving') || ($ed['type'] == 'engidle')) {
						event_stopped_moving_engidle($ed, $ud, $od, $loc);
					}
					if ($ed['type'] == 'zone_in_stop_engine') {
						zone_in_stop_engine($ed, $ud, $od, $loc);
					}
				}

				// check for GPS tracker events
				if (!isset($loc['event'])) {
					continue;
				}
				
				if (strtolower($ed['type']) === 'sos' && strtolower($loc['params']['type']) === 'sos') {
					event_tracker($ed, $ud, $od, $loc);
				}

				if (($ed['type'] == 'bracon') && ($loc['event'] == 'bracon')) {
					event_tracker($ed, $ud, $od, $loc);
				}

				if (($ed['type'] == 'bracoff') && ($loc['event'] == 'bracoff')) {
					event_tracker($ed, $ud, $od, $loc);
				}

				if (($ed['type'] == 'dismount') && ($loc['event'] == 'dismount')) {
					event_tracker($ed, $ud, $od, $loc);
				}

				if (($ed['type'] == 'disassem') && ($loc['event'] == 'disassem')) {
					event_tracker($ed, $ud, $od, $loc);
				}

				if (($ed['type'] == 'door') && ($loc['event'] == 'door')) {
					event_tracker($ed, $ud, $od, $loc);
				}

				if (($ed['type'] == 'mandown') && ($loc['event'] == 'mandown')) {
					event_tracker($ed, $ud, $od, $loc);
				}

				if (($ed['type'] == 'shock') && ($loc['event'] == 'shock')) {
					event_tracker($ed, $ud, $od, $loc);
				}

				if (($ed['type'] == 'tow') && ($loc['event'] == 'tow')) {
					event_tracker($ed, $ud, $od, $loc);
				}

				if (($ed['type'] == 'haccel') && ($loc['event'] == 'haccel')) {
					event_tracker($ed, $ud, $od, $loc);
				}

				if (($ed['type'] == 'hbrake') && ($loc['event'] == 'hbrake')) {
					event_tracker($ed, $ud, $od, $loc);
				}

				if (($ed['type'] == 'hcorn') && ($loc['event'] == 'hcorn')) {
					event_tracker($ed, $ud, $od, $loc);
				}

				if (($ed['type'] == 'pwrcut') && ($loc['event'] == 'pwrcut')) {
					event_tracker($ed, $ud, $od, $loc);
				}

				if (($ed['type'] == 'gpsantcut') && ($loc['event'] == 'gpscut')) {
					event_tracker($ed, $ud, $od, $loc);
				}

				if (($ed['type'] == 'lowdc') && ($loc['event'] == 'lowdc')) {
					event_tracker($ed, $ud, $od, $loc);
				}

				if (($ed['type'] == 'lowbat') && ($loc['event'] == 'lowbat')) {
					event_tracker($ed, $ud, $od, $loc);
				}

				if (($ed['type'] == 'jamming') && ($loc['event'] == 'jamming')) {
					event_tracker($ed, $ud, $od, $loc);
				}

				if (($ed['type'] == 'dtc') && (substr($loc['event'], 0, 3) == 'dtc')) {
					event_tracker($ed, $ud, $od, $loc);
				}

				if ($ed['type'] == 'task') {
					event_programmed_journey($ed, $ud, $od, $loc);
				}
			}
		}
	}
}

function event_tracker($ed, $ud, $od, $loc)
{
	//$event_status = get_event_status($ed['event_id'], $loc['imei']);

	$ed['event_desc'] = $ed['name'];

	if ($ed['type'] == 'dtc') {
		$codes = str_replace("dtc:", "", $loc['event']);
		$codes = str_replace(",", ", ", $codes);
		$ed['event_desc'] .= ' (' . $codes . ')';
	}

	event_notify($ed, $ud, $od, $loc);
}

function event_connection($ed, $ud, $od, $loc)
{
	global $gsValues;

	$event_status_tmp = get_event_status($ed['event_id'], $loc['imei']);
	if ($ed['type'] == 'connyes') {
		if (strtotime($loc['dt_server']) >= strtotime(gmdate("Y-m-d H:i:s") . " - " . $gsValues['CONNECTION_TIMEOUT'] . " minutes")) {

			if ($event_status_tmp == -1) {
				set_event_status($ed['event_id'], $loc['imei'], '1');
				// set dt_tracker to dt_server to show exact time
				$loc['dt_tracker'] = $loc['dt_server'];
				// add event desc to event data array
				$ed['event_desc'] = $ed['name'];
				event_notify($ed, $ud, $od, $loc);
			}
		} else {
			if ($event_status_tmp != -1) {
				set_event_status($ed['event_id'], $loc['imei'], '-1');
			}
		}
	}

	if ($ed['type'] == 'connno') {
		if (strtotime($loc['dt_server']) < strtotime(gmdate("Y-m-d H:i:s") . " - " . $ed['checked_value'] . " minutes")) {
			if ($event_status_tmp == -1) {
				set_event_status($ed['event_id'], $loc['imei'], '1');
				// set dt_tracker to dt_server to show exact time
				$loc['dt_tracker'] = $loc['dt_server'];
				// add event desc to event data array
				$ed['event_desc'] = $ed['name'];
				event_notify($ed, $ud, $od, $loc);
			}
		} else {
			if ($event_status_tmp != -1) {
				set_event_status($ed['event_id'], $loc['imei'], '-1');
			}
		}
	}
}

function event_gps($ed, $ud, $od, $loc)
{
	$event_status_tmp = get_event_status($ed['event_id'], $loc['imei']);
	if ($ed['type'] == 'gpsyes') {
		if ($loc['loc_valid'] == '1') {
			if ($event_status_tmp == -1) {
				set_event_status($ed['event_id'], $loc['imei'], '1');
				// set dt_tracker to dt_server to show exact time
				$loc['dt_tracker'] = $loc['dt_server'];
				// add event desc to event data array
				$ed['event_desc'] = $ed['name'];
				event_notify($ed, $ud, $od, $loc);
			}
		} else {
			if ($event_status_tmp != -1) {
				set_event_status($ed['event_id'], $loc['imei'], '-1');
			}
		}
	}

	if ($ed['type'] == 'gpsno') {
		if (($loc['loc_valid'] == '0') && (strtotime($loc['dt_tracker']) < strtotime(gmdate("Y-m-d H:i:s") . " - " . $ed['checked_value'] . " minutes"))) {
			if ($event_status_tmp == -1) {
				set_event_status($ed['event_id'], $loc['imei'], '1');
				// set dt_tracker to dt_server to show exact time
				$loc['dt_tracker'] = $loc['dt_server'];
				// add event desc to event data array
				$ed['event_desc'] = $ed['name'];
				event_notify($ed, $ud, $od, $loc);
			}
		} else {
			if ($event_status_tmp != -1) {
				set_event_status($ed['event_id'], $loc['imei'], '-1');
			}
		}
	}
}

function event_stopped_moving_engidle($ed, $ud, $od, $loc)
{
	$dt_last_stop = strtotime($loc['dt_last_stop']);
	$dt_last_idle = strtotime($loc['dt_last_idle']);
	$dt_last_move = strtotime($loc['dt_last_move']);
	$event_status_tmp = get_event_status($ed['event_id'], $loc['imei']);

	if (($dt_last_stop > 0) || ($dt_last_move > 0)) {
		if ($ed['type'] == 'stopped') {
			if (($dt_last_stop >= $dt_last_move) && (strtotime($loc['dt_last_stop']) < strtotime(gmdate("Y-m-d H:i:s") . " - " . $ed['checked_value'] . " minutes"))) {
				if ($event_status_tmp == -1) {
					set_event_status($ed['event_id'], $loc['imei'], '1');
					// set dt_tracker to dt_server to show exact time
					$loc['dt_tracker'] = $loc['dt_server'];
					// add event desc to event data array
					$ed['event_desc'] = $ed['name'];
					event_notify($ed, $ud, $od, $loc);
				}
			} else {
				if ($event_status_tmp != -1) {
					set_event_status($ed['event_id'], $loc['imei'], '-1');
				}
			}
		}

		if ($ed['type'] == 'moving') {
			if (($dt_last_stop < $dt_last_move) && (strtotime($loc['dt_last_move']) < strtotime(gmdate("Y-m-d H:i:s") . " - " . $ed['checked_value'] . " minutes"))) {
				if ($event_status_tmp == -1) {
					set_event_status($ed['event_id'], $loc['imei'], '1');
					// set dt_tracker to dt_server to show exact time
					$loc['dt_tracker'] = $loc['dt_server'];
					// add event desc to event data array
					$ed['event_desc'] = $ed['name'];
					event_notify($ed, $ud, $od, $loc);
				}
			} else {
				if ($event_status_tmp != -1) {
					set_event_status($ed['event_id'], $loc['imei'], '-1');
				}
			}
		}

		if ($ed['type'] == 'engidle') {
			if (($dt_last_stop <= $dt_last_idle) && ($dt_last_move <= $dt_last_idle) && (strtotime($loc['dt_last_idle']) < strtotime(gmdate("Y-m-d H:i:s") . " - " . $ed['checked_value'] . " minutes"))) {
				if ($event_status_tmp == -1) {
					set_event_status($ed['event_id'], $loc['imei'], '1');
					// set dt_tracker to dt_server to show exact time
					$loc['dt_tracker'] = $loc['dt_server'];
					// add event desc to event data array
					$ed['event_desc'] = $ed['name'];
					event_notify($ed, $ud, $od, $loc);
				}
			} else {
				if ($event_status_tmp != -1) {
					set_event_status($ed['event_id'], $loc['imei'], '-1');
				}
			}
		}
	}
}

function event_route_in($ed, $ud, $od, $loc)
{
	global $ms;

	// get user units and convert if needed
	$units = explode(",", $ud['units']);

	$event_status = get_event_status($ed['event_id'], $loc['imei']);

	// check if route still exists, to fix bug if user deletes zone
	$q = "SELECT * FROM `gs_user_routes` WHERE `route_id`='" . $event_status . "'";
	$r = mysqli_query($ms, $q);

	if (mysqli_num_rows($r) == 0) {
		set_event_status($ed['event_id'], $loc['imei'], '-1');

		$event_status = '-1';
	}

	// check event
	$q = "SELECT * FROM `gs_user_routes` WHERE `user_id`='" . $ed['user_id'] . "' AND `route_id` IN (" . $ed['routes'] . ")";
	$r = mysqli_query($ms, $q);

	while ($route = mysqli_fetch_array($r)) {
		$dist = isPointOnLine($route['route_points'], $loc['lat'], $loc['lng']);

		$dist = convDistanceUnits($dist, 'km', $units[0]);

		if ($dist <= $route['route_deviation']) {
			if ($event_status == -1) {
				set_event_status($ed['event_id'], $loc['imei'], $route['route_id']);
				// add event desc to event data array
				$ed['event_desc'] = $ed['name'] . ' (' . $route['route_name'] . ')';
				// add route name
				$ed['route_name'] = $route['route_name'];
				event_notify($ed, $ud, $od, $loc);
			}
		} else {
			if ($event_status == $route['route_id']) {
				set_event_status($ed['event_id'], $loc['imei'], '-1');
			}
		}
	}
}

function event_route_out($ed, $ud, $od, $loc)
{
	global $ms;

	// get user units and convert if needed
	$units = explode(",", $ud['units']);

	$event_status = get_event_status($ed['event_id'], $loc['imei']);

	// check if route still exists, to fix bug if user deletes zone
	$q = "SELECT * FROM `gs_user_routes` WHERE `route_id`='" . $event_status . "'";
	$r = mysqli_query($ms, $q);

	if (mysqli_num_rows($r) == 0) {
		set_event_status($ed['event_id'], $loc['imei'], '-1');

		$event_status = '-1';
	}

	// check event
	$q = "SELECT * FROM `gs_user_routes` WHERE `user_id`='" . $ed['user_id'] . "' AND `route_id` IN (" . $ed['routes'] . ")";
	$r = mysqli_query($ms, $q);

	while ($route = mysqli_fetch_array($r)) {
		$dist = isPointOnLine($route['route_points'], $loc['lat'], $loc['lng']);


		$dist = convDistanceUnits($dist, 'km', $units[0]);

		if ($dist < $route['route_deviation']) {
			if ($event_status == -1) {
				set_event_status($ed['event_id'], $loc['imei'], $route['route_id']);
			}
		} else {
			if ($event_status == $route['route_id']) {
				set_event_status($ed['event_id'], $loc['imei'], '-1');
				// add event desc to event data array
				$ed['event_desc'] = $ed['name'] . ' (' . $route['route_name'] . ')';
				// add route name
				$ed['route_name'] = $route['route_name'];
				event_notify($ed, $ud, $od, $loc);
			}
		}
	}
}

function event_zone_in($ed, $ud, $od, $loc)
{
	global $ms;

	$event_status = get_event_status($ed['event_id'], $loc['imei']);

	// check if zone still exists, to fix bug if user deletes zone
	$q = "SELECT * FROM `gs_user_zones` WHERE `zone_id`='" . $event_status . "'";
	$r = mysqli_query($ms, $q);

	if (mysqli_num_rows($r) == 0) {
		set_event_status($ed['event_id'], $loc['imei'], '-1');

		$event_status = '-1';
	}

	// check event
	$q = "SELECT * FROM `gs_user_zones` WHERE `user_id`='" . $ed['user_id'] . "' AND `zone_id` IN (" . $ed['zones'] . ")";
	$r = mysqli_query($ms, $q);

	if (!$r) {
		return;
	}

	while ($zone = mysqli_fetch_array($r)) {
		$in_zone = isPointInPolygon($zone['zone_vertices'], $loc['lat'], $loc['lng']);

		if ($in_zone) {
			if ($event_status == -1) {
				set_event_status($ed['event_id'], $loc['imei'], $zone['zone_id']);
				// add event desc to event data array
				$ed['event_desc'] = $ed['name'] . ' (' . $zone['zone_name'] . ')';
				// add zone name
				$ed['zone_name'] = $zone['zone_name'];
				event_notify($ed, $ud, $od, $loc);
			}
		} else {
			if ($event_status == $zone['zone_id']) {
				set_event_status($ed['event_id'], $loc['imei'], '-1');
			}
		}
	}
}

function event_programmed_journey($ed, $ud, $od, $loc)
{
	global $ms;

	$q = "SELECT DISTINCT(gs_user_objects.user_id), gs_object_tasks.task_id as task_id, gs_object_tasks.imei_truck_tractor as imei_truck_tractor, now() as current_dt,   gs_objects.lat, gs_objects.lng,
            gs_object_tasks.task_id as id_tarea_programada, (select zone_vertices from gs_user_zones where zone_id = gs_object_tasks.initial_zone) as initial_zone, (select zone_vertices from gs_user_zones where zone_id = gs_object_tasks.ended_zone)  as ended_zone,
            (select zone_name from gs_user_zones where zone_id = gs_object_tasks.initial_zone) as zone_name_start, (select zone_name from gs_user_zones where zone_id = gs_object_tasks.ended_zone)  as zone_name_end,
            gs_objects.dt_server, gs_objects.dt_tracker,
            gs_objects.name as name_gps,
            gs_object_tasks.journey_name, gs_object_tasks.status, 
            gs_object_tasks.start_from_dt, gs_object_tasks.start_to_dt, gs_object_tasks.end_from_dt, gs_object_tasks.end_to_dt  
            FROM `gs_user_objects` 
            INNER JOIN gs_objects ON gs_user_objects.imei = gs_objects.imei
            INNER JOIN gs_object_tasks ON gs_objects.imei = gs_object_tasks.imei_truck_tractor 
            WHERE gs_object_tasks.imei_truck_tractor='" . $loc['imei'] . "'
            AND STR_TO_DATE( gs_object_tasks.start_from_dt, '%Y-%m-%d %T' ) > DATE_SUB(NOW(), INTERVAL 1 DAY)";

	$r = mysqli_query($ms, $q);

	if (mysqli_num_rows($r) > 0) {
		while ($row = mysqli_fetch_assoc($r)) {
			$_zone_start_distance = verifyGPSNearestZone($row['initial_zone'], $row['lat'], $row['lng']);
			$startdistanceValue = floatval($_zone_start_distance);
			$_zone_end_distance = verifyGPSNearestZone($row['ended_zone'], $row['lat'], $row['lng']);
			$enddistanceValue = floatval($_zone_end_distance);
			$current_utc = new DateTime($row['current_dt']);
			$interval = new DateInterval('PT6H');
			$local = $current_utc->sub($interval);
			$_current_dt = $local->getTimestamp() * 1000;
			$_start_to_dt = strtotime($row['start_to_dt']) * 1000;
			$_start_to_dt_add_30_mins = $_start_to_dt + (30 * 60 * 1000);
			$_start_from_dt = strtotime($row['start_from_dt']) * 1000;
			$_end_to_dt = strtotime($row['end_to_dt']);
			$_end_to_dt_add_30_mins = $_end_to_dt + (1000 * 60 * 30);
			$_end_from_dt = strtotime($row['end_from_dt']);
			$_status = $row['status'];
			$_task_id = $row['task_id'];
			$_new_status = 0;

			if ($_status == 0 && $_current_dt >= $_start_from_dt && $_current_dt <= $_start_to_dt && $startdistanceValue >= 0) {
				$_new_status = 1;
				$ed['event_desc'] = 'Viaje programado ha iniciado.';
				event_notify($ed, $ud, $od, $loc);
			} elseif (($_status == 1 || $_status == 3) && $_current_dt >= $_end_from_dt && $_current_dt <= $_end_to_dt && $enddistanceValue == 0) {
				$_new_status = 2;
				$ed['event_desc'] = 'Viaje programado ha terminado.';
				event_notify($ed, $ud, $od, $loc);
			} elseif ($_status == 0 && $_current_dt > $_start_to_dt && $startdistanceValue > 0) {
				$_new_status = 3;
				$ed['event_desc'] = 'Viaje programado ha iniciado con retrazo.';
				event_notify($ed, $ud, $od, $loc);
			} elseif (($_status == 1 || $_status == 3) && $_current_dt > $_end_to_dt_add_30_mins && $enddistanceValue == 0) {
				$_new_status = 6;
				$ed['event_desc'] = 'Viaje programado ha terminado de 30min.';
				event_notify($ed, $ud, $od, $loc);
			} elseif (($_status == 1 || $_status == 3) && $_current_dt > $_end_to_dt && $enddistanceValue == 0) {
				$_new_status = 4;
				$ed['event_desc'] = 'Viaje programado ha terminado con retrazo.';
				event_notify($ed, $ud, $od, $loc);
			} elseif (($_status == 1 || $_status == 3) && $_current_dt > $_end_to_dt_add_30_mins && $startdistanceValue > 0) {
				$_new_status = 5;
				$ed['event_desc'] = 'Viaje programado ha fallado, exedio el tiempo de llegada.';
				event_notify($ed, $ud, $od, $loc);
			} elseif ($_status == 0 && $_current_dt > $_end_to_dt_add_30_mins && $startdistanceValue == 0) {
				$_new_status = 7;
				$ed['event_desc'] = 'Viaje programado ha terminado, unidad nunca comenzo el viaje.';
				event_notify($ed, $ud, $od, $loc);
			}

			if ($_status == 0 && $_new_status != 0 || $_status > 0 && $_new_status != 0) {

				$q = "UPDATE gs_object_tasks SET status = '" . $_new_status . "' WHERE task_id =" . $_task_id;
				mysqli_query($ms, $q);
			} else {
				$q = "UPDATE gs_object_tasks SET status = '" . $_status . "' WHERE task_id =" . $_task_id;
				mysqli_query($ms, $q);
			}

			echo 'OK';
			die;
		}
	}
}
function event_zone_out($ed, $ud, $od, $loc)
{
	global $ms;

	$event_status = get_event_status($ed['event_id'], $loc['imei']);

	// check if zone still exists, to fix bug if user deletes zone
	$q = "SELECT * FROM `gs_user_zones` WHERE `zone_id`='" . $event_status . "'";
	$r = mysqli_query($ms, $q);

	if (mysqli_num_rows($r) == 0) {
		set_event_status($ed['event_id'], $loc['imei'], '-1');

		$event_status = '-1';
	}

	// check event
	$q = "SELECT * FROM `gs_user_zones` WHERE `user_id`='" . $ed['user_id'] . "' AND `zone_id` IN (" . $ed['zones'] . ")";
	$r = mysqli_query($ms, $q);

	if (!$r) {
		return;
	}

	while ($zone = mysqli_fetch_array($r)) {
		$in_zone = isPointInPolygon($zone['zone_vertices'], $loc['lat'], $loc['lng']);

		if ($in_zone) {
			if ($event_status == -1) {
				set_event_status($ed['event_id'], $loc['imei'], $zone['zone_id']);
			}
		} else {
			if ($event_status == $zone['zone_id']) {
				set_event_status($ed['event_id'], $loc['imei'], '-1');
				// add event desc to event data array
				$ed['event_desc'] = $ed['name'] . ' (' . $zone['zone_name'] . ')';
				// add zone name
				$ed['zone_name'] = $zone['zone_name'];
				event_notify($ed, $ud, $od, $loc);
			}
		}
	}
}
function zone_in_stop_engine($ed, $ud, $od, $loc)
{
	global $ms;

	$event_status = get_event_status($ed['event_id'], $loc['imei']);
	$send_status = get_send_status($ed['event_id'], $loc['imei']);
	$start_engine = get_start_engine($ed['event_id'], $loc['imei']);

	// check event
	$q = "SELECT * FROM `gs_user_zones` WHERE `user_id`='" . $ed['user_id'] . "' AND `zone_id` IN (" . $ed['zones'] . ")";
	$r = mysqli_query($ms, $q);

	if (!$r) {
		return;
	}

	$is_in_any_zone = false;

	while ($zone = mysqli_fetch_array($r)) {
		$in_zone = isPointInPolygon($zone['zone_vertices'], $loc['lat'], $loc['lng']);

		if ($in_zone) {
			$is_in_any_zone = true;

			$zone_entry_time = get_zone_entry_time($ms, $ed['event_id'], $loc['imei']);

			if (!$zone_entry_time) {
				set_zone_entry_time($ms, $ed['event_id'], $loc['imei'], $loc['dt_server']);
			} else {
				$detenido = date('Y-m-d H:i:s', strtotime($loc['dt_last_stop'] . ' -6 hours'));
				$ahora = strtotime(gmdate("Y-m-d H:i:s", strtotime("-6 hours")));

				$tiempo_en_zona = $ahora - strtotime($zone_entry_time);
				$tiempo_detenido = $ahora - strtotime($detenido);

				if ($tiempo_en_zona > $ed['cmd_send'] * 60 && $tiempo_detenido > $ed['cmd_send'] * 60) {
					$bloqueo = (!empty($loc['params']['out1']) ? $loc['params']['out1'] : ($loc['params']['output'] ?? null));


					if ($bloqueo == 0 && $event_status == -1 && $send_status == 'false') {
						$loc['dt_tracker'] = $loc['dt_server'];
						$ed['event_desc'] = $ed['name'] . ' (Geocerca: ' . $zone['zone_name'] . ')';
						event_notify($ed, $ud, $od, $loc);
					}
					if ($bloqueo == 0 && $event_status == -1 && $send_status == 'desbloqueado') {
						$loc['dt_tracker'] = $loc['dt_server'];
						$ed['event_desc'] = $ed['name'] . ' (Geocerca: ' . $zone['zone_name'] . ')';
						event_notify($ed, $ud, $od, $loc);
					}

					if ($bloqueo == 1 && $event_status == 1 && $send_status == 'bloqueo' && $start_engine == 'true') {
						$hora_actual_local = gmdate('H:i', strtotime('-6 hours'));
						$fecha_actual_local = gmdate('Y-m-d', strtotime('-6 hours'));
						$fecha_in_zone = date('Y-m-d', strtotime($zone_entry_time));

						if (isset($ed['cmd_gateway'])) {
							$hora_encendido = $ed['cmd_gateway'];

							if ($hora_actual_local >= $hora_encendido && strtotime($fecha_actual_local) > strtotime($fecha_in_zone)) {
								$loc['dt_tracker'] = $loc['dt_server'];
								$ed['event_desc'] = 'Encendido_Programado: ' . $ed['name'] . ' (Geocerca: ' . $zone['zone_name'] . ')';
								event_notify($ed, $ud, $od, $loc);
							}
						}
					}
				}
			}
		}
	}

	if (!$is_in_any_zone) {
		delete_zone_entry_time($ms, $ed['event_id'], $loc['imei']);
		set_send_status($ed['event_id'], $loc['imei'], 'false');
		if ($event_status != -1) {
			set_event_status($ed['event_id'], $loc['imei'], '-1');
		}
	}

}

function get_zone_entry_time($ms, $event_id, $imei)
{
	$q = "SELECT `in_zone` FROM `gs_user_events_status` WHERE `event_id` = '$event_id' AND `imei` = '$imei'";
	$r = mysqli_query($ms, $q);
	if ($r && $row = mysqli_fetch_assoc($r)) {
		$in_zone = $row['in_zone'];
		if ($in_zone && $in_zone !== '0000-00-00 00:00:00') {
			return $in_zone;
		}
	}
	return null;
}
function get_start_engine($event_id, $imei)
{
	global $ms;

	$result = 'false';

	$q = "SELECT * FROM `gs_user_events` WHERE `event_id`='" . $event_id . "' AND `imei`='" . $imei . "'";
	$r = mysqli_query($ms, $q);
	$row = mysqli_fetch_array($r);
	$result = $row['webhook_send'];

	if ($result) {
		$result = $row['webhook_send'];
	} else {
		$result = 'false';
	}

	return $result;
}
function get_send_status($event_id, $imei)
{
	global $ms;

	$result = 'false';

	$q = "SELECT * FROM `gs_user_events_status` WHERE `event_id`='" . $event_id . "' AND `imei`='" . $imei . "'";
	$r = mysqli_query($ms, $q);
	$row = mysqli_fetch_array($r);
	$result = $row['event_send'];

	if ($result) {
		$result = $row['event_send'];
	} else {
		$result = 'false';
	}

	return $result;
}

function set_send_status($event_id, $imei, $value)
{
	global $ms;

	$q = "UPDATE `gs_user_events_status` SET `event_send`='" . $value . "' WHERE `event_id`='" . $event_id . "' AND `imei`='" . $imei . "'";
	$r = mysqli_query($ms, $q);
}
function set_zone_entry_time($ms, $event_id, $imei, $time)
{
	$fecha_in_zone = date("Y-m-d H:i:s", strtotime($time . ' -6 hours'));

	$q = "UPDATE `gs_user_events_status` SET `in_zone` = '$fecha_in_zone' WHERE `event_id` = '$event_id' AND `imei` = '$imei'";
	mysqli_query($ms, $q);
}

function delete_zone_entry_time($ms, $event_id, $imei)
{
	$q = "UPDATE `gs_user_events_status`SET `in_zone` = NULL WHERE `event_id` = '$event_id' AND `imei` = '$imei'";
	mysqli_query($ms, $q);
}

function event_param($ed, $ud, $od, $loc, $loc_prev)
{
	$condition = false;
	$params = $loc['params'];
	$params_prev = $loc_prev['params'];
	$units = explode(",", $ud['units']);

	$pc = json_decode($ed['checked_value'], true);
	if ($pc == null) {
		return;
	}

	// check conditions
	for ($i = 0; $i < count($pc); $i++) {
		$cn = false;

		if (($pc[$i]['cn'] == 'grp') || ($pc[$i]['cn'] == 'lwp')) {
			$pc[$i]['val'] = trim(str_replace("%", "", $pc[$i]['val']));

			if ($pc[$i]['src'] == 'speed') {
				$value = convSpeedUnits($loc['speed'], 'km', $units[0]);
				$value_prev = convSpeedUnits($loc_prev['speed'], 'km', $units[0]);
			} else {
				// check if param exits
				if ((!isset($params[$pc[$i]['src']])) || (!isset($params_prev[$pc[$i]['src']]))) {
					$condition = false;
					break;
				}

				$value = $params[$pc[$i]['src']];
				$value_prev = $params_prev[$pc[$i]['src']];
			}

			if ($value_prev == 0) {
				$condition = false;
				break;
			}

			if (($pc[$i]['cn'] == 'grp') && ($value > $value_prev)) {
				$percent_diff = ($value - $value_prev) / $value_prev * 100;
				$percent_diff = abs($percent_diff);
				if ($percent_diff > $pc[$i]['val'])
					$cn = true;
			}

			if (($pc[$i]['cn'] == 'lwp') && ($value < $value_prev)) {
				$percent_diff = ($value - $value_prev) / $value_prev * 100;
				$percent_diff = abs($percent_diff);
				if ($percent_diff > $pc[$i]['val'])
					$cn = true;
			}
		} else {

			if ($pc[$i]['src'] == 'alertaDMS') {
				$value = getDmsAlert($loc['imei'], $pc[$i]['val']);

				if ($value == 'true') {
					$cn = true;
				}
			}

			if ($pc[$i]['src'] == 'speed') {
				$value = convSpeedUnits($loc['speed'], 'km', $units[0]);
			} else {
				// check if param exits
				if (!isset($params[$pc[$i]['src']]) && $cn == false) {
					$condition = false;
					break;
				}

				$value = $params[$pc[$i]['src']];
			}

			if ($pc[$i]['cn'] == 'eq') {
				if ($value == $pc[$i]['val'])
					$cn = true;
			}

			if ($pc[$i]['cn'] == 'gr') {
				if ($value > $pc[$i]['val'])
					$cn = true;
			}

			if ($pc[$i]['cn'] == 'lw') {
				if ($value < $pc[$i]['val'])
					$cn = true;
			}
		}

		if ($cn == true) {
			$condition = true;
		} else {
			$condition = false;
			break;
		}
	}

	if ($condition) {
		if (get_event_status($ed['event_id'], $loc['imei']) == -1) {
			set_event_status($ed['event_id'], $loc['imei'], '1');
			// add event desc to event data array
			$ed['event_desc'] = $ed['name'];
			event_notify($ed, $ud, $od, $loc);
		}
	} else {
		if (get_event_status($ed['event_id'], $loc['imei']) != -1) {
			set_event_status($ed['event_id'], $loc['imei'], '-1');
		}
	}
}

function event_sensor($ed, $ud, $od, $loc, $loc_prev)
{
	$condition = false;
	$params = $loc['params'];
	$params_prev = $loc_prev['params'];
	$units = explode(",", $ud['units']);

	$sc = json_decode($ed['checked_value'], true);
	if ($sc == null) {
		return;
	}

	$sensors = getSensors($loc['imei']);

	if ($sensors == false) {
		return;
	}

	// check conditions
	for ($i = 0; $i < count($sc); $i++) {
		$cn = false;

		if (($sc[$i]['cn'] == 'grp') || ($sc[$i]['cn'] == 'lwp') || ($sc[$i]['cn'] == 'lw') || ($sc[$i]['cn'] == 'gr')) {
			$sc[$i]['val'] = trim(str_replace("%", "", $sc[$i]['val']));

			if ($sc[$i]['src'] == 'speed') {
				$value = convSpeedUnits($loc['speed'], 'km', $units[0]);
				$value_prev = convSpeedUnits($loc_prev['speed'], 'km', $units[0]);
			} else {
				$sensor = false;

				for ($j = 0; $j < count($sensors); ++$j) {
					if ($sc[$i]['src'] == $sensors[$j]['name']) {
						$sensor = $sensors[$j];
					}
				}

				// check if sensor exits
				if (!$sensor) {
					$condition = false;
					break;
				}

				// check if param exits
				if ((!isset($params[$sensor['param']])) || (!isset($params_prev[$sensor['param']]))) {
					$condition = false;
					break;
				}

				$sensor_value = getSensorValue($params, $sensor);
				$sensor_value_prev = getSensorValue($params_prev, $sensor);

				$value = $sensor_value['value'];
				$value_prev = $sensor_value_prev['value'];
			}

			if ($value_prev == 0) {
				$condition = false;
				break;
			}

			if (($sc[$i]['cn'] == 'grp') && ($value > $value_prev)) {
				$percent_diff = ($value - $value_prev) / $value_prev * 100;
				$percent_diff = abs($percent_diff);
				if ($percent_diff > $sc[$i]['val'])
					$cn = true;
			}

			if (($sc[$i]['cn'] == 'lwp') && ($value < $value_prev)) {
				$percent_diff = ($value - $value_prev) / $value_prev * 100;
				$percent_diff = abs($percent_diff);
				if ($percent_diff > $sc[$i]['val'])
					$cn = true;
			}

			if (($sc[$i]['cn'] == 'lw') && ($value < $value_prev)) {
				$percent_diff = ($value - $value_prev);
				$fuel_diff = abs($percent_diff);
				if ($fuel_diff > $sc[$i]['val'])
					$cn = true;
			}

			if (($sc[$i]['cn'] == 'gr') && ($value > $value_prev)) {
				$percent_diff = ($value - $value_prev);
				$fuel_diff = abs($percent_diff);
				if ($fuel_diff > $sc[$i]['val'])
					$cn = true;
			}
		} else {
			if ($sc[$i]['src'] == 'speed') {
				$value = convSpeedUnits($loc['speed'], 'km', $units[0]);
			} else {
				$sensor = false;

				for ($j = 0; $j < count($sensors); ++$j) {
					if ($sc[$i]['src'] == $sensors[$j]['name']) {
						$sensor = $sensors[$j];
					}
				}

				// check if sensor exits
				if (!$sensor) {
					$condition = false;
					break;
				}

				// check if param exits
				if (!isset($params[$sensor['param']])) {
					$condition = false;
					break;
				}

				$sensor_value = getSensorValue($params, $sensor);

				$value = $sensor_value['value'];
			}

			if ($sc[$i]['cn'] == 'eq') {
				if ($value == $sc[$i]['val'])
					$cn = true;
			}
		}

		if ($cn == true) {
			$condition = true;
		} else {
			$condition = false;
			break;
		}
	}

	if ($condition) {
		if (get_event_status($ed['event_id'], $loc['imei']) == -1) {
			set_event_status($ed['event_id'], $loc['imei'], '1');

			// add event desc to event data array
			if ($sc[0]['cn'] == 'gr') {
				$ed['event_desc'] = $ed['name'] . ' ' . $fuel_diff . ' lts';
			} elseif ($sc[0]['cn'] == 'lw') {
				$ed['event_desc'] = $ed['name'] . ' ' . $fuel_diff . ' lts';
			} else {
				$ed['event_desc'] = $ed['name'];
			}
			event_notify($ed, $ud, $od, $loc);
		}
	} else {
		if (get_event_status($ed['event_id'], $loc['imei']) != -1) {
			set_event_status($ed['event_id'], $loc['imei'], '-1');
		}
	}
}

function event_driver_change($ed, $ud, $od, $loc, $loc_prev)
{
	$imei = $loc['imei'];
	$params = $loc['params'];
	$params_prev = $loc_prev['params'];

	$group_array = array('da');

	for ($i = 0; $i < count($group_array); ++$i) {
		$group = $group_array[$i];

		$sensor = getSensorFromType($imei, $group);

		if ($sensor != false) {
			$sensor_ = $sensor[0];

			$sensor_data = getSensorValue($params, $sensor_);
			$assign_id = $sensor_data['value'];

			$sensor_data_prev = getSensorValue($params_prev, $sensor_);
			$assign_id_prev = $sensor_data_prev['value'];

			if ((string) $assign_id != (string) $assign_id_prev) {
				// add event desc to event data array
				$ed['event_desc'] = $ed['name'];
				event_notify($ed, $ud, $od, $loc);
			}
		}
	}
}

function event_trailer_change($ed, $ud, $od, $loc, $loc_prev)
{
	$imei = $loc['imei'];
	$params = $loc['params'];
	$params_prev = $loc_prev['params'];

	$group_array = array('ta');

	for ($i = 0; $i < count($group_array); ++$i) {
		$group = $group_array[$i];

		$sensor = getSensorFromType($imei, $group);

		if ($sensor != false) {
			$sensor_ = $sensor[0];

			$sensor_data = getSensorValue($params, $sensor_);
			$assign_id = $sensor_data['value'];

			$sensor_data_prev = getSensorValue($params_prev, $sensor_);
			$assign_id_prev = $sensor_data_prev['value'];

			if ((string) $assign_id != (string) $assign_id_prev) {
				// add event desc to event data array
				$ed['event_desc'] = $ed['name'];
				event_notify($ed, $ud, $od, $loc);
			}
		}
	}
}

function event_overspeed($ed, $ud, $od, $loc)
{
	$speed = $loc['speed'];

	// get user speed unit and convert if needed
	$units = explode(",", $ud['units']);
	$speed = convSpeedUnits($speed, 'km', $units[0]);

	if ($speed > $ed['checked_value']) {
		if (get_event_status($ed['event_id'], $loc['imei']) == -1) {
			set_event_status($ed['event_id'], $loc['imei'], '1');
			// add event desc to event data array
			$ed['event_desc'] = $ed['name'];
			;
			event_notify($ed, $ud, $od, $loc);
		}
	} else {
		if (get_event_status($ed['event_id'], $loc['imei']) != -1) {
			set_event_status($ed['event_id'], $loc['imei'], '-1');
		}
	}
}

function event_underspeed($ed, $ud, $od, $loc)
{
	$speed = $loc['speed'];

	// get user speed unit and convert if needed
	$units = explode(",", $ud['units']);
	$speed = convSpeedUnits($speed, 'km', $units[0]);

	if ($speed < $ed['checked_value']) {
		if (get_event_status($ed['event_id'], $loc['imei']) == -1) {
			set_event_status($ed['event_id'], $loc['imei'], '1');
			// add event desc to event data array
			$ed['event_desc'] = $ed['name'];
			;
			event_notify($ed, $ud, $od, $loc);
		}
	} else {
		if (get_event_status($ed['event_id'], $loc['imei']) != -1) {
			set_event_status($ed['event_id'], $loc['imei'], '-1');
		}
	}
}

function event_notify($ed, $ud, $od, $loc)
{
	global $ms, $gsValues;

	$imei = $loc['imei'];

	if (!checkObjectActive($imei)) {
		return;
	}

	// get current date and time for week days and day time check
	$dt_check = convUserIDTimezone($ud['id'], date("Y-m-d H:i:s", strtotime($loc['dt_server'])));

	if (strpos($ed['event_desc'], 'Encendido_Programado') === false) {

		if (!check_event_week_days($dt_check, $ed['week_days'])) {
			return;
		}

		if (!check_event_day_time($dt_check, $ed['day_time'])) {
			return;
		}
	}

	$ed = check_event_route_trigger($ed, $ud, $loc);
	if ($ed == false) {
		return;
	}

	$ed = check_event_zone_trigger($ed, $ud, $loc);
	if ($ed == false) {
		return;
	}

	// duration from last event
	if (!check_event_duration_from_last($ed, $imei)) {
		return;
	} else {
		$q = "UPDATE `gs_user_events_status` SET `dt_server`='" . gmdate("Y-m-d H:i:s") . "' WHERE `event_id`='" . $ed['event_id'] . "' AND `imei`='" . $imei . "'";
		$r = mysqli_query($ms, $q);
	}

	$attended_status = 'Sin atender';
	if (isset($ed['event_desc']) && preg_match('/\/\s*Servicio Realizado\b/', $ed['event_desc'])) {
		$attended_status = 'Atendido';
	}


	// insert event into list
	$q = "INSERT INTO `gs_user_last_events_data` (	user_id,
								type,
								event_desc,
								notify_system,
								notify_push,
								notify_arrow,
								notify_arrow_color,
								notify_ohc,
								notify_ohc_color,
								imei,
								name,
								dt_server,
								dt_tracker,
								lat,
								lng,
								altitude,
								angle,
								speed,
								params,
								attended_status
								) VALUES (
								'" . $ed['user_id'] . "',
								'" . $ed['type'] . "',
								'" . $ed['event_desc'] . "',
								'" . $ed['notify_system'] . "',
								'" . $ed['notify_push'] . "',
								'" . $ed['notify_arrow'] . "',
								'" . $ed['notify_arrow_color'] . "',
								'" . $ed['notify_ohc'] . "',
								'" . $ed['notify_ohc_color'] . "',
								'" . $od['imei'] . "',
								'" . mysqli_real_escape_string($ms, $od['name']) . "',
								'" . $loc['dt_server'] . "',
								'" . $loc['dt_tracker'] . "',
								'" . $loc['lat'] . "',
								'" . $loc['lng'] . "',
								'" . $loc['altitude'] . "',
								'" . $loc['angle'] . "',
								'" . $loc['speed'] . "',
								'" . json_encode($loc['params']) . "',
								'" . $attended_status . "')";
	$r = mysqli_query($ms, $q);

	// insert event into list
	$q = "INSERT INTO `gs_user_events_data` (	user_id,
								type,
								event_desc,
								imei,
								name,
								dt_server,
								dt_tracker,
								lat,
								lng,
								altitude,
								angle,
								speed,
								params
								) VALUES (
								'" . $ed['user_id'] . "',
								'" . $ed['type'] . "',
								'" . $ed['event_desc'] . "',
								'" . $od['imei'] . "',
								'" . mysqli_real_escape_string($ms, $od['name']) . "',
								'" . $loc['dt_server'] . "',
								'" . $loc['dt_tracker'] . "',
								'" . $loc['lat'] . "',
								'" . $loc['lng'] . "',
								'" . $loc['altitude'] . "',
								'" . $loc['angle'] . "',
								'" . $loc['speed'] . "',
								'" . json_encode($loc['params']) . "')";
	$r = mysqli_query($ms, $q);


	// send webhook
	if ($ed['webhook_send'] == 'true') {
		if (checkUserUsage($ed['user_id'], 'webhook')) {
			$units = explode(",", $ud['units']);

			$speed = $loc['speed'];
			$speed = convSpeedUnits($speed, 'km', $units[0]);

			$driver = getObjectDriver($ud['id'], $od['imei'], $loc['params']);

			$trailer = getObjectTrailer($ud['id'], $od['imei'], $loc['params']);

			$odometer = getObjectOdometer($od['imei']);
			$odometer = floor(convDistanceUnits($odometer, 'km', $units[0]));

			$eng_hours = getObjectEngineHours($od['imei'], false);

			$url = $ed['webhook_url'];
			$url .= '?username=' . urlencode($ud['username']);
			$url .= '&name=' . urlencode($od['name']);
			$url .= '&imei=' . urlencode($od['imei']);
			$url .= '&type=' . urlencode($ed['type']);
			$url .= '&desc=' . urlencode($ed['event_desc']);

			if (isset($ed['zone_name'])) {
				$url .= '&zone_name=' . urlencode($ed['zone_name']);
			}

			if (isset($ed['route_name'])) {
				$url .= '&route_name=' . urlencode($ed['route_name']);
			}

			$url .= '&lat=' . urlencode($loc['lat']);
			$url .= '&lng=' . urlencode($loc['lng']);
			$url .= '&speed=' . urlencode($speed);
			$url .= '&altitude=' . urlencode($loc['altitude']);
			$url .= '&angle=' . urlencode($loc['angle']);
			$url .= '&dt_server=' . urlencode($loc['dt_server']);
			$url .= '&dt_tracker=' . urlencode($loc['dt_tracker']);

			$url .= '&tr_model=' . urlencode($od['model']);
			$url .= '&vin=' . urlencode($od['vin']);
			$url .= '&plate_number=' . urlencode($od['plate_number']);
			$url .= '&sim_number=' . urlencode($od['sim_number']);

			$url .= '&driver_name=' . urlencode($driver['driver_name']);
			$url .= '&trailer_name=' . urlencode($trailer['trailer_name']);
			$url .= '&odometer=' . urlencode($odometer);
			$url .= '&eng_hours=' . urlencode($eng_hours);

			$result = sendWebhookQueue($url);

			if ($result) {
				//update user usage
				updateUserUsage($ed['user_id'], false, false, false, 1, false);
			}
		}
	}

	// send cmd
	if ($ed['type'] == 'zone_in_stop_engine') {
		$q1 = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
		$r1 = mysqli_query($ms, $q1);
		$row1 = mysqli_fetch_array($r1);
		$device = $row1['device'];

		$bloqueo = $loc['params']['out1'] ?? $loc['params']['output'] ?? null;


		if (strpos(strtolower($device), 'teltonika') !== false) {
			if ($bloqueo != 1) {
				$q = "SELECT * FROM `gs_user_cmd` WHERE `protocol`='$device' AND `name` = 'Apagado de Motor' AND `gateway` = 'sms'";
				$r = mysqli_query($ms, $q);
				if ($row = mysqli_fetch_array($r)) {
					$cmd = $row['cmd'];
					set_event_status($ed['event_id'], $loc['imei'], '1');
					set_send_status($ed['event_id'], $loc['imei'], 'bloqueo');
					sendObjectSMSCommand($ed['user_id'], $imei, mysqli_real_escape_string($ms, $ed['event_desc']), $cmd);
				}
			} else {
				$q = "SELECT * FROM `gs_user_cmd` WHERE `protocol`='$device' AND `name` = 'Encendido de Motor' AND `gateway` = 'gprs'";
				$r = mysqli_query($ms, $q);
				if ($row = mysqli_fetch_array($r)) {
					$cmd = $row['cmd'];
					set_event_status($ed['event_id'], $loc['imei'], '-1');
					set_send_status($ed['event_id'], $loc['imei'], 'desbloqueado');
					set_zone_entry_time($ms, $ed['event_id'], $loc['imei'], $loc['dt_server']);
					sendObjectSMSCommand($ed['user_id'], $imei, mysqli_real_escape_string($ms, $ed['event_desc']), $cmd);
				}
			}
		} else {
			if ($bloqueo != 1) {
				$q = "SELECT * FROM `gs_user_cmd` WHERE `protocol`='$device' AND `name` = 'Apagado de Motor' AND `gateway` = 'gprs'";
				$r = mysqli_query($ms, $q);
				if ($row = mysqli_fetch_array($r)) {
					$cmd = $row['cmd'];
					set_event_status($ed['event_id'], $loc['imei'], '1');
					set_send_status($ed['event_id'], $loc['imei'], 'bloqueo');
					sendObjectGPRSCommand($ed['user_id'], $imei, mysqli_real_escape_string($ms, $ed['event_desc']), $ed['cmd_type'], $cmd);
				}
			} else {
				$q = "SELECT * FROM `gs_user_cmd` WHERE `protocol`='$device' AND `name` = 'Encendido de Motor' AND `gateway` = 'gprs'";
				$r = mysqli_query($ms, $q);
				if ($row = mysqli_fetch_array($r)) {
					$cmd = $row['cmd'];
					set_event_status($ed['event_id'], $loc['imei'], '-1');
					set_send_status($ed['event_id'], $loc['imei'], 'desbloqueado');
					set_zone_entry_time($ms, $ed['event_id'], $loc['imei'], $loc['dt_server']);
					sendObjectGPRSCommand($ed['user_id'], $imei, mysqli_real_escape_string($ms, $ed['event_desc']), $ed['cmd_type'], $cmd);
				}
			}
		}
	}

	// send push notification
	if ($ed['notify_push'] == 'true') {
		// account
		$result = sendPushQueue($ud['push_notify_identifier'], 'event', '');

		// sub accounts
		$q = "SELECT * FROM `gs_users` WHERE `manager_id`='" . $ed['user_id'] . "' AND `privileges` LIKE ('%subuser%')";
		$r = mysqli_query($ms, $q);

		while ($row = mysqli_fetch_array($r)) {
			$privileges = json_decode($row['privileges'], true);
			if (!isset($privileges["imei"])) {
				continue;
			}
			$imeis = explode(",", $privileges["imei"]);
			if (in_array($imei, $imeis)) {
				$result = sendPushQueue($row['push_notify_identifier'], 'event', '');
			}
		}
	}

	if ($ed['notify_email'] === 'true' && !empty($ed['notify_email_address'])) {
		if (checkUserUsage($ed['user_id'], 'email')) {
			$email = $ed['notify_email_address'];
			$template = event_notify_template('email', $ed, $ud, $od, $loc);

			if (($ed['type'] ?? '') !== 'service' || empty($ed['mtto'])) {
				$result = sendEmailQueue($email, $template['subject'], $template['message'], true);
				if ($result) {
					updateUserUsage($ed['user_id'], false, $result, false, false, false);
				}
				// No hacer return aquí para permitir que continúen otros canales (Telegram, etc.)
			}

			if ($template && isset($template['message'])) {
				$tipo = $ed['mtto'];
				$diff = 0;
				$next = '';
				$label = '';
				$unidad = '';
				$vencido_label = '';
				$restante_label = '';
				$comentarios = trim($ed['details'] ?? '');

				switch ($tipo) {
					case 'horas':
						$diff = is_numeric($ed['engh_diff'] ?? null) ? $ed['engh_diff'] : 0;
						$next = is_numeric($ed['next_engh'] ?? null) ? number_format((float) $ed['next_engh'], 0) . ' hrs' : '';
						$label = number_format(abs((float) $diff), 0) . ' hrs';
						$unidad = 'Horas';
						break;
					case 'odometer':
						$diff = is_numeric($ed['odo_diff'] ?? null) ? $ed['odo_diff'] : 0;
						$next = is_numeric($ed['next_odo'] ?? null) ? number_format((float) $ed['next_odo'], 0) . ' Km' : '';
						$label = number_format(abs((float) $diff), 0) . ' Km';
						$unidad = 'Odometro';
						break;
					case 'dias':
						$diff = is_numeric($ed['days_diff'] ?? null) ? $ed['days_diff'] : 0;
						$next = $ed['next_date'] ?? '';
						$label = number_format(abs((float) $diff), 0) . ' Días';
						$unidad = 'Días';
						break;
				}

				if ($unidad !== '') {
					$vencido_label = "$unidad Vencido:";
					$restante_label = "$unidad Restante:";
					$linea_1 = ($diff < 0) ? "$unidad Vencido: $label" : "$unidad Restante: $label";
					$linea_2 = 'Siguiente Servicio: ' . $next;
					$comentarios = $comentarios !== '' ? 'Comentarios: "' . strip_tags($comentarios) . '"' : 'Comentarios: Sin datos';
					$bloque = $linea_1 . "\n" . $linea_2 . "\n" . $comentarios;

					if (strpos($template['message'], $vencido_label) !== false) {
						$template['message'] = str_replace($vencido_label, $bloque, $template['message']);
					} elseif (strpos($template['message'], $restante_label) !== false) {
						$template['message'] = str_replace($restante_label, $bloque, $template['message']);
					}
				}
			}

			$result = sendEmailQueue($email, $template['subject'], $template['message'], true);
			if ($result) {
				updateUserUsage($ed['user_id'], false, $result, false, false, false);
			}
		}
	}


	// send SMS notification
	if (($ed['notify_sms'] == 'true') && ($ed['notify_sms_number'] != '')) {
		if (checkUserUsage($ed['user_id'], 'sms')) {
			$result = false;

			$number = $ed['notify_sms_number'];

			$template = event_notify_template('sms', $ed, $ud, $od, $loc);

			if ($ud['sms_gateway'] == 'true') {
				if ($ud['sms_gateway_type'] == 'http') {
					$result = sendSMSHTTPQueue($ud['sms_gateway_url'], '', $number, $template['message']);
				} else if ($ud['sms_gateway_type'] == 'app') {
					$result = sendSMSAPP($ud['sms_gateway_identifier'], '', $number, $template['message']);
				}
			} else {
				if (($ud['sms_gateway_server'] == 'true') && ($gsValues['SMS_GATEWAY'] == 'true')) {
					if ($gsValues['SMS_GATEWAY_TYPE'] == 'http') {
						$result = sendSMSHTTPQueue($gsValues['SMS_GATEWAY_URL'], $gsValues['SMS_GATEWAY_NUMBER_FILTER'], $number, $template['message']);
					} else if ($gsValues['SMS_GATEWAY_TYPE'] == 'app') {
						$result = sendSMSAPP($gsValues['SMS_GATEWAY_IDENTIFIER'], $gsValues['SMS_GATEWAY_NUMBER_FILTER'], $number, $template['message']);
					}
				}
			}

			if ($result) {
				//update user usage
				updateUserUsage($ed['user_id'], false, false, $result, false, false);
			}
		}
	}
	//send notification to telegram
	if (!empty($ed['notify_telegram']) && $ed['notify_telegram'] === 'true') {
		$chat_ids_raw = !empty($ed['telegram_chat_id']) ? $ed['telegram_chat_id'] : '';

		if (!empty($chat_ids_raw)) {
			$template = event_notify_template('telegram', $ed, $ud, $od, $loc);
			$chat_ids = array_filter(array_map('trim', explode(',', $chat_ids_raw)));
			foreach ($chat_ids as $chat_id) {
				if ($chat_id !== '') {
					$tg_result = sendTelegramMessage($template['message'], $chat_id);
					addRowBinnacle($ed['user_id'], 'telegram_debug: respuesta API', 'chat_id=' . $chat_id . ' | response=' . $tg_result);
				}
			}
		}
	}
}

function event_notify_template($type, $ed, $ud, $od, $loc)
{
	global $ms, $la;

	// load language
	loadLanguage($ud["language"], $ud["units"]);

	// get template
	$template = array();
	$template['subject'] = '';
	$template['message'] = '';

	if ($type == 'email') {
		$template = getDefaultTemplate('event_email', $ud["language"]);
	} else if ($type == 'sms') {
		$template = getDefaultTemplate('event_sms', $ud["language"]);
	} else if ($type == 'telegram') {
		$template = getDefaultTemplate('event_telegram', $ud["language"]);
	}

	if ($ed[$type . '_template_id'] != 0) {
		$q = "SELECT * FROM `gs_user_templates` WHERE `template_id`='" . $ed[$type . '_template_id'] . "'";
		$r = mysqli_query($ms, $q);
		$row = mysqli_fetch_array($r);

		if ($row) {
			if ($row['subject'] != '') {
				$template['subject'] = $row['subject'];
			}

			if ($row['message'] != '') {
				$template['message'] = $row['message'];
			}
		}
	}

	// modify template variables
	$g_map = 'https://maps.google.com/maps?q=' . $loc['lat'] . ',' . $loc['lng'] . '&t=m';

	// add timezone to dt_tracker and dt_server
	$dt_server = convUserIDTimezone($ud['id'], $loc['dt_server']);
	$dt_tracker = convUserIDTimezone($ud['id'], $loc['dt_tracker']);

	$units = explode(",", $ud['units']);

	$speed = $loc['speed'];
	$speed = convSpeedUnits($speed, 'km', $units[0]);
	$speed = $speed . ' ' . $la["UNIT_SPEED"];

	$driver = getObjectDriver($ud['id'], $od['imei'], $loc['params']);

	$trailer = getObjectTrailer($ud['id'], $od['imei'], $loc['params']);

	// check if there is address variable
	if ((strpos($template['subject'], "%ADDRESS%") !== "") || (strpos($template['message'], "%ADDRESS%") !== "")) {
		$address = geocoderGetAddress($loc["lat"], $loc["lng"]);
	}

	// check odometer variable
	if ((strpos($template['subject'], "%ODOMETER%") !== "") || (strpos($template['message'], "%ODOMETER%") !== "")) {
		$odometer = getObjectOdometer($od['imei']);
		$odometer = floor(convDistanceUnits($odometer, 'km', $units[0]));
	}

	// check engine hours variable
	if ((strpos($template['subject'], "%ENG_HOURS%") !== "") || (strpos($template['message'], "%ENG_HOURS%") !== "")) {
		$eng_hours = getObjectEngineHours($od['imei'], false);
	}
	$driverName  = (is_array($driver)  && isset($driver['driver_name']))   ? $driver['driver_name']   : '';
	$trailerName = (is_array($trailer) && isset($trailer['trailer_name'])) ? $trailer['trailer_name'] : '';
	
	foreach ($template as $key => $value) {
		$value = str_replace("%NAME%", $od["name"], $value);
		$value = str_replace("%IMEI%", $od["imei"], $value);
		$value = str_replace("%EVENT%", $ed['event_desc'], $value);

		if (isset($ed['zone_name'])) {
			$value = str_replace("%ZONE%", $ed['zone_name'], $value);
		} else {
			$value = str_replace("%ZONE%", '', $value);
		}

		if (isset($ed['route_name'])) {
			$value = str_replace("%ROUTE%", $ed['route_name'], $value);
		} else {
			$value = str_replace("%ROUTE%", '', $value);
		}

		$value = str_replace("%LAT%", $loc["lat"], $value);
		$value = str_replace("%LNG%", $loc["lng"], $value);
		$value = str_replace("%ADDRESS%", $address, $value);
		$value = str_replace("%SPEED%", $speed, $value);
		$value = str_replace("%ALT%", $loc["altitude"], $value);
		$value = str_replace("%ANGLE%", $loc["angle"], $value);
		$value = str_replace("%DT_POS%", $dt_tracker, $value);
		$value = str_replace("%DT_SER%", $dt_server, $value);
		$value = str_replace("%G_MAP%", $g_map, $value);
		$value = str_replace("%TR_MODEL%", $od["model"], $value);
		$value = str_replace("%VIN%", $od["vin"], $value);
		$value = str_replace("%PL_NUM%", $od["plate_number"], $value);
		$value = str_replace("%SIM_NUMBER%", $od["sim_number"], $value);
		$value = str_replace("%DRIVER%", $driverName, $value);
		$value = str_replace("%TRAILER%", $trailerName, $value);
		$value = str_replace("%ODOMETER%", $odometer, $value);
		$value = str_replace("%ENG_HOURS%", $eng_hours, $value);

		$template[$key] = $value;
	}

	return $template;
}

function get_event_status($event_id, $imei)
{
	global $ms;

	$result = '-1';

	$q = "SELECT * FROM `gs_user_events_status` WHERE `event_id`='" . $event_id . "' AND `imei`='" . $imei . "'";
	$r = mysqli_query($ms, $q);
	$row = mysqli_fetch_array($r);

	if ($row) {
		$result = $row['event_status'];
	} else {
		$q = "INSERT INTO `gs_user_events_status` (`event_id`,`imei`,`event_status`) VALUES ('" . $event_id . "','" . $imei . "','-1')";
		$r = mysqli_query($ms, $q);
	}

	return $result;
}

function getDmsAlert($imei, $value)
{
	global $ms;

	$q = "SELECT 1 FROM `gs_object_alertas_dms` 
          WHERE `alerta`='" . mysqli_real_escape_string($ms, $value) . "' 
          AND `imei`='" . mysqli_real_escape_string($ms, $imei) . "' 
          LIMIT 1";

	$r = mysqli_query($ms, $q);

	if (mysqli_num_rows($r) > 0) {

		$q = "DELETE FROM `gs_object_alertas_dms` 
              WHERE `alerta`='" . mysqli_real_escape_string($ms, $value) . "' 
              AND `imei`='" . mysqli_real_escape_string($ms, $imei) . "'";

		if (mysqli_query($ms, $q)) {
			return 'true';
		}
	}

	return 'false';
}


function set_event_status($event_id, $imei, $value)
{
	global $ms;

	$q = "UPDATE `gs_user_events_status` SET `event_status`='" . $value . "' WHERE `event_id`='" . $event_id . "' AND `imei`='" . $imei . "'";
	$r = mysqli_query($ms, $q);
}

function check_event_duration_from_last($ed, $imei)
{
	global $ms;

	$q = "SELECT * FROM `gs_user_events_status` WHERE `event_id`='" . $ed['event_id'] . "' AND `imei`='" . $imei . "'";
	$r = mysqli_query($ms, $q);
	$row = mysqli_fetch_array($r);

	if ($row) {
		if ($ed['duration_from_last_event'] == 'true') {
			if (strtotime($row['dt_server']) >= strtotime(gmdate("Y-m-d H:i:s") . " - " . $ed['duration_from_last_event_minutes'] . " minutes")) {
				return false;
			}
		}
	}

	return true;
}

function check_event_week_days($dt_check, $week_days)
{
	$day_of_week = gmdate('w', strtotime($dt_check));
	$week_days = explode(',', $week_days);

	if ($week_days[$day_of_week] == 'true') {
		return true;
	} else {
		return false;
	}
}

function check_event_day_time($dt_check, $day_time)
{
	$day_of_week = gmdate('w', strtotime($dt_check));
	$day_time = json_decode($day_time, true);

	if ($day_time != null) {
		if ($day_time['dt'] == true) {
			if (($day_time['sun'] == true) && ($day_of_week == 0)) {
				$from = $day_time['sun_from'];
				$to = $day_time['sun_to'];
			} else if (($day_time['mon'] == true) && ($day_of_week == 1)) {
				$from = $day_time['mon_from'];
				$to = $day_time['mon_to'];
			} else if (($day_time['tue'] == true) && ($day_of_week == 2)) {
				$from = $day_time['tue_from'];
				$to = $day_time['tue_to'];
			} else if (($day_time['wed'] == true) && ($day_of_week == 3)) {
				$from = $day_time['wed_from'];
				$to = $day_time['wed_to'];
			} else if (($day_time['thu'] == true) && ($day_of_week == 4)) {
				$from = $day_time['thu_from'];
				$to = $day_time['thu_to'];
			} else if (($day_time['fri'] == true) && ($day_of_week == 5)) {
				$from = $day_time['fri_from'];
				$to = $day_time['fri_to'];
			} else if (($day_time['sat'] == true) && ($day_of_week == 6)) {
				$from = $day_time['sat_from'];
				$to = $day_time['sat_to'];
			} else {
				return false;
			}

			if (isset($from) && isset($to)) {
				$dt_check = strtotime(date("H:i", strtotime($dt_check)));
				$from = strtotime($from);
				$to = strtotime($to);

				// add one day offset
				if ($from > $to) {
					$to = $to + 86400;
				}

				if (($from < $dt_check) && ($to > $dt_check)) {
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		} else {
			return true;
		}
	} else {
		return true;
	}
}

function check_event_route_trigger($ed, $ud, $loc)
{
	global $ms;

	$user_id = $ed['user_id'];
	$route_trigger = $ed['route_trigger'];
	$routes = $ed['routes'];
	$lat = $loc['lat'];
	$lng = $loc['lng'];

	if (($route_trigger == '') || ($route_trigger == 'off')) {
		return $ed;
	}

	if ($route_trigger == 'in') {
		$q = "SELECT * FROM `gs_user_routes` WHERE `user_id`='" . $user_id . "' AND `route_id` IN (" . $routes . ")";
		$r = mysqli_query($ms, $q);

		if (!$r) {
			return false;
		}

		while ($route = mysqli_fetch_array($r)) {
			$dist = isPointOnLine($route['route_points'], $loc['lat'], $loc['lng']);

			// get user units and convert if needed
			$units = explode(",", $ud['units']);
			$dist = convDistanceUnits($dist, 'km', $units[0]);

			if ($dist <= $route['route_deviation']) {
				// add route name
				$ed['route_name'] = $route['route_name'];
				return $ed;
			}
		}
	}

	if ($route_trigger == 'out') {
		$q = "SELECT * FROM `gs_user_routes` WHERE `user_id`='" . $user_id . "' AND `route_id` IN (" . $routes . ")";
		$r = mysqli_query($ms, $q);

		if (!$r) {
			return false;
		}

		$in_routes = false;

		while ($route = mysqli_fetch_array($r)) {
			$dist = isPointOnLine($route['route_points'], $loc['lat'], $loc['lng']);

			// get user units and convert if needed
			$units = explode(",", $ud['units']);
			$dist = convDistanceUnits($dist, 'km', $units[0]);

			if ($dist <= $route['route_deviation']) {
				$in_routes = true;
				break;
			}
		}

		if ($in_routes == false) {
			return $ed;
		}
	}

	return false;
}

function check_event_zone_trigger($ed, $ud, $loc)
{
	global $ms;

	$user_id = $ed['user_id'];
	$zone_trigger = $ed['zone_trigger'];
	$zones = $ed['zones'];
	$lat = $loc['lat'];
	$lng = $loc['lng'];

	if (($zone_trigger == '') || ($zone_trigger == 'off')) {
		return $ed;
	}

	if ($zone_trigger == 'in') {
		$q = "SELECT * FROM `gs_user_zones` WHERE `user_id`='" . $user_id . "' AND `zone_id` IN (" . $zones . ")";
		$r = mysqli_query($ms, $q);

		if (!$r) {
			return false;
		}

		while ($zone = mysqli_fetch_array($r)) {
			$in_zone = isPointInPolygon($zone['zone_vertices'], $lat, $lng);

			if ($in_zone) {
				// add zone name
				$ed['zone_name'] = $zone['zone_name'];
				return $ed;
			}
		}
	}

	if ($zone_trigger == 'out') {
		$q = "SELECT * FROM `gs_user_zones` WHERE `user_id`='" . $user_id . "' AND `zone_id` IN (" . $zones . ")";
		$r = mysqli_query($ms, $q);

		if (!$r) {
			return false;
		}

		$in_zones = false;

		while ($zone = mysqli_fetch_array($r)) {
			$in_zone = isPointInPolygon($zone['zone_vertices'], $lat, $lng);

			if ($in_zone) {
				$in_zones = true;
				break;
			}
		}

		if ($in_zones == false) {
			return $ed;
		}
	}

	return false;
}

function getJarvisApiToken()
{
	$cacheFile = sys_get_temp_dir() . '/jarvis_api_token.json';

	// Si existe un token en caché y es válido (con margen de 60 segundos)
	if (file_exists($cacheFile)) {
		$cache = json_decode(file_get_contents($cacheFile), true);
		if ($cache && isset($cache['access_token']) && isset($cache['expires_at'])) {
			if (time() < ($cache['expires_at'] - 60)) {
				return $cache['access_token'];
			}
		}
	}

	$apiUrl = getenv('JARVIS_API_URL');
	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => $apiUrl . '/auth/token',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => 'grant_type=password&username=' . getenv('USER_JARVIS_SEND_MESSAGE_TO_TELEGRAM') . '&password=' . getenv('PASS_JARVIS_SEND_MESSAGE_TO_TELEGRAM') . '&scope=&client_id=string&client_secret=string',
		CURLOPT_HTTPHEADER => array(
			'accept: application/json',
			'Content-Type: application/x-www-form-urlencoded'
		),
	));

	$response = curl_exec($curl);
	curl_close($curl);

	if ($response) {
		$data = json_decode($response, true);
		if (isset($data['access_token'])) {
			$expiresIn = isset($data['expires_in']) ? (int) $data['expires_in'] : 1800;
			$data['expires_at'] = time() + $expiresIn;

			// Guardar token y expiración
			file_put_contents($cacheFile, json_encode($data));

			return $data['access_token'];
		}
	}

	return null;
}

function sendTelegramMessage($message, $chat_id)
	{
		$bot_token = '8483933785:AAFb_9tgPLiWrqCMUdUAezDoL9qxFx_aCGs';
		$url = "https://api.telegram.org/bot" . $bot_token . "/sendMessage";
		$data = [
			'chat_id' => $chat_id,
			'text' => $message,
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