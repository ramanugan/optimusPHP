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

if (@$_POST['cmd'] == 'load_tasks_nearest_zone') {
    // check privileges
    if ($_SESSION["privileges"] == 'subuser') {
        $sub_imeis = array_map(function ($imei) {
            if (preg_match('/"(\d+)_\d+"/', $imei, $result)) {
                return '"' . $result[1] . '"';
            } else {
                return $imei;
            }
        }, explode(',', $_SESSION["privileges_imei"]));
        $q = "SELECT now() as fecha, gs_object_tasks.imei_truck_tractor,  gs_objects.lat, gs_objects.lng,
            gs_object_tasks.task_id as id_tarea_programada, (select zone_vertices from gs_user_zones where zone_id = gs_object_tasks.initial_zone) as initial_zone, (select zone_vertices from gs_user_zones where zone_id = gs_object_tasks.ended_zone)  as ended_zone,
            (select zone_name from gs_user_zones where zone_id = gs_object_tasks.initial_zone) as zone_name_start, (select zone_name from gs_user_zones where zone_id = gs_object_tasks.ended_zone)  as zone_name_end,
            gs_objects.dt_server, gs_objects.dt_tracker,
            gs_objects.name as name_gps,
            gs_object_tasks.journey_name, gs_object_tasks.status, 
            gs_object_tasks.start_from_dt, gs_object_tasks.start_to_dt, gs_object_tasks.end_from_dt, gs_object_tasks.end_to_dt  
            FROM `gs_user_objects` 
            INNER JOIN gs_objects ON gs_user_objects.imei = gs_objects.imei
            INNER JOIN gs_object_tasks ON gs_objects.imei = gs_object_tasks.imei_truck_tractor 
            WHERE `user_id`='" . $user_id . "'
            AND gs_object_tasks.imei_truck_tractor IN (" . implode(',', $sub_imeis) . ")
            AND STR_TO_DATE( gs_object_tasks.start_from_dt, '%Y-%m-%d %T' ) > DATE_SUB(NOW(), INTERVAL 1 DAY)";
    } else {
        $q = "SELECT now() as fecha, gs_object_tasks.imei_truck_tractor,  gs_objects.lat, gs_objects.lng,
            gs_object_tasks.task_id as id_tarea_programada, (select zone_vertices from gs_user_zones where zone_id = gs_object_tasks.initial_zone) as initial_zone, (select zone_vertices from gs_user_zones where zone_id = gs_object_tasks.ended_zone)  as ended_zone,
            (select zone_name from gs_user_zones where zone_id = gs_object_tasks.initial_zone) as zone_name_start, (select zone_name from gs_user_zones where zone_id = gs_object_tasks.ended_zone)  as zone_name_end,
            gs_objects.dt_server, gs_objects.dt_tracker,
            gs_objects.name as name_gps,
            gs_object_tasks.journey_name, gs_object_tasks.status,
            gs_object_tasks.start_from_dt, gs_object_tasks.start_to_dt, gs_object_tasks.end_from_dt, gs_object_tasks.end_to_dt  
            FROM `gs_user_objects` 
            INNER JOIN gs_objects ON gs_user_objects.imei = gs_objects.imei
            INNER JOIN gs_object_tasks ON gs_objects.imei = gs_object_tasks.imei_truck_tractor 
            WHERE `user_id`='" . $user_id . "'
            AND STR_TO_DATE( gs_object_tasks.start_from_dt, '%Y-%m-%d %T' ) > DATE_SUB(NOW(), INTERVAL 1 DAY)";
    }


    $r = mysqli_query($ms, $q);

    $result = array();

    while ($row = mysqli_fetch_array($r)) {

        $result[$row['imei_truck_tractor']] = array();
        $result[$row['imei_truck_tractor']]['current_dt'] = $row['fecha'];
        $result[$row['imei_truck_tractor']]['lat'] = $row['lat'];
        $result[$row['imei_truck_tractor']]['lng'] = $row['lng'];
        $result[$row['imei_truck_tractor']]['id'] = $row['id_tarea_programada'];
        $result[$row['imei_truck_tractor']]['initial_zone'] = $row['initial_zone'];
        $result[$row['imei_truck_tractor']]['ended_zone'] = $row['ended_zone'];
        $result[$row['imei_truck_tractor']]['start_from_dt'] = $row['start_from_dt'];
        $result[$row['imei_truck_tractor']]['start_to_dt'] = $row['start_to_dt'];
        $result[$row['imei_truck_tractor']]['end_from_dt'] = $row['end_from_dt'];
        $result[$row['imei_truck_tractor']]['end_to_dt'] = $row['end_to_dt'];
        $result[$row['imei_truck_tractor']]['journey_name'] = $row['journey_name'];
        $result[$row['imei_truck_tractor']]['status'] = $row['status'];
        $result[$row['imei_truck_tractor']]['zone_name_start'] = $row['zone_name_start'];
        $result[$row['imei_truck_tractor']]['zone_name_end'] = $row['zone_name_end'];
        $result[$row['imei_truck_tractor']]['name_gps'] = $row['name_gps'];
        $result[$row['imei_truck_tractor']]['dt_server'] = $row['dt_server'];
        $result[$row['imei_truck_tractor']]['dt_tracker'] = $row['dt_tracker'];
    }

    mysqli_close($ms);
    ob_start();
    header('Content-type: application/json');
    echo json_encode($result);
    header("Connection: close");
    header("Content-length: " . (string)ob_get_length());
    ob_end_flush();
    die;
}

