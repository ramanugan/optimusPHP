<?
//$_POST['net_protocol'] - tcp or udp
//$_POST['protocol'] - device protocol, like coban, teltonika, xexun
//$_POST['ip'] - IP address of GPS device
//$_POST['port'] - PORT of GPS device
//$_POST['imei'] - device 15 char ID
//$_POST['dt_server'] - 0 UTC date and time in "YYYY-MM-DD HH-MM-SS" format
//$_POST['dt_tracker'] - 0 UTC date and time in "YYYY-MM-DD HH-MM-SS" format
//$_POST['lat'] - latitude with +/-
//$_POST['lng'] - longitude with +/-
//$_POST['altitude'] - in meters
//$_POST['angle'] - in degree
//$_POST['speed'] - in km/h
//$_POST['loc_valid'] - 1 means valid location, 0 means not valid location
//$_POST['params'] - stores array of params like acc, di, do, ai...
//$_POST['event'] - possible events: sos, bracon, bracoff, dismount, disassem, door, mandown, shock, tow, pwrcut, gpscut, jamming, lowdc, lowbat, haccel, hbrake, hcorn
define('ROOT_PATH', dirname(__DIR__) . '/');
include('s_init.php');
include('s_events.php');
include('/var/www/html/func/fn_common.php');
include('/var/www/html/tools/gc_func.php');
include('ws/altotrack_1.0.php');
include('ws/walmart.php');
include('ws/recurso_confiable.php');
include('ws/guda.php');
include('ws/grupouda.php');
include('ws/sukarne.php');
include('ws/wsMABE.php');
include('ws/wsLALA.php');
include('ws/wsPanelRey.php');
include('ws/wsKRONH.php');
include('ws/unigis.php');
include('ws/sendEventsFwdLogistica.php');
function insert_db_loc($loc)
{
    global $ms;

    // format data
    $loc['imei'] = strtoupper(trim($loc['imei']));
    $loc['lat'] = (float) sprintf('%0.6f', $loc['lat']);
    $loc['lng'] = (float) sprintf('%0.6f', $loc['lng']);
    $loc['altitude'] = floor($loc['altitude']);
    $loc['angle'] = floor($loc['angle']);
    $loc['speed'] = floor($loc['speed']);
    $loc['protocol'] = strtolower($loc['protocol']);
    $loc['net_protocol'] = strtolower($loc['net_protocol']);

    $q = "SELECT gc.imei, gc.cmd, go.traccar_id FROM gs_object_cmd_exec gc JOIN gs_objects go ON gc.imei = go.imei WHERE gc.imei = '" . $loc['imei'] . "' AND gc.status = 0";
    $r = mysqli_query($ms, $q);

    while ($row = mysqli_fetch_array($r)) {

        $data = [
            'id' => 0,
            'attributes' => [
                'data' => $row['cmd']
            ],
            'deviceId' => intval($row['traccar_id']),
            'type' => 'custom',
            'textChannel' => false,
            'description' => 'nuevo...'
        ];


        $jsonData = json_encode($data);


        $externalURL = 'http://traccar:8082/api/commands/send';


        $authHeader = 'Authorization: Basic ' . base64_encode("admin:admin");


        $options = [
            'http' => [
                'header' => "Content-Type: application/json\r\n$authHeader",
                'method' => 'POST',
                'content' => $jsonData,
            ],
        ];
        $context = stream_context_create($options);

        $result = file_get_contents($externalURL, false, $context);

        if ($result !== false) {
            $q = "UPDATE gs_object_cmd_exec SET status='1', re_hex= 'OK' WHERE imei='" . $row["imei"] . "'";
            $r = mysqli_query($ms, $q);
        }
    }

    // check for wrong IMEI
    if (!ctype_alnum($loc['imei'])) {
        return false;
    }

    // check for wrong speed
    if ($loc['speed'] > 1000) {
        return false;
    }

    // check if object exists in system
    if (!checkObjectExistsSystem($loc['imei'])) {
        insert_db_unused($loc);
        return false;
    }

    // apply GPS Roll Over fix
    if ((substr($loc['dt_tracker'], 0, 4) > (gmdate('Y') + 10)) || (substr($loc['dt_tracker'], 0, 4) < (gmdate('Y') - 10))) {
        if (substr($loc['dt_tracker'], 5, 5) == gmdate('m-d')) {
            $loc['dt_tracker'] = gmdate('Y') . substr($loc['dt_tracker'], 4, 15);
        } else {
            $loc['dt_tracker'] = gmdate("Y-m-d H:i:s");
        }
    }

    // check if dt_tracker is one day too far - skip coordinate
    if (strtotime($loc['dt_tracker']) >= strtotime(gmdate("Y-m-d H:i:s") . ' +1 days')) {
        return false;
    }

    // check if dt_tracker is at least one hour too far - set 0 UTC time
    if (strtotime($loc['dt_tracker']) >= strtotime(gmdate("Y-m-d H:i:s") . ' +1 hours')) {
        $loc['dt_tracker'] = gmdate("Y-m-d H:i:s");
    }

    // adjust GPS time
    $loc['dt_tracker'] = adjustObjectTime($loc['imei'], $loc['dt_tracker']);

    // get previous known location
    $loc_prev = get_gs_objects_data($loc['imei']);

    // merge params only if dt_tracker is newer
    if (strtotime($loc['dt_tracker']) >= strtotime($loc_prev['dt_tracker'])) {
        $params = $loc['params'];
        $params_prev = $loc_prev['params'];

        // Verifica si driverUniqueId existe en $loc_prev['params']
        if (isset($loc_prev['params']['driverUniqueId'])) {
            $driver_id = $loc_prev['params']['driverUniqueId'];
        }

        $type      = $params['type']      ?? null;
        $type_prev = $params_prev['type'] ?? null;
        
        if ($type !== null && $type !== $type_prev) {
        
            // Merge de params (asegúrate que existan)
            $prevParams = $loc_prev['params'] ?? [];
            $curParams  = $loc['params']      ?? [];
        
            $loc['params'] = mergeParams($prevParams, $curParams);
        
            // Incluye driverUniqueId
            if (isset($driver_id)) {
                $loc['params']['driverUniqueId'] = $driver_id;
            }
        }

        // check if there is any sensor values to ignore due to ignition off
        $ignore_sensors = array();
        $acc_sensor = getSensorFromType($loc['imei'], 'acc');

        if ($acc_sensor) {
            $sensors = getObjectSensors($loc['imei']);

            foreach ($sensors as $key => $value) {
                if ($value['acc_ignore'] == 'true') {
                    $ignore_sensors[] = $value;
                }
            }

            foreach ($ignore_sensors as $key => $value) {
                $sensor_data = getSensorValue($loc['params'], $acc_sensor[0]);

                if ($sensor_data['value'] == 0) {
                    if (isset($loc_prev['params'][$value['param']])) {
                        $loc['params'][$value['param']] = $loc_prev['params'][$value['param']];
                    } else {
                        unset($loc['params'][$value['param']]);
                    }
                }
            }
        }
    }

    insert_db_objects($loc, $loc_prev);

    insert_db_status($loc, $loc_prev);

    insert_db_odo_engh($loc, $loc_prev);

    insert_db_ri($loc, $loc_prev);

    insert_db_dtc($loc);

    // check for duplicate locations
    if (loc_filter($loc, $loc_prev) == false) {
        insert_db_object_data($loc); //Checar

        if ($loc['loc_valid'] == 0) {
            if (($loc['lat'] == 0) || ($loc['lng'] == 0)) {
                $loc['dt_tracker'] = $loc_prev['dt_tracker'];
                $loc['lat'] = $loc_prev['lat'];
                $loc['lng'] = $loc_prev['lng'];
                $loc['altitude'] = $loc_prev['altitude'];

                $loc['angle'] = $loc_prev['angle'];
                $loc['speed'] = $loc_prev['speed'];

                // $file = fopen("archivo.txt", "w");

                // fwrite($file, print_r($loc, true));

                // fclose($file);
            }
        } else {
            if (($loc['lat'] == 0) || ($loc['lng'] == 0)) {
                $loc['dt_tracker'] = $loc_prev['dt_tracker'];
                $loc['lat'] = $loc_prev['lat'];
                $loc['lng'] = $loc_prev['lng'];
                $loc['altitude'] = $loc_prev['altitude'];
                $loc['angle'] = $loc_prev['angle'];
                $loc['speed'] = $loc_prev['speed'];

                // $file = fopen("archivo.txt", "w");

                // fwrite($file, print_r($loc, true));

                // fclose($file);

            }
        }

        // check for local events if dt_tracker is newer, in other case only tracker events will be checked
        if (($loc['lat'] != 0) && ($loc['lng'] != 0)) {
            // check for local events if dt_tracker is newer, in other case only tracker events will be checked
            if (strtotime($loc['dt_tracker']) >= strtotime($loc_prev['dt_tracker'])) {
                check_events($loc, $loc_prev, true, true, false);
            } else {
                check_events($loc, false, false, false, false);
            }
        }
    }

    //********************* COMIENZA AREA PARA WS **********************

    $consulta = "SELECT cf.*, o.name AS economico, v.plate FROM gs_object_custom_fields AS cf JOIN gs_objects AS o ON cf.imei = o.imei
    LEFT JOIN gs_object_vehicle_data AS v ON cf.imei = v.imei where cf.imei ='" . $loc['imei'] . "' and cf.data_list = 'true'";


    if ($resultado = mysqli_query($ms, $consulta)) {

        /* obtener array asociativo */
        while ($row = mysqli_fetch_assoc($resultado)) {
            $result = json_encode($row);
            $loc_ws = json_decode($result, true);
            if ($loc_ws) {

                $ws_name = trim($loc_ws["name"]);

                $ws_id = 0;

                $ws_credentials = explode(',', $loc_ws["value"]);
                $imei = $loc['imei'];

                $response = '';
                $ws0 = $ws_credentials[0] ?? null;
                $ws1 = $ws_credentials[1] ?? null;
                $ws2 = $ws_credentials[2] ?? null;
                $ws3 = $ws_credentials[3] ?? null;
                

                switch ($ws_name) {
                    case "WS_ASSISTCARGO":
                        $payload = array(
                            'ws_name' => 'Assiscargo',
                            'msg' => 'Sin definir',
                            'response' => ''
                        );
                        $response = json_encode($payload);
                        $ws_id = 1;
                        break;
                    case "WALMART":
                        //Array,User,Passw
                        $response = envioWalmart($loc, $ws0, $ws1, $ws2, $ws3, $loc_ws['plate'] ?? null);
                    case "ALTOTRACK":
                        //Array,economico,placa
                        $response = envioAltoTrack($loc, $loc_ws['economico'], $loc_ws['plate']);
                        $wsid = 2;
                        break;
                    case "SUKARNE":
                        //Array,user,passw,temp,tipo de vehiculo
                        $sensor = getSensorFromType($imei, 'temp');
                        $temp_value = null;
                        if (!empty($sensor) && isset($sensor[0])) { 
                            $sensor_data = getSensorValue($loc['params'], $sensor[0]);
                            $temp_value  = $sensor_data['value'];
                        }
                        $response = envioSukarne($loc, $ws0, $ws1, $temp_value, $ws2, $loc_ws['economico'], $loc_ws['plate']);
                        $ws_id = 3;
                        break;
                    case "RCONFIABLE":
                        //Array,user,passw,id,name,placa
                        $response = envioRecursoConfiable($loc, $ws0, $ws1, $ws2, $ws3, $loc_ws['plate'] ?? null);
                        $ws_id = 4;
                        break;
                    case "GUDA":
                        $response = envioGuda1($loc, $ws0, $ws1, $loc_ws['plate']);
                        $ws_id = 5;
                        break;
                    case "GRUPOUDA":
                        $unitDistance = $_SESSION['unit_distance'] ?? 'km';

                        $odometer = getObjectOdometer($imei);
                        $odometer = floor(convDistanceUnits($odometer, 'km', $unitDistance));                        
                        $response = envioGrupoUda($loc, $ws0, $ws1, $loc_ws['plate'], $odometer);
                        break;
                    case "PANELREY":
                        envioPanelRey($loc, $loc_ws['economico'], $loc_ws['plate']);
                        break;
                    case "KRONH":
                        $response = envioKRONH($loc, $ws0, $ws1, $loc_ws['economico']);
                        break;
                    case "FWDLOGISTICA":
                        $response = envioFwdLogistica($loc, $ws0, $ws1, $loc_ws['plate'], $ws2);
                        break;
                    case "MABE":
                        $response = enviaDataToMabe($loc, $loc_ws);
                        break;
                    case "BAFAR":
                        $response = enviaDataToLala($loc, $ws0, $ws1, $loc_ws['economico']);
                        break;
                    case "UNIGIS":
                        //Array,user,passw,temp,tipo de vehiculo
                        $sensor = getSensorFromType($imei, 'temp');
                        $temp_value = null;
                        if (!empty($sensor) && isset($sensor[0])) { 
                            $sensor_data = getSensorValue($loc['params'], $sensor[0]);
                            $temp_value  = $sensor_data['value'];
                        }
                    $response = envioUniGis($loc, $ws0, $ws1, $temp_value, $ws2, $loc_ws['economico'], $loc_ws['plate']);
                }

                file_put_contents('php://stdout', $response);
            }
        }

        /* liberar el conjunto de resultados */
        mysqli_free_result($resultado);
    }

    // /* cerrar la conexión */
    // mysqli_close($enlace);


    //***************************************** TERMINA AREA PARA WS *****************************************

}