if (@$_POST['cmd'] == 'load_task') {
    $task_id = $_POST['task_id'];

    $q = "SELECT * FROM `gs_object_tasks` WHERE `task_id`='" . $task_id . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    $result = array(
        'journey_name' => $row['journey_name'],
        'driver_name' => $row['driver_name'],
        'imei_truck_tractor' => $row['imei_truck_tractor'],
        'imei_trailer_1' => $row['imei_trailer_1'],
        'dolly' => $row['dolly'],
        'imei_trailer_2' => $row['imei_trailer_2'],
        'desc' => $row['desc'],
        'priority' => $row['priority'],
        'status' => $row['status'],
        'carta_porte' => strlen($row['carta_porte']) > 0 ? $row['carta_porte'] : null,
        'doc1' => strlen($row['doc1']) > 0 ? $row['doc1'] : null,
        'doc2' => strlen($row['doc2']) > 0 ? $row['doc2'] : null,
        'doc3' => strlen($row['doc3']) > 0 ? $row['doc3'] : null,
        'initial_zone' => $row['initial_zone'],
        'start_from_dt' => $row['start_from_dt'],
        'start_to_dt' => $row['start_to_dt'],
        'ended_zone' => $row['ended_zone'],
        'end_from_dt' => $row['end_from_dt'],
        'end_to_dt' => $row['end_to_dt']
    );

    echo json_encode($result);
    die;
}