function insert_db_noloc($loc)
{
    global $ms;

    // format data
    $loc['imei'] = strtoupper(trim($loc['imei']));
    $loc['protocol'] = strtolower($loc['protocol']);
    $loc['net_protocol'] = strtolower($loc['net_protocol']);

    // check for wrong IMEI
    if (!ctype_alnum($loc['imei'])) {
        return false;
    }

    // get previous known location
    $loc_prev = get_gs_objects_data($loc['imei']);

    if ($loc_prev != false) {
        // add previous known location
        $loc['dt_tracker'] = $loc_prev['dt_tracker'];
        $loc['lat'] = $loc_prev['lat'];
        $loc['lng'] = $loc_prev['lng'];
        $loc['altitude'] = $loc_prev['altitude'];
        $loc['angle'] = $loc_prev['angle'];

        // check speed for reset
        $loc['speed'] = $loc_prev['speed'];
        if ($loc['speed'] > 0) {
            $dt_difference = strtotime(gmdate("Y-m-d H:i:s")) - strtotime($loc['dt_tracker']);
            if ($dt_difference >= 150) {
                $loc['speed'] = 0;
            }
        }

        $loc['loc_valid'] = $loc_prev['loc_valid'];
        $loc['params'] = mergeParams($loc_prev['params'], $loc['params']);

        $q = "UPDATE gs_objects SET 	`protocol`='" . $loc['protocol'] . "',
							`net_protocol`='" . $loc['net_protocol'] . "',
							`ip`='" . $loc['ip'] . "',
							`port`='" . $loc['port'] . "',
							`dt_server`='" . $loc['dt_server'] . "',
							`speed`='" . $loc['speed'] . "',
							`params`='" . json_encode($loc['params']) . "'
							WHERE imei='" . $loc['imei'] . "'";

        $r = mysqli_query($ms, $q) or die(mysqli_error($ms));

        // check if location exists
        if (($loc['lat'] != 0) && ($loc['lng'] != 0)) {
            insert_db_status($loc, $loc_prev);

            insert_db_odo_engh($loc, $loc_prev);

            insert_db_ri($loc, $loc_prev);

            insert_db_dtc($loc);

            check_events($loc, $loc_prev, false, true, false);
        }
    }
}

function insert_db_imgloc($loc)
{
    global $ms, $gsValues;

    // format data
    $loc['imei'] = strtoupper(trim($loc['imei']));
    $loc['lat'] = (float) sprintf('%0.6f', $loc['lat']);
    $loc['lng'] = (float) sprintf('%0.6f', $loc['lng']);
    $loc['altitude'] = floor($loc['altitude']);
    $loc['angle'] = floor($loc['angle']);
    $loc['speed'] = floor($loc['speed']);
    $loc['protocol'] = strtolower($loc['protocol']);
    $loc['net_protocol'] = strtolower($loc['net_protocol']);

    // check for wrong IMEI
    if (!ctype_alnum($loc['imei'])) {
        return false;
    }

    // check if object exists in system
    if (!checkObjectExistsSystem($loc['imei'])) {
        return false;
    }

    if (($loc['lat'] == 0) || ($loc['lng'] == 0)) {
        // get previous known location
        $loc_prev = get_gs_objects_data($loc['imei']);

        //$loc['dt_tracker'] = $loc_prev['dt_tracker'];
        $loc['lat'] = $loc_prev['lat'];
        $loc['lng'] = $loc_prev['lng'];
        $loc['altitude'] = $loc_prev['altitude'];
        $loc['angle'] = $loc_prev['angle'];
        $loc['speed'] = $loc_prev['speed'];
    }

    $img_file = $loc['imei'] . '_' . $loc['dt_server'] . '.jpg';
    $img_file = str_replace('-', '', $img_file);
    $img_file = str_replace(':', '', $img_file);
    $img_file = str_replace(' ', '_', $img_file);

    // save to database
    $q = "INSERT INTO gs_object_img (img_file,
						imei,
						dt_server,
						dt_tracker,
						lat,
						lng,
						altitude,
						angle,
						speed,
						params
						) VALUES (
						'" . $img_file . "',
						'" . $loc['imei'] . "',
						'" . $loc['dt_server'] . "',
						'" . $loc['dt_tracker'] . "',
						'" . $loc['lat'] . "',
						'" . $loc['lng'] . "',
						'" . $loc['altitude'] . "',
						'" . $loc['angle'] . "',
						'" . $loc['speed'] . "',
						'" . json_encode($loc['params']) . "')";

    $r = mysqli_query($ms, $q);

    // save file
    $img_path = $gsValues['PATH_ROOT'] . '/data/img/';
    $img_path = $img_path . basename($img_file);

    if (!isFilePathValid($img_path)) {
        die;
    }

    $postdata = hex2bin($loc["img"]);

    if (substr($postdata, 0, 3) == "\xFF\xD8\xFF") {
        $fp = fopen($img_path, "w");
        fwrite($fp, $postdata);
        fclose($fp);
    }
}