if (@$_POST['cmd'] == 'save_task') {
    $task_id = $_POST["task_id"];
    $journey_name = $_POST['dialog_task_journey_name'];
    $driver_name = $_POST['dialog_task_driver_name'];
    $imei_truck_tractor = $_POST['dialog_task_truck_tractor'];
    $imei_trailer_1 = $_POST['dialog_task_trailer_1'];
    $dolly = $_POST['dialog_task_dolly'];
    $imei_trailer_2 = $_POST['dialog_task_trailer_2'];
    $desc = $_POST['dialog_task_desc'];
    $priority = $_POST['dialog_task_priority'];
    $status = $_POST['dialog_task_status'];
    $carta_porte = $_POST['dialog_task_carta_porte'];
    $doc1 = $_POST['dialog_task_doc1'];
    $doc2 = $_POST['dialog_task_doc2'];
    $doc3 = $_POST['dialog_task_doc3'];
    $initial_zone = $_POST['dialog_task_initial_zone'];
    $start_from_date = $_POST['dialog_task_start_from_date'];
    $start_to_date = $_POST['dialog_task_start_to_date'];
    $ended_zone = $_POST['dialog_task_ended_zone'];
    $end_from_date = $_POST['dialog_task_end_from_date'];
    $end_to_date = $_POST['dialog_task_end_to_date'];

    if ($task_id == 'false') {

        $qryUserEvents = "INSERT INTO gs_user_events (user_id,type,name,active,duration_from_last_event,duration_from_last_event_minutes,week_days,day_time,imei,sub_accounts,checked_value,route_trigger,zone_trigger,routes,zones,notify_system,notify_push,notify_email,notify_email_address,notify_sms,notify_sms_number,email_template_id,sms_template_id,notify_arrow,notify_arrow_color,notify_ohc,notify_ohc_color,webhook_send,webhook_url,cmd_send,cmd_gateway,cmd_type,cmd_string,maintenance_id)
	    VALUES ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s') ";


        $valores = [
            $user_id,
            'task',
            $journey_name,
            'true',
            'false',
            0,
            'true,true,true,true,true,true,true',
            '{dt:false,mon:false,mon_from:00:00,mon_to:24:00,tue:false,tue_from:00:00,tue_to:24:00,wed:false,wed_from:00:00,wed_to:24:00,thu:false,thu_from:00:00,thu_to:24:00,fri:false,fri_from:00:00,fri_to:24:00,sat:false,sat_from:00:00,sat_to:24:00,sun:false,sun_from:00:00,sun_to:24:00}',
            $imei_truck_tractor,
            '',
            '',
            'off',
            'off',
            '',
            '',
            'true,false,true,alarm1.mp3',
            'false',
            'false',
            '',
            'false',
            '',
            'false',
            '',
            'true',
            'arrow_purple',
            'true',
            '#FF002B',
            'false',
            '',
            'false',
            'gprs',
            'ascii',
            '',
            ''
        ];

        $q = "INSERT INTO `gs_object_tasks`(    
                          `dt_task`,
                                                    `journey_name`,
                                                    `driver_name`,
                                                    `imei_truck_tractor`,
                                                    `imei_trailer_1`,
                                                    `dolly`,
                                                    `imei_trailer_2`,
                                                    `desc`,
                                                    `priority`,
                                                    `status`,
                                                    `carta_porte`,
                                                    `doc1`,
                                                    `doc2`,
                                                    `doc3`,
                                                    `initial_zone`,
                                                    `start_from_dt`,
                                                    `start_to_dt`,
                                                    `ended_zone`,
                                                    `end_from_dt`,
                                                    `end_to_dt`)
                                              VALUES
                                                    ('" . gmdate("Y-m-d H:i:s") . "',
                                                    '" . $journey_name . "',
                                                    '" . $driver_name . "',
                                                    '" . $imei_truck_tractor . "',
                                                    '" . $imei_trailer_1 . "',
                                                    '" . $dolly . "',
                                                    '" . $imei_trailer_2 . "',
                                                    '" . $desc . "',
                                                    '" . $priority . "',
                                                    '" . $status . "',
                                                    '" . $carta_porte . "',
                                                    '" . $doc1 . "',
                                                    '" . $doc2 . "',
                                                    '" . $doc3 . "',
                                                    '" . $initial_zone . "',
                                                    '" . $start_from_date . "',
                                                    '" . $start_to_date . "',
                                                    '" . $ended_zone . "',
                                                    '" . $end_from_date . "',
                                                    '" . $end_to_date . "')";
        $q1s = vsprintf($qryUserEvents, $valores);
        mysqli_query($ms, $q1s);
        $event_id = mysqli_insert_id($ms);
        $r = mysqli_query($ms, $q);
        $last_id = mysqli_insert_id($ms);

        $file_carta_porte = preg_replace('/\_\d+/', '_id_' . $last_id, $carta_porte);
        $file_doc1 = preg_replace('/\_\d{2,}/', '_id_' . $last_id, $doc1);
        $file_doc2 = preg_replace('/\_\d{2,}/', '_id_' . $last_id, $doc2);
        $file_doc3 = preg_replace('/\_\d{2,}/', '_id_' . $last_id, $doc3);
        $q1 = "UPDATE gs_object_tasks SET carta_porte='" . $file_carta_porte . "', doc1='" . $file_doc1 . "' , doc2='" . $file_doc2 . "', doc3='" . $file_doc3 . "' WHERE task_id =" . $last_id;
        mysqli_query($ms, $q1);

        rename($gsValues['PATH_ROOT'] . 'data/user/viaje_programado/' . $carta_porte, $gsValues['PATH_ROOT'] . 'data/user/viaje_programado/' . $file_carta_porte);
        rename($gsValues['PATH_ROOT'] . 'data/user/viaje_programado/' . $doc1, $gsValues['PATH_ROOT'] . 'data/user/viaje_programado/' . $file_doc1);
        rename($gsValues['PATH_ROOT'] . 'data/user/viaje_programado/' . $doc2, $gsValues['PATH_ROOT'] . 'data/user/viaje_programado/' . $file_doc2);
        rename($gsValues['PATH_ROOT'] . 'data/user/viaje_programado/' . $doc3, $gsValues['PATH_ROOT'] . 'data/user/viaje_programado/' . $file_doc3);

        $q2 = "UPDATE gs_user_events SET maintenance_id = '" . $last_id . "' WHERE `event_id`='" . $event_id . "'";
        $r2 = mysqli_query($ms, $q2);
    } else {
        $q = "SELECT carta_porte,doc1, doc2, doc3 FROM gs_object_tasks WHERE task_id='" . $task_id . "'";
        $r = mysqli_query($ms, $q);
        $row = mysqli_fetch_assoc($r);
        $file_carta_porte = $row['carta_porte'];
        $file_doc1 = $row['doc1'];
        $file_doc2 = $row['doc2'];
        $file_doc3 = $row['doc3'];
        if ($file_carta_porte !== $carta_porte) {
            $file_carta_porte = preg_replace('/\_\d+/', '_id_' . $task_id, $carta_porte);
            rename($gsValues['PATH_ROOT'] . 'data/user/viaje_programado/' . $carta_porte, $gsValues['PATH_ROOT'] . 'data/user/viaje_programado/' . $file_carta_porte);
        }

        if ($file_doc1 !== $doc1) {
            $file_doc1 = preg_replace('/\_\d{2,}/', '_id_' . $task_id, $doc1);
            rename($gsValues['PATH_ROOT'] . 'data/user/viaje_programado/' . $doc1, $gsValues['PATH_ROOT'] . 'data/user/viaje_programado/' . $file_doc1);
        }

        if ($file_doc2 !== $doc2) {
            $file_doc2 = preg_replace('/\_\d{2,}/', '_id_' . $task_id, $doc2);
            rename($gsValues['PATH_ROOT'] . 'data/user/viaje_programado/' . $doc2, $gsValues['PATH_ROOT'] . 'data/user/viaje_programado/' . $file_doc2);
        }

        if ($file_doc3 !== $doc3) {
            $file_doc3 = preg_replace('/\_\d{2,}/', '_id_' . $task_id, $doc3);
            rename($gsValues['PATH_ROOT'] . 'data/user/viaje_programado/' . $doc3, $gsValues['PATH_ROOT'] . 'data/user/viaje_programado/' . $file_doc3);
        }

        $q = "UPDATE `gs_object_tasks` SET
            `journey_name`='" . $journey_name . "',
            `driver_name`='" . $driver_name . "',
            `imei_truck_tractor`='" . $imei_truck_tractor . "',
            `imei_trailer_1`='" . $imei_trailer_1 . "',
            `dolly`='" . $dolly . "',
            `imei_trailer_2`='" . $imei_trailer_2 . "',
            `status`='" . $status . "',
            `carta_porte`='" . $file_carta_porte . "',
            `doc1`='" . $file_doc1 . "',
            `doc2`='" . $file_doc2 . "',
            `doc3`='" . $file_doc3 . "',
            `initial_zone`='" . $initial_zone . "',
            `start_from_dt`='" . $start_from_date . "',
            `start_to_dt`='" . $start_to_date . "',
            `ended_zone`='" . $ended_zone . "',
            `end_from_dt`='" . $end_from_date . "',
            `end_to_dt`='" . $end_to_date . "'
            WHERE `task_id`='" . $task_id . "';

            UPDATE `gs_user_events` SET
            `type`='connyes',
            `name`='" . $journey_name . "',
            `active`='true'
            WHERE `maintenance_id`='" . $task_id . "'";
        $r = mysqli_multi_query($ms, $q);
    }

    echo 'OK';
    die;
}