function insert_db_unused($loc)
{
    global $ms;

    $q = "INSERT INTO `gs_objects_unused` (imei, protocol, net_protocol, ip, port, dt_server, count)
						VALUES ('" . $loc['imei'] . "', '" . $loc['protocol'] . "', '" . $loc['net_protocol'] . "', '" . $loc['ip'] . "', '" . $loc['port'] . "', '" . $loc['dt_server'] . "', '1')
						ON DUPLICATE KEY UPDATE protocol = '" . $loc['protocol'] . "', net_protocol = '" . $loc['net_protocol'] . "', ip = '" . $loc['ip'] . "', port = '" . $loc['port'] . "', dt_server = '" . $loc['dt_server'] . "', count = count + 1";
    $r = mysqli_query($ms, $q) or die(mysqli_error($ms));
}

function insert_db_objects($loc, $loc_prev)
{
    global $ms;

    //incrementar contador de reportes 
    $q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $loc['imei'] . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);
    $contador = $row['contador'];
    if ($contador >= 0) {
        $contador++;
    }
    $new_params_json = json_encode($loc['params']);


    if (strtotime($loc['dt_tracker']) >= strtotime($loc_prev['dt_tracker'])) {
        if ($loc['loc_valid'] == 1) {
            // calculate angle
            if ($loc['angle'] == 0) {
                $loc['angle'] = getAngle($loc_prev['lat'], $loc_prev['lng'], $loc['lat'], $loc['lng']);
            }

            $q = "UPDATE gs_objects SET	`protocol`='" . $loc['protocol'] . "',
								`net_protocol`='" . $loc['net_protocol'] . "',
								`ip`='" . $loc['ip'] . "',
								`port`='" . $loc['port'] . "',
								`dt_server`='" . $loc['dt_server'] . "',
								`dt_tracker`='" . $loc['dt_tracker'] . "',
								`lat`='" . $loc['lat'] . "',
								`lng`='" . $loc['lng'] . "',
								`altitude`='" . $loc['altitude'] . "',
								`angle`='" . $loc['angle'] . "',
								`speed`='" . $loc['speed'] . "',
								`loc_valid`='1',
                                `contador`='" . $contador . "',
								`params`='" . $new_params_json . "'
								WHERE imei='" . $loc['imei'] . "'";

            $r = mysqli_query($ms, $q) or die(mysqli_error($ms));
        } else {
            $loc['speed'] = 0;

            $q = "UPDATE gs_objects SET 	`protocol`='" . $loc['protocol'] . "',
								`net_protocol`='" . $loc['net_protocol'] . "',
								`ip`='" . $loc['ip'] . "',
								`port`='" . $loc['port'] . "',
								`dt_server`='" . $loc['dt_server'] . "',
								`speed`='" . $loc['speed'] . "',
								`loc_valid`='0',
                                `contador`='" . $contador . "',
								`params`='" . $new_params_json . "'
								WHERE imei='" . $loc['imei'] . "'";

            $r = mysqli_query($ms, $q) or die(mysqli_error($ms));
        }
    } else {
        $q = "UPDATE gs_objects SET 	`protocol`='" . $loc['protocol'] . "',
							`net_protocol`='" . $loc['net_protocol'] . "',
							`ip`='" . $loc['ip'] . "',
							`port`='" . $loc['port'] . "',
                            `contador`='" . $contador . "',
							`dt_server`='" . $loc['dt_server'] . "'
							WHERE imei='" . $loc['imei'] . "'";

        $r = mysqli_query($ms, $q) or die(mysqli_error($ms));
    }
    $new_params_array = json_decode($new_params_json, true);

    $version = $new_params_array['version'] ?? null;
    $iccid = $new_params_array['iccid'] ?? null;
    
    if ($version) {
        if ($version == '6E0304') {
            $fw = '1';
        } elseif ($version == '6E0405') {
            $fw = '2';
        } elseif ($version == '6E0202') {
            $fw = '3';
        }
        if (isset($fw)) { // Asegurarse de que $fw esté definido
            createcommand($loc['imei'], $fw);
        }
    }
    
    if ($iccid) {
        $q = "UPDATE gs_objects SET
        `sim_iccid`='" . mysqli_real_escape_string($ms, $iccid) . "' 
        WHERE `imei`='" . mysqli_real_escape_string($ms, $loc['imei']) . "'";
    
        $r = mysqli_query($ms, $q) or die(mysqli_error($ms));
    }
    
}

function insert_db_object_data($loc)
{
    global $ms;

    if (($loc['lat'] != 0) && ($loc['lng'] != 0)) {
        $q = "INSERT INTO gs_object_data_" . $loc['imei'] . "(	dt_server,
										dt_tracker,
										lat,
										lng,
										altitude,
										angle,
										speed,
										params
										) VALUES (
										'" . $loc['dt_server'] . "',
										'" . $loc['dt_tracker'] . "',
										'" . $loc['lat'] . "',
										'" . $loc['lng'] . "',
										'" . $loc['altitude'] . "',
										'" . $loc['angle'] . "',
										'" . $loc['speed'] . "',
										'" . json_encode($loc['params']) . "')";

        $r = mysqli_query($ms, $q) or die(mysqli_error($ms));
    }
}

function insert_db_status($loc, $loc_prev)
{
    global $ms;

    if (strtotime($loc['dt_tracker']) >= strtotime($loc_prev['dt_tracker'])) {
        $imei = $loc['imei'];
        $params = $loc['params'];

        $dt_last_stop = strtotime($loc_prev['dt_last_stop']);
        $dt_last_idle = strtotime($loc_prev['dt_last_idle']);
        $dt_last_move = strtotime($loc_prev['dt_last_move']);

        if ($loc['loc_valid'] == 1) {
            // status stop
            if ((($dt_last_stop <= 0) || ($dt_last_stop < $dt_last_move)) && ($loc['speed'] == 0)) {
                $q = "UPDATE gs_objects SET `dt_last_stop`='" . $loc['dt_server'] . "' WHERE imei='" . $imei . "'";
                $r = mysqli_query($ms, $q) or die(mysqli_error($ms));

                $dt_last_stop = strtotime($loc['dt_server']);
            }

            // status moving
            if (($dt_last_stop >= $dt_last_move) && ($loc['speed'] > 0)) {
                $q = "UPDATE gs_objects SET `dt_last_move`='" . $loc['dt_server'] . "' WHERE imei='" . $imei . "'";
                $r = mysqli_query($ms, $q) or die(mysqli_error($ms));

                $dt_last_move = strtotime($loc['dt_server']);
            }
        } else {
            // status stop
            if ((($dt_last_stop <= 0) || ($dt_last_stop < $dt_last_move)) && ($loc['speed'] == 0)) {
                $q = "UPDATE gs_objects SET `dt_last_stop`='" . $loc['dt_server'] . "' WHERE imei='" . $imei . "'";
                $r = mysqli_query($ms, $q) or die(mysqli_error($ms));

                $dt_last_stop = strtotime($loc['dt_server']);
            }
        }

        // status idle
        if ($dt_last_stop >= $dt_last_move) {
            $sensor = getSensorFromType($imei, 'acc');
            $acc = $sensor[0]['param'] ?? null;

            if (isset($params[$acc])) {
                if (($params[$acc] == 1) && ($dt_last_idle <= 0)) {
                    $q = "UPDATE gs_objects SET `dt_last_idle`='" . $loc['dt_server'] . "' WHERE imei='" . $imei . "'";
                    $r = mysqli_query($ms, $q) or die(mysqli_error($ms));
                } else if (($params[$acc] == 0) && ($dt_last_idle > 0)) {
                    $q = "UPDATE gs_objects SET `dt_last_idle`='0000-00-00 00:00:00' WHERE imei='" . $imei . "'";
                    $r = mysqli_query($ms, $q) or die(mysqli_error($ms));
                }
            }
        } else {
            if ($dt_last_idle > 0) {
                $q = "UPDATE gs_objects SET `dt_last_idle`='0000-00-00 00:00:00' WHERE imei='" . $imei . "'";
                $r = mysqli_query($ms, $q) or die(mysqli_error($ms));
            }
        }
    }
}