if (@$_GET['cmd'] == 'load_task_list') {
    $imei = @$_GET['imei'];

    $page = $_GET['page']; // get the requested page
    $limit = $_GET['rows']; // get how many rows we want to have into the grid
    $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
    $sord = $_GET['sord']; // get the direction

    if (!$sidx) $sidx = 1;

    // get records number
    if ($_SESSION["privileges"] == 'subuser') {
        $q = "SELECT * FROM `gs_object_tasks` WHERE `imei_truck_tractor` IN (" . $_SESSION["privileges_imei"] . ")";
    } else {
        $q = "SELECT * FROM `gs_object_tasks` WHERE `imei_truck_tractor` IN (" . getUserObjectIMEIs($user_id) . ")";
    }

    if (isset($imei)) {
        $q .= ' AND `imei_truck_tractor`="' . $imei . '"';
    }

    if (isset($_GET['dtf']) && isset($_GET['dtt'])) {
        $q .= " AND start_from_dt BETWEEN '" . convUserUTCTimezone($_GET['dtf']) . "' AND '" . convUserUTCTimezone($_GET['dtt']) . "'";
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
        $q = "SELECT * FROM `gs_object_tasks` WHERE `imei_truck_tractor` IN (" . $_SESSION["privileges_imei"] . ")";
    } else {
        $q = "SELECT * FROM `gs_object_tasks` WHERE `imei_truck_tractor` IN (" . getUserObjectIMEIs($user_id) . ")";
    }

    if (isset($imei)) {
        $q .= ' AND `imei_truck_tractor`="' . $imei . '"';
    }

    if (isset($_GET['dtf']) && isset($_GET['dtt'])) {
        $q .= " AND start_from_dt BETWEEN '" . convUserUTCTimezone($_GET['dtf']) . "' AND '" . convUserUTCTimezone($_GET['dtt']) . "'";
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
            if (checkObjectActive($row['imei_truck_tractor']) == true) {
                $task_id = $row['task_id'];
                $start_from_dt = convUserTimezone($row['start_from_dt']);
                $date = new DateTime($start_from_dt);
                $date->modify('+6 hours');
                $start_from_dt = $date->format('Y-m-d H:i:s');
                $journey_name = $row['journey_name'];
                $imei_truck_tractor = $row['imei_truck_tractor'];
                $initial_zone = getZoneName($row["initial_zone"]);
                $ended_zone = getZoneName($row["ended_zone"]);
                $priority = $row["priority"];
                $status = $row["status"];

                $object_name = getObjectName($imei_truck_tractor);
                if ($priority == 'low') {
                    $priority = $la['LOW'];
                } else if ($priority == 'normal') {
                    $priority = $la['NORMAL'];
                } else if ($priority == 'high') {
                    $priority = $la['HIGH'];
                }

                if ($status == 0) {
                    $status = $la['NEW'];
                } else if ($status == 1) {
                    $status = $la['IN_PROGRESS'];
                } else if ($status == 2) {
                    $status = $la['COMPLETED'];
                } else if ($status == 3) {
                    $status = $la['WITH_DELAY_1'];
                } else if ($status == 4) {
                    $status = $la['WITH_DELAY_2'];
                } else if ($status == 5) {
                    $status = $la['FAILED'];
                } else if ($status == 6) {
                    $status = $la['WITH_DELAY_4'];
                } else if ($status == 7) {
                    $status = $la['WITH_DELAY_7'];
                }

                // set modify buttons
                $modify = '<a href="#" onclick="taskProperties(\'' . $task_id . '\');" title="' . $la['EDIT'] . '"><img src="theme/images/edit.svg" />';
                $modify .= '<a href="#" onclick="taskExport(\'' . $task_id . '\');" title="' . $la['EXPORT'] . '"><img src="theme/images/export.svg" />';
                $modify .= '</a><a href="#" onclick="tasksDelete(\'' . $task_id . '\');" title="' . $la['DELETE'] . '"><img src="theme/images/remove3.svg" /></a>';


                // set row
                $response->rows[$i]['id'] = $task_id;
                $response->rows[$i]['cell'] = array($start_from_dt, $journey_name, $object_name, $initial_zone, $ended_zone, $priority, $status, $modify);
                $i++;
            }
        }
    }

    header('Content-type: application/json');
    echo json_encode($response);
    die;
}