function insert_db_odo_engh($loc, $loc_prev)
{
    global $ms;

    $imei = $loc['imei'];
    $params = $loc['params'];
    $params_prev = $loc_prev['params'];

    // odo gps
    if ($loc_prev['odometer_type'] == 'gps') {
        if (strtotime($loc['dt_tracker']) >= strtotime($loc_prev['dt_tracker'])) {
            if ($loc['loc_valid'] == 1) {
                if (($loc_prev['lat'] != 0) && ($loc_prev['lng'] != 0) && ($loc['speed'] > 3)) {
                    $odometer = getLengthBetweenCoordinates($loc_prev['lat'], $loc_prev['lng'], $loc['lat'], $loc['lng']);

                    if ($odometer > 0) {
                        $q = 'UPDATE gs_objects SET `odometer` = odometer + ' . $odometer . ' WHERE imei="' . $imei . '"';
                        $r = mysqli_query($ms, $q);

                        // dashboard/mileage
                        $q = 'UPDATE gs_objects SET `mileage_1` = mileage_1 + ' . $odometer . ' WHERE imei="' . $imei . '"';
                        $r = mysqli_query($ms, $q);
                    }
                }
            }
        }
    }

    // odo sen
    if ($loc_prev['odometer_type'] == 'sen') {
        $sensor = getSensorFromType($imei, 'odo');

        if ($sensor != false) {
            $sensor_ = $sensor[0];

            $odo = getSensorValue($params, $sensor_);

            $result_type = $sensor_['result_type'];

            if ($result_type == 'abs') {
                if (strtotime($loc['dt_tracker']) >= strtotime($loc_prev['dt_tracker'])) {
                    $q = 'UPDATE gs_objects SET `odometer` = ' . $odo['value'] . ' WHERE imei="' . $imei . '"';
                    $r = mysqli_query($ms, $q);

                    // dashboard/mileage
                    $mileage_1 = $odo['value'] - $loc_prev['odometer'];
                    if ($mileage_1 > 0) {
                        $q = 'UPDATE gs_objects SET `mileage_1` = ' . $mileage_1 . ' WHERE imei="' . $imei . '"';
                        $r = mysqli_query($ms, $q);
                    }
                }
            }

            if ($result_type == 'rel') {
                if ($odo['value'] > 0) {
                    $q = 'UPDATE gs_objects SET `odometer` = odometer + ' . $odo['value'] . ' WHERE imei="' . $imei . '"';
                    $r = mysqli_query($ms, $q);

                    // dashboard/mileage
                    $q = 'UPDATE gs_objects SET `mileage_1` = mileage_1 + ' . $odo['value'] . ' WHERE imei="' . $imei . '"';
                    $r = mysqli_query($ms, $q);
                }
            }
        }
    }

    // engh acc
    if ($loc_prev['engine_hours_type'] == 'acc') {
        if (strtotime($loc['dt_tracker']) >= strtotime($loc_prev['dt_tracker'])) {
            if ($loc['loc_valid'] == 1) {
                if ((strtotime($loc['dt_tracker']) > 0) && (strtotime($loc_prev['dt_tracker']) > 0)) {
                    $engine_hours = 0;

                    // get ACC sensor
                    $sensor = getSensorFromType($imei, 'acc');
                    $acc = $sensor[0]['param'];

                    // calculate engine hours from ACC
                    $dt_tracker = $loc['dt_tracker'];
                    $dt_tracker_prev = $loc_prev['dt_tracker'];

                    if (isset($params_prev[$acc]) && isset($params[$acc])) {
                        if (($params_prev[$acc] == '1') && ($params[$acc] == '1')) {
                            $engine_hours = strtotime($dt_tracker) - strtotime($dt_tracker_prev);

                            // calculate engine hours only if message time difference is not longer than 300 sec in order to avoid issues with some devices which change parameters without location data
                            if ($engine_hours <= 300) {
                                $q = 'UPDATE gs_objects SET `engine_hours` = engine_hours + ' . $engine_hours . ' WHERE imei="' . $imei . '"';
                                $r = mysqli_query($ms, $q);
                            }
                        }
                    }
                }
            } else {
                if ((strtotime($loc['dt_server']) > 0) && (strtotime($loc_prev['dt_server']) > 0)) {
                    $engine_hours = 0;

                    // get ACC sensor
                    $sensor = getSensorFromType($imei, 'acc');
                    $acc = $sensor[0]['param'];

                    // calculate engine hours from ACC
                    $dt_server = $loc['dt_server'];
                    $dt_server_prev = $loc_prev['dt_server'];

                    if (isset($params_prev[$acc]) && isset($params[$acc])) {
                        if (($params_prev[$acc] == '1') && ($params[$acc] == '1')) {
                            $engine_hours = strtotime($dt_server) - strtotime($dt_server_prev);

                            // calculate engine hours only if message time difference is not longer than 300 sec in order to avoid issues with some devices which change parameters without location data
                            if ($engine_hours <= 300) {
                                $q = 'UPDATE gs_objects SET `engine_hours` = engine_hours + ' . $engine_hours . ' WHERE imei="' . $imei . '"';
                                $r = mysqli_query($ms, $q);
                            }
                        }
                    }
                }
            }
        }
    }

    // eng sen
    if ($loc_prev['engine_hours_type'] == 'sen') {
        $sensor = getSensorFromType($imei, 'engh');

        if ($sensor != false) {
            $sensor_ = $sensor[0];

            $engh = getSensorValue($params, $sensor_);

            $result_type = $sensor_['result_type'];

            if ($result_type == 'abs') {
                if (strtotime($loc['dt_tracker']) >= strtotime($loc_prev['dt_tracker'])) {
                    $q = 'UPDATE gs_objects SET `engine_hours` = ' . $engh['value'] . ' WHERE imei="' . $imei . '"';
                    $r = mysqli_query($ms, $q);
                }
            }

            if ($result_type == 'rel') {
                $q = 'UPDATE gs_objects SET `engine_hours` = engine_hours + ' . $engh['value'] . ' WHERE imei="' . $imei . '"';
                $r = mysqli_query($ms, $q);
            }
        }
    }
}

function insert_db_ri($loc, $loc_prev)
{
    global $ms;

    // logbook
    if (strtotime($loc['dt_tracker']) >= strtotime($loc_prev['dt_tracker'])) {
        $imei = $loc['imei'];
        $params = $loc['params'];
        $params_prev = $loc_prev['params'];

        $group_array = array('da', 'pa', 'ta');

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
                    insert_db_ri_data($loc['dt_server'], $loc['dt_tracker'], $imei, $group, $assign_id, $loc['lat'], $loc['lng']);
                }
            }
        }
    }
}

function insert_db_ri_data($dt_server, $dt_tracker, $imei, $group, $assign_id, $lat, $lng)
{
    global $ms;

    $address = geocoderGetAddress($lat, $lng);

    $q = 'INSERT INTO gs_rilogbook_data  (	`dt_server`,
							`dt_tracker`,
							`imei`,
							`group`,
							`assign_id`,
							`lat`,
							`lng`,
							`address`
							) VALUES (
							"' . $dt_server . '",
							"' . $dt_tracker . '",
							"' . $imei . '",
							"' . $group . '",
							"' . $assign_id . '",
							"' . $lat . '",
							"' . $lng . '",
							"' . mysqli_real_escape_string($ms, $address) . '")';

    $r = mysqli_query($ms, $q) or die(mysqli_error($ms));
}

function insert_db_dtc($loc)
{
    global $ms;

    if (isset($loc['event'])) {
        if (substr($loc['event'], 0, 3) == 'dtc') {
            $dtcs = str_replace("dtc:", "", $loc['event']);

            $dtcs = explode(',', $dtcs);

            for ($i = 0; $i < count($dtcs); ++$i) {
                if ($dtcs[$i] != '') {
                    insert_db_dtc_data($loc['dt_server'], $loc['dt_tracker'], $loc['imei'], strtoupper($dtcs[$i]), $loc['lat'], $loc['lng']);
                }
            }
        }
    }
}