if (@$_POST['cmd'] == 'delete_task') {

    if ($_SESSION["manager_id"] > 0) {
        $user_id = $_SESSION["manager_id"];
    } else {
        $user_id = $_SESSION["user_id"];
    }
    $task_id = $_POST["task_id"];

    $q0 = "SELECT event_id, user_id, type,name from  `gs_user_events` WHERE `maintenance_id`='" . $task_id . "'";
    $r = mysqli_query($ms, $q0);
    $row = mysqli_fetch_assoc($r);

    $event_id = $row['event_id'];
    $user_id = $row['user_id'];

    $q = "DELETE FROM `gs_user_events` WHERE `event_id`='" . $event_id . "' AND `user_id`='" . $user_id . "'";
    $r = mysqli_query($ms, $q);

    $q2 = "DELETE FROM `gs_user_events_status` WHERE `event_id`='" . $event_id . "'";
    $r = mysqli_query($ms, $q2);

    $task_id = $_POST["task_id"];

    $q = "DELETE FROM `gs_object_tasks` WHERE `task_id`='" . $task_id . "'";
    $r = mysqli_query($ms, $q);

    addRowBinnacle($_SESSION["user_id"], 'Eliminacion de tarea y evento', $event_dropped);
    echo 'OK';
    die;
}