function insert_db_dtc_data($dt_server, $dt_tracker, $imei, $code, $lat, $lng)
{
    global $ms;

    // check for duplicates during past 24 hours
    $q = "SELECT * FROM `gs_dtc_data` WHERE `imei`='" . $imei . "' AND `code`='" . $code . "' AND dt_server > DATE_SUB(UTC_DATE(), INTERVAL 1 DAY)";
    $r = mysqli_query($ms, $q);

    $num = mysqli_num_rows($r);

    if ($num == 0) {
        $address = geocoderGetAddress($lat, $lng);

        $q = 'INSERT INTO gs_dtc_data  (`dt_server`,
							`dt_tracker`,
							`imei`,
							`code`,
							`lat`,
							`lng`,
							`address`
							) VALUES (
							"' . $dt_server . '",
							"' . $dt_tracker . '",
							"' . $imei . '",
							"' . $code . '",
							"' . $lat . '",
							"' . $lng . '",
							"' . mysqli_real_escape_string($ms, $address) . '")';

        $r = mysqli_query($ms, $q) or die(mysqli_error($ms));
    }
}

function get_gs_ws_data($imei)
{
    global $ms;

    // $q = "SELECT * FROM gs_object_custom_fields WHERE `imei`='".$imei."'";
    $q = "SELECT gs_object_custom_fields.*, gs_objects.name as economico,gs_objects.plate_number FROM gs_object_custom_fields join gs_objects on gs_object_custom_fields.imei = gs_objects.imei where gs_object_custom_fields.imei ='" . $imei . "'";
    $r = mysqli_query($ms, $q) or die(mysqli_error($ms));
    $row = mysqli_fetch_array($r);

    if ($row) {
        // $row['params'] = json_decode($row['params'],true);
        $row['params'] = $row['value'];

        return $row;
    } else {
        return false;
    }
}

function get_gs_objects_data($imei)
{
    global $ms;

    $q = "SELECT * FROM gs_objects WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q) or die(mysqli_error($ms));
    $row = mysqli_fetch_array($r);

    if ($row) {
        $row['params'] = json_decode($row['params'], true);

        return $row;
    } else {
        return false;
    }
}

function loc_filter($loc, $loc_prev)
{
    global $ms, $gsValues;

    if ($gsValues['LOCATION_FILTER'] == false) {
        return false;
    }

    if (isset($loc['lat']) && isset($loc['lng']) && isset($loc['params'])) {
        if (($loc['event'] == '') && ($loc_prev['params'] == $loc['params'])) {
            $dt_difference = abs(strtotime($loc['dt_server']) - strtotime($loc_prev['dt_server']));

            if ($dt_difference < 120) {
                // skip same location
                if (($loc_prev['lat'] == $loc['lat']) && ($loc_prev['lng'] == $loc['lng']) && ($loc_prev['speed'] == $loc['speed'])) {
                    return true;
                }

                // skip drift
                $distance = getLengthBetweenCoordinates($loc_prev['lat'], $loc_prev['lng'], $loc['lat'], $loc['lng']);
                if (($dt_difference < 30) && ($distance < 0.01) && ($loc['speed'] < 3) && ($loc_prev['speed'] == 0)) {
                    return true;
                }
            }
        }
    }

    return false;
}
function createcommand($imei, $fw)
{
    global $ms;
    $q = "SELECT * FROM `gs_user_cmd_schedule` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);
    $cmd = $row['cmd'];

    if ($fw == '1') {
        $cmd_ = 'AT+GTUPD=gv310lau,0,1,20,0,,,http://optimusrastreogps.net/gv310lau_fw/GV310LAU_R02_0314_060B.bin,,0,1,,0001$';
    }
    if ($fw == '2') {
        $cmd_ = 'AT+GTUPD=gv310lau,0,1,20,0,,,http://optimusrastreogps.net/gv310lau_fw/GV310LAU_R02_040A_060B.bin,,0,1,,0001$';
    }
    if ($fw == '3') {
        $cmd_ = 'AT+GTUPD=gv310lau,0,1,20,0,,,http://optimusrastreogps.net/gv310lau_fw/GV310LAU_R02_021E_060B.bin,,0,1,,0001$';
    }
    if ($cmd != $cmd_) {
        date_default_timezone_set("Mexico/General");
        $dt_now = date("Y-m-d H:i:s");
        $dt_now_obj = new DateTime($dt_now);
        $dt_now_obj->modify("+30 minutes");
        $new_dt_now = $dt_now_obj->format("Y-m-d H:i:s");

        $q = "INSERT INTO `gs_user_cmd_schedule`(`user_id`,
								`name`,
								`active`,
								`exact_time`,
								`exact_time_dt`,
								`day_time`,
								`protocol`,
								`imei`,
								`gateway`,
								`type`,
								`cmd`)
								VALUES
								('172',
								'Update_Fw',
								'true',
								'true',
								'" . $new_dt_now . "',
								'false',
								'',
								'" . $imei . "',
								'gprs',
								'ascii',
								'" . $cmd_ . "')";
        $r = mysqli_query($ms, $q);
    }
}