if (@$_POST['cmd'] == 'delete_selected_tasks') {
    $items = $_POST["items"];

    for ($i = 0; $i < count($items); ++$i) {
        $item = $items[$i];

        $q = "DELETE FROM `gs_object_tasks` WHERE `task_id`='" . $item . "'";
        $r = mysqli_query($ms, $q);
    }

    echo 'OK';
    die;
}

if (@$_POST['cmd'] == 'delete_all_tasks') {
    if ($_SESSION["privileges"] == 'subuser') {
        $q = "DELETE FROM `gs_object_tasks` WHERE `imei` IN (" . $_SESSION["privileges_imei"] . ")";
    } else {
        $q = "DELETE FROM `gs_object_tasks` WHERE `imei` IN (" . getUserObjectIMEIs($user_id) . ")";
    }

    $r = mysqli_query($ms, $q);

    echo 'OK';
    die;
}

if (@$_POST['cmd'] == 'update_status') {
    $task_id = $_POST["task_id"];
    $status = $_POST['status'];
    $dt_server = $_POST['dt_server'];
    $dt_tracker = $_POST['dt_tracker'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $imei = $_POST['imei'];
    $name_gps = $_POST['name_gps'];

    $q = "UPDATE gs_object_tasks SET status = '" . $status . "' WHERE task_id =" . $task_id;
    mysqli_query($ms, $q);

    $q = "SELECT * FROM gs_object_data_" . $imei . " WHERE dt_server ='" . $dt_server . "' and dt_tracker ='" . $dt_tracker . "' and lat ='" . $lat . "' and lng ='" . $lng . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);


    $qry = "INSERT INTO gs_user_last_events_data (  
                                user_id,
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
                                params
                                ) VALUES (
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s' )";
    // save new event in database
    if ($status == "1") {
        $values = [
            $user_id,
            'zone_out',
            'Viaje programado ha iniciado.',
            'true,true,true,alarm1.mp3',
            'false',
            'false',
            'arrow_yellow',
            'false',
            '#FFFF00',
            $imei,
            $name_gps,
            $row['dt_server'],
            $row['dt_tracker'],
            $row['lat'],
            $row['lng'],
            $row['altitude'],
            $row['angle'],
            $row['speed'],
            $row['params']
        ];
    } else if ($status == "2") {
        $values = [
            $user_id,
            'zone_in',
            'Viaje programado ha terminado.',
            'true,true,true,alarm1.mp3',
            'false',
            'false',
            'arrow_yellow',
            'false',
            '#FFFF00',
            $imei,
            $name_gps,
            $row['dt_server'],
            $row['dt_tracker'],
            $row['lat'],
            $row['lng'],
            $row['altitude'],
            $row['angle'],
            $row['speed'],
            $row['params']
        ];
    } else if ($status == "3") {
        $values = [
            $user_id,
            'zone_out',
            'Viaje programado ha iniciado con retrazo.',
            'true,true,true,alarm1.mp3',
            'false',
            'false',
            'arrow_yellow',
            'false',
            '#FFFF00',
            $imei,
            $name_gps,
            $row['dt_server'],
            $row['dt_tracker'],
            $row['lat'],
            $row['lng'],
            $row['altitude'],
            $row['angle'],
            $row['speed'],
            $row['params']
        ];
    } else if ($status == "4") {
        $values = [
            $user_id,
            'zone_in',
            'Viaje programado ha terminado con retrazo.',
            'true,true,true,alarm1.mp3',
            'false',
            'false',
            'arrow_yellow',
            'false',
            '#FFFF00',
            $imei,
            $name_gps,
            $row['dt_server'],
            $row['dt_tracker'],
            $row['lat'],
            $row['lng'],
            $row['altitude'],
            $row['angle'],
            $row['speed'],
            $row['params']
        ];
    } else if ($status == "5") {
        $values = [
            $user_id,
            'zone_out',
            'Viaje programado ha iniciado con 30min de retrazo',
            'true,true,true,alarm1.mp3',
            'false',
            'false',
            'arrow_yellow',
            'false',
            '#FFFF00',
            $imei,
            $name_gps,
            $row['dt_server'],
            $row['dt_tracker'],
            $row['lat'],
            $row['lng'],
            $row['altitude'],
            $row['angle'],
            $row['speed'],
            $row['params']
        ];
    } else if ($status == "6") {
        $values = [
            $user_id,
            'zone_in',
            'Viaje programado ha terminado de 30min',
            'true,true,true,alarm1.mp3',
            'false',
            'false',
            'arrow_yellow',
            'false',
            '#FFFF00',
            $imei,
            $name_gps,
            $row['dt_server'],
            $row['dt_tracker'],
            $row['lat'],
            $row['lng'],
            $row['altitude'],
            $row['angle'],
            $row['speed'],
            $row['params']
        ];
    } else if ($status == "7") {
        $values = [
            $user_id,
            'zone_out',
            'Viaje programado ha terminado, unidad nunca comenzo el viaje.',
            'true,true,true,alarm1.mp3',
            'false',
            'false',
            'arrow_yellow',
            'false',
            '#FFFF00',
            $imei,
            $name_gps,
            $row['dt_server'],
            $row['dt_tracker'],
            $row['lat'],
            $row['lng'],
            $row['altitude'],
            $row['angle'],
            $row['speed'],
            $row['params']
        ];
    }
    $q = vsprintf($qry, $values);
    mysqli_query($ms, $q);
    echo 'OK';
    die;
}
