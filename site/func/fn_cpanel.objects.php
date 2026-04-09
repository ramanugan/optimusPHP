<?php

set_time_limit(0);

session_start();
include('../init.php');
include('fn_common.php');
include('../tools/email.php');
include('../tools/sms.php');
checkUserSession();
checkUserCPanelPrivileges();

loadLanguage($_SESSION["language"], $_SESSION["units"]);
include('../custom/consultas.php');

if (@$_GET['cmd'] == 'load_object_list') {
    // tabla de dispositivos
    $page = $_GET['page']; // get the requested page
    $limit = $_GET['rows']; // get how many rows we want to have into the grid
    $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
    $sord = $_GET['sord']; // get the direction
    $search = caseToUpper(@$_GET['s']); // get search
    $manager_id = @$_GET['manager_id'];

    if (!$sidx) {
        $sidx = 1;
    }

    // check if admin or manager
    if (($_SESSION["cpanel_privileges"] == 'super_admin') || ($_SESSION["cpanel_privileges"] == 'admin')) {
        if ($manager_id == 0) {
            $q = "SELECT * FROM `gs_objects` WHERE UPPER(`imei`) LIKE '%$search%' OR UPPER(`name`) LIKE '%$search%' OR UPPER(`protocol`) LIKE '%$search%' OR UPPER(`sim_number`) LIKE '%$search%' OR UPPER(`no_sensor1`) LIKE '%$search%' OR UPPER(`sensor_trademark`) LIKE '%$search%' OR UPPER(`no_sensor2`) LIKE '%$search%' OR UPPER(`no_sensor3`) LIKE '%$search%'OR UPPER(`acc`) LIKE '%$search%'";
        } else {
            $q = "SELECT * FROM `gs_objects` WHERE `manager_id`='" . $manager_id . "' AND (UPPER(`imei`) LIKE '%$search%' OR UPPER(`name`) LIKE '%$search%' OR UPPER(`protocol`) LIKE '%$search%' OR UPPER(`sim_number`) LIKE '%$search%')";
        }
    } else {
        $q = "SELECT * FROM `gs_objects` WHERE `manager_id`='" . $_SESSION["cpanel_manager_id"] . "' AND (UPPER(`imei`) LIKE '%$search%' OR UPPER(`name`) LIKE '%$search%' OR UPPER(`protocol`) LIKE '%$search%' OR UPPER(`sim_number`) LIKE '%$search%')";
    }

    $r = mysqli_query($ms, $q);
    $count = mysqli_num_rows($r);

    if ($count > 0) {
        $total_pages = ceil($count / $limit);
    } else {
        $total_pages = 1;
    }

    if ($page > $total_pages) {
        $page = $total_pages;
    }
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
            $imei = $row['imei'];

            if ($row['active'] == 'true') {
                $active = '<a href="#" onclick="objectDeactivate(\'' . $imei . '\');" title="' . $la['DEACTIVATE'] . '"><img src="theme/images/tick-green.svg" /></a>';
            } else {
                $active = '<a href="#" onclick="objectActivate(\'' . $imei . '\');" title="' . $la['ACTIVATE'] . '"><img src="theme/images/remove-red.svg" style="width:12px;" />';
            }

            $expires_on = '';

            if ($row['object_expire'] == 'true') {
                if (strtotime($row['object_expire_dt']) > 0) {
                    $expires_on = $row['object_expire_dt'];
                }
            }

            $last_connection = $row['dt_server'];
            $dt_now = gmdate("Y-m-d H:i:s");

            $dt_difference = strtotime($dt_now) - strtotime($last_connection);
            if ($dt_difference < $gsValues['CONNECTION_TIMEOUT'] * 60) {
                $loc_valid = $row['loc_valid'];

                if ($loc_valid == 1) {
                    $status = '<img src="theme/images/connection-gsm-gps.svg" />';
                } else {
                    $status = '<img src="theme/images/connection-gsm.svg" />';
                }
            } else {
                $status = '<img src="theme/images/connection-no.svg" />';
            }

            $last_connection = convUserTimezone($last_connection);

            $protocol = $row['protocol'];
            $net_protocol = strtoupper($row['net_protocol']);
            $port = $row['port'];

            $used_in = '';

            $q2 = "SELECT * FROM `gs_user_objects` WHERE `imei`='" . $imei . "' ORDER BY `user_id` ASC";
            $r2 = mysqli_query($ms, $q2);

            if (mysqli_num_rows($r2) > 0) {
                $ids_user = array();
                while ($row2 = mysqli_fetch_array($r2)) {
                    $user = getUserData($row2['user_id']);

                    if ($_SESSION["cpanel_privileges"] == 'super_admin' && !in_array($user['user_id'], $ids_user)) {
                        $used_in .= '<a href="#" onclick="userEdit(\'' . $user['user_id'] . '\');">' . $user['username'] . '</a>, ';
                        array_push($ids_user, $user['user_id']);
                    } elseif ($_SESSION["cpanel_privileges"] == 'admin') {
                        if ($user['privileges'] == 'super_admin') {
                            $used_in .= $user['username'] . ', ';
                        } elseif (($user['privileges'] == 'admin') && ($user['user_id'] != $_SESSION["cpanel_user_id"])) {
                            $used_in .= $user['username'] . ', ';
                        } elseif (!in_array($user['user_id'], $ids_user)) {
                            $used_in .= '<a href="#" onclick="userEdit(\'' . $user['user_id'] . '\');">' . $user['username'] . '</a>, ';
                            array_push($ids_user, $user['user_id']);
                        }
                    } else {
                        if ($user['manager_id'] == $_SESSION["cpanel_manager_id"] && !in_array($user['user_id'], $ids_user)) {
                            $used_in .= '<a href="#" onclick="userEdit(\'' . $user['user_id'] . '\');">' . $user['username'] . '</a>, ';
                            array_push($ids_user, $user['user_id']);
                        }
                    }
                }
                $used_in = rtrim($used_in, ', ');
            }

            // Obtener el usuario para compararlo con la otra tabla
            /*$sql =  "SELECT id, email FROM gs_users where id=" . $row2['user_id'];
            $rown = mysqli_fetch_assoc(mysqli_query($ms, $sql));
            $user_email = $rown['email'];*/

            // consulta nueva a la base de datos alterna
            $sql = 'select fecha_alta, observacion, renta from gs_object_observations where imei ="' . $imei . '"';
            $res = CONSULTAR($sql, $conexion_share);
            $res_row = mysqli_fetch_assoc($res);

            $fecha_alta = empty($res_row['fecha_alta']) ? '' : $res_row['fecha_alta'];
            $observacion = empty($res_row['observacion']) ? '' : $res_row['observacion'];
            $renta = empty($res_row['renta']) ? '' : number_format($res_row['renta'], 2, ",", ".");


            // set modify buttons
            $modify = '<a href="#" onclick="objectEdit(\'' . $imei . '\');" title="' . $la['EDIT'] . '"><img src="theme/images/edit.svg" /></a>';
            $modify .= '<a href="#" onclick="objectClearHistory(\'' . $imei . '\');" title="' . $la['CLEAR_HISTORY'] . '"><img src="theme/images/erase.svg" /></a>';
            $modify .= '<a href="#" onclick="objectDelete(\'' . $imei . '\');" title="' . $la['DELETE'] . '"><img src="theme/images/remove3.svg" /></a>';

            // set row
            $renta_simbolizada = empty($renta) ? '' : '$ ' . $renta;
            $response->rows[$i]['id'] = $imei;
            $response->rows[$i]['cell'] = array($row['name'], $row['imei'], $active, $expires_on, $row['sim_number'], $last_connection, $protocol, $fecha_alta, $renta_simbolizada, $observacion, $status, $used_in, $modify);
            $i++;
        }
    }

    header('Content-type: application/json');
    echo json_encode($response);
    die;
}

if (isset($_GET['cmd']) && $_GET['cmd'] === 'load_unused_object_list') {
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = max(1, intval($_GET['rows'] ?? 20));
    $sidx = $_GET['sidx'] ?? 'dt_tracker';
    $sord = $_GET['sord'] ?? 'ASC';
    $search = isset($_GET['s']) ? caseToUpper($_GET['s']) : '';
    $email_client = $_SESSION["email"];
    $excluded_ids = implode(',', [
        1, 171, 172, 290, 311, 316, 320, 345, 689, 621, 720, 723, 766, 767, 768, 769, 770,
        772, 1024, 1046, 1049, 1050, 1051, 1052, 1053, 1054, 1059, 1060, 1066, 1067, 1140,
        1441, 1150, 1167, 1171, 1219, 1599, 1669
    ]);

    $where = "
        FROM gs_objects
        LEFT JOIN gs_user_objects ON gs_objects.imei = gs_user_objects.imei
        INNER JOIN gs_users ON gs_user_objects.user_id = gs_users.id
        WHERE gs_users.manager_id = 0
          AND gs_user_objects.imei IS NOT NULL
          AND gs_user_objects.user_id IS NOT NULL
          AND gs_user_objects.user_id NOT IN ($excluded_ids)
          AND gs_objects.sim_number != '0'
          AND gs_objects.sim_number != ''
    ";

    if ($search) {
        $search_esc = mysqli_real_escape_string($ms, $search);
        $where .= " AND (
            UPPER(gs_objects.imei) LIKE '%$search_esc%' OR
            UPPER(gs_objects.name) LIKE '%$search_esc%' OR
            UPPER(gs_users.username) LIKE '%$search_esc%'
        )";
    }

    $r = mysqli_query($ms, "SELECT COUNT(DISTINCT gs_objects.imei) AS total $where");
    $count = ($r && $row = mysqli_fetch_assoc($r)) ? intval($row['total']) : 0;
    $total_pages = max(1, ceil($count / $limit));
    $page = min($page, $total_pages);
    $start = ($page - 1) * $limit;

    if ($sidx == 'sim_card_acount') $sidx = 'cuenta_padre';
    if ($sidx == 'numero_report') $sidx = 'contador';

    $query = "
        SELECT gs_objects.*, gs_user_objects.user_id, gs_users.*
        $where
        ORDER BY 
            (TIMESTAMPDIFF(SECOND, gs_objects.dt_tracker, UTC_TIMESTAMP()) > 86400) DESC,
            gs_objects.dt_tracker DESC
        LIMIT $start, $limit
    ";
    $r = mysqli_query($ms, $query);

    $response = (object)[
        'page' => $page,
        'total' => $total_pages,
        'records' => $count,
        'rows' => []
    ];

    if ($r) {
        while ($row = mysqli_fetch_assoc($r)) {
            $imei = $row['imei'];
            $user_id = $row['user_id'];
            $username = getUserName_($user_id);
            if (!$username) continue;

            $sim_number = $row['sim_number'];
            $name = $row['name'];
            $contador = $row['contador'];
            $dt_tracker = $row['dt_tracker'];
            $last_connection = convUserTimezone($dt_tracker);
            $dt_now = gmdate("Y-m-d H:i:s");
            $dt_diff = strtotime($dt_now) - strtotime($dt_tracker);
            $reporte_limite = 15000;

            $status = '<img src="theme/images/connection-no-crm.svg" />';
            if ($dt_diff < 300) {
                $status = '<img src="theme/images/connection-gsm-gps.svg" />';
            } elseif ($dt_diff > 86400 && $dt_diff < 86401) {
                $status = '<img src="theme/images/connection-gsm.svg" />';
            }

            $edit = '';
            $q1 = mysqli_query($ms, "SELECT fecha_edit FROM gs_object_data WHERE imei = '$imei'");
            if ($q1 && $row1 = mysqli_fetch_assoc($q1)) {
                $edit = isset($row1['fecha_edit']) ? convUserTimezone($row1['fecha_edit']) : '';
            }

            $comentario = 'Sin Comentario';
            $estado = 'Sin Seguimiento';
            $q2 = mysqli_query($ms, "SELECT comment, attended_status FROM gs_object_data_details WHERE imei = '$imei' ORDER BY id DESC LIMIT 1");
            if ($q2 && $row2 = mysqli_fetch_assoc($q2)) {
                $comentario = $row2['comment'];
                $estado = $row2['attended_status'];
            }

            $modify = '<a href="#" onclick="(\'' . $imei . '\');" title="' . $la['WITHOUT_FOLLOW'] . '"><img src="theme/images/remove-red.svg" style="width:12px;" />';
            if ($estado === 'Agendado' || $estado === 'Postfechar') {
                $modify = '<a href="#" onclick="(\'' . $imei . '\');" title="' . $la['IN_FOLLOW'] . '"><img src="theme/images/time_orange.crm.svg" /></a>';
            } elseif ($estado === 'Atendido') {
                $modify = '<a href="#" onclick="(\'' . $imei . '\');" title="' . $la['ATTENDED'] . '"><img src="theme/images/tick-green.svg" /></a>';
            }

            $follow = '<a href="#" onclick="objectEditCrm(\'' . $imei . '\');" title="' . $la['EDIT'] . '"><img src="theme/images/edit.svg" /></a>';

            $response->rows[] = [
                'id' => $imei,
                'cell' => [$username, $name, $imei, $sim_number, $last_connection, $edit, $contador, $comentario, $status, $modify, $follow]
            ];
        }
    }

    header('Content-type: application/json');
    echo json_encode($response);
    exit;
}
if (@$_GET['cmd'] == 'load_object_search_list') {
    $result = array();

    $search = caseToUpper(@$_GET['search']);
    $manager_id = @$_GET['manager_id'];

    // check if admin or manager
    if (($_SESSION["cpanel_privileges"] == 'super_admin') || ($_SESSION["cpanel_privileges"] == 'admin')) {
        if ($manager_id == 0) {
            $q = "SELECT * FROM `gs_objects` WHERE UPPER(`imei`) LIKE '%$search%' OR UPPER(`name`) LIKE '%$search%'";
        } else {
            $q = "SELECT * FROM `gs_objects` WHERE `manager_id`='" . $manager_id . "' AND (UPPER(`imei`) LIKE '%$search%' OR UPPER(`name`) LIKE '%$search%')";
        }
    } else {
        $q = "SELECT * FROM `gs_objects` WHERE `manager_id`='" . $_SESSION["cpanel_manager_id"] . "' AND (UPPER(`imei`) LIKE '%$search%' OR UPPER(`name`) LIKE '%$search%')";
    }

    $q .= " ORDER BY name ASC";

    $r = mysqli_query($ms, $q);

    while ($row = mysqli_fetch_array($r)) {
        $data['value'] = $row['imei'];
        $data['text'] = stripslashes($row['name']);
        $result[] = $data;
    }

    header('Content-type: application/json');
    echo json_encode($result);
    die;
}

if (@$_POST['cmd'] == 'add_object') {
    $active = $_POST['active'];
    $object_expire = $_POST['object_expire'];
    $object_expire_dt = $_POST['object_expire_dt'];
    $name = $_POST['name'];
    $imei = strtoupper($_POST['imei']);
    $model = $_POST['model'];
    $plan = $_POST['plan'];
    $device = $_POST['device'];
    $sim_number = $_POST['sim_number'];
    $sim_number_company = $_POST['sim_number_company'];
    $cuenta_padre = $_POST['cuenta_padre'];
    $sensor_trademark = $_POST['sensor_trademark'];
    $no_sensor1 = $_POST['no_sensor1'];
    $no_sensor2 = $_POST['no_sensor2'];
    $no_sensor3 = $_POST['no_sensor3'];
    $manager_id = $_POST['manager_id'];
    $acc = $_POST['acc'];
    $user_ids = $_POST['user_ids'];
    $renta = $_POST['renta'];
    $observaciones = $_POST['observaciones'];
    $seller = $_POST['seller'];

    // Datos del vehículo
    $vehicle_year          = $_POST['vehicle_year'];
    $vehicle_brand         = $_POST['vehicle_brand'];
    $vehicle_model         = $_POST['vehicle_model'];
    $vehicle_color         = $_POST['vehicle_color'];
    $vehicle_plate         = $_POST['vehicle_plate'];
    $vehicle_vin           = $_POST['vehicle_vin'];
    $vehicle_odometer      = $_POST['vehicle_odometer'];
    $vehicle_insurance     = $_POST['vehicle_insurance'];
    $vehicle_insurance_exp = $_POST['vehicle_insurance_exp'];
    $vehicle_fuel          = $_POST['vehicle_fuel'];

    // Datos de sensores / señales del vehículo
    $alimentacion_principal = $_POST['alimentacion_principal'];
    $ignicion               = $_POST['ignicion'];
    $bateria                = $_POST['bateria'];
    $bloqueo                = $_POST['bloqueo'];
    $velocidad              = $_POST['velocidad'];
    $t_motor                = $_POST['t_motor'];
    $consumo                = $_POST['consumo'];
    $c_seguridad            = $_POST['c_seguridad'];
    $l_frontales            = $_POST['l_frontales'];
    $l_estacionamiento      = $_POST['l_estacionamiento'];
    $clutch                 = $_POST['clutch'];
    $freno                  = $_POST['freno'];
    $maletero               = $_POST['maletero'];
    $p_conductor            = $_POST['p_conductor'];
    $p_copiloto             = $_POST['p_copiloto'];
    $rpm                    = $_POST['rpm'];
    $nivel_combustible      = $_POST['nivel_combustible'];
    $f_mano                 = $_POST['f_mano'];

    //Sensores Temp_Diesel
    $sn_1  = $_POST['sn_1'];
    $s_1  = $_POST['s_1'];
    $sl_1 = $_POST['sl_1'];
    $sa_1 = $_POST['sa_1'];

    $sn_2  = $_POST['sn_2'];
    $s_2  = $_POST['s_2'];
    $sl_2 = $_POST['sl_2'];
    $sa_2 = $_POST['sa_2'];

    $sn_3  = $_POST['sn_3'];
    $s_3  = $_POST['s_3'];
    $sl_3 = $_POST['sl_3'];
    $sa_3 = $_POST['sa_3'];

    $user_ids_ = json_decode(stripslashes($user_ids), true);

    function checkExists($ms, $query, $errorMsg)
    {
        $r = mysqli_query($ms, $query);
        if ($row = mysqli_fetch_array($r)) {
            echo $errorMsg;
            die;
        }
    }

    if ($sim_number > "0") {
        checkExists($ms, "SELECT 1 FROM `gs_objects` WHERE `sim_number`='" . mysqli_real_escape_string($ms, $sim_number) . "' LIMIT 1", 'ERROR_SIM_NUMBER_EXISTS');
    }

    if (!$device) {
        echo 'DEVICE_CANT_BE_EMPTY';
        die;
    }

    $sensors = [
        ['value' => $no_sensor1, 'error' => 'ERROR_SENSOR1_NUMBER_EXISTS'],
        ['value' => $no_sensor2, 'error' => 'ERROR_SENSOR2_NUMBER_EXISTS'],
        ['value' => $no_sensor3, 'error' => 'ERROR_SENSOR3_NUMBER_EXISTS'],
    ];

    foreach ($sensors as $s) {
        if ($s['value'] > "0") {
            $val = mysqli_real_escape_string($ms, $s['value']);
            $sql = "SELECT 1 FROM `gs_objects` 
                WHERE `no_sensor1`='$val' 
                   OR `no_sensor2`='$val' 
                   OR `no_sensor3`='$val' 
                LIMIT 1";
            checkExists($ms, $sql, $s['error']);
        }
    }

    if ($imei != "") {
        if (checkObjectLimitSystem()) {
            echo 'ERROR_SYSTEM_OBJECT_LIMIT';
            die;
        }

        if (!checkObjectExistsSystem($imei)) {
            // check if admin or manager
            if ($_SESSION["cpanel_privileges"] == 'manager') {
                $manager_id = $_SESSION["cpanel_manager_id"];

                // check object limit
                $q = "SELECT * FROM `gs_objects` WHERE `manager_id`='" . $manager_id . "'";
                $r = mysqli_query($ms, $q);
                $num = mysqli_num_rows($r);

                if ($_SESSION["obj_add"] == 'true') {
                    if ($_SESSION["obj_limit"] == 'true') {
                        if ($num >= $_SESSION["obj_limit_num"]) {
                            echo 'ERROR_OBJECT_LIMIT';
                            die;
                        }
                    }

                    if ($_SESSION["obj_days"] == 'true') {
                        if (($object_expire == 'false') || ($object_expire_dt == '')) {
                            echo 'ERROR_EXPIRATION_DATE_NOT_SET';
                            die;
                        }

                        if (strtotime($_SESSION["obj_days_dt"]) < strtotime($object_expire_dt)) {
                            echo 'ERROR_EXPIRATION_DATE_TOO_LATE';
                            die;
                        }
                    }
                } else {
                    echo 'ERROR_NO_PRIVILEGES';
                    die;
                }
            }
            if ($acc != '') {
                $acc_ = explode(',', $acc);
                $userId = $_SESSION['user_id'];
                if (checkCPanelToObjectUserPrivilegesIncuded($userId)) {


                    $operador = false;
                    $Basico = false;
                    $Mic_Spk = false;
                    $SensorT_1 = false;
                    $SensorT_2 = false;
                    $SensorT_3 = false;
                    $Sensor_1 = false;
                    $Sensor_2 = false;
                    $Sensor_3 = false;
                    $Tanques_2 = false;
                    $Tanques_3 = false;
                    $Tanques_2_3 = false;
                    $Temperatura1 = false;
                    $Temperatura2 = false;
                    $Temperatura3 = false;

                    if ($sim_number_company == 'Telcel') {
                        $operador = 'Telcel';
                    } elseif ($sim_number_company == 'M2M(Teltonika)') {
                        $operador = 'Teltonika';
                    } elseif ($sim_number_company == 'M2M(Telefonica)' || $sim_number_company == 'M2M(Emprenet)') {
                        $operador = 'Movistar';
                    }

                    $panico = in_array('Boton de Panico', $acc_);
                    $basico = in_array('Basico', $acc_);
                    $Mic_Spk = in_array('Mic y Bocina', $acc_);
                    $asistencia = in_array('Boton de Asistencia', $acc_);
                    $paro = in_array('Corte de Motor', $acc_);
                    $temp = in_array('Sensor de Temperatura', $acc_);
                    $enganche = in_array('Sensor de Enganche', $acc_);
                    $puerta = in_array('Sensor de Puerta', $acc_);
                    $sensorT_1 = in_array('Sensor Temp 1', $acc_);
                    $sensorT_2 = in_array('Sensor Temp 2', $acc_);
                    $sensorT_3 = in_array('Sensor Temp 3', $acc_);
                    $sensor_1 = in_array('Sensor Diesel 1', $acc_);
                    $sensor_2 = in_array('Sensor Diesel 2', $acc_);
                    $sensor_3 = in_array('Sensor Diesel 3', $acc_);

                    if ($sensor_1 && $sensor_2 && $sensor_3) {
                        $Tanques_3 = true;
                    } elseif ($sensor_1 && $sensor_2) {
                        $Tanques_2 = true;
                    } elseif ($sensorT_1 && $sensorT_2 && $sensorT_3) {
                        $Temperatura3 = true;
                    } elseif ($sensorT_1 && $sensorT_2) {
                        $Temperatura2 = true;
                    } elseif ($Mic_Spk) {
                        $Mic_Spk = true;
                    } elseif ($basico) {
                        $Basico = true;
                    } elseif ($sensor_2 && $sensor_3) {
                        $Tanques_2_3 = true;
                    } elseif ($sensor_1) {
                        $Sensor_1 = true;
                    } elseif ($sensor_2) {
                        $Sensor_2 = true;
                    } elseif ($sensor_3) {
                        $Sensor_3 = true;
                    } elseif ($panico && $paro) {
                        $Basico = true;
                    } elseif ($sensorT_1) {
                        $Temperatura1 = true;
                    } else {
                        $Basico = true;
                    }
                } else {

                    $Basico = true;
                }

                $comandos = array();

                if ($device && $operador == 'Telcel') {
                    $protocol = $device;
                    $q = "SELECT * FROM `gs_user_cmd` WHERE `user_id` = '999' AND `protocol` = '$protocol' AND gateway = 'sms' ORDER BY `cmd_id` ASC";
                    $r = mysqli_query($ms, $q);

                    if ($r) {
                        while ($row = mysqli_fetch_assoc($r)) {
                            $comandos[$row['name']] = $row['cmd'];
                        }
                    }
                    if ($device == 'DUX' || $device == 'DUXPro') {
                        if ($Basico) {
                            $cmd_config = $comandos['Configuración Basico'];
                            $nombre_configuracion = 'Configuración Básica';
                        } elseif ($Sensor_1) {
                            $cmd_config = $comandos['Configuración Tanque 1'];
                            $nombre_configuracion = 'Configuración Tanque 1';
                        } elseif ($Sensor_2) {
                            $cmd_config = $comandos['Configuración Tanque 2'];
                            $nombre_configuracion = 'Configuración Tanque 2';
                        } elseif ($Sensor_3) {
                            $cmd_config = $comandos['Configuración Tanque 3'];
                            $nombre_configuracion = 'Configuración Tanque 3';
                        } elseif ($Tanques_2 && $device == 'DUXPro') {
                            $cmd_config = $comandos['Configuración Sensores_2'];
                            $nombre_configuracion = 'Configuración Sensores_2';
                        } elseif ($Tanques_2) {
                            $cmd_config = $comandos['Configuración Sensores'];
                            $nombre_configuracion = 'Configuración Sensores';
                        } elseif ($Tanques_3) {
                            $cmd_config = $comandos['Configuración Sensores'];
                            $nombre_configuracion = 'Configuración Sensores_3';
                        } elseif ($Tanques_2_3) {
                            $cmd_config = $comandos['Configuración Sensores_2_3'];
                            $nombre_configuracion = 'Configuración Sensores_2_3';
                        } elseif ($Temperatura) {
                            $cmd_config = $comandos['Configuración Temp'];
                            $nombre_configuracion = 'Configuración Temp';
                        }

                        if (isset($cmd_config)) {
                            $user = $_SESSION["user_id"];
                            sendObjectSMSConfig($user, $imei, $nombre_configuracion, $cmd_config, $sim_number, $sim_number_company, $device);
                            // CreateCommandConfig($imei, $cmd_config, $user, $nombre_configuracion);
                            addRowBinnacle($user, 'Envío de ' . $nombre_configuracion . ' Por Alta: ' . $name, $q);
                            addSensors($imei, $_POST);
                        }
                    }
                } elseif ($device && $operador == 'Movistar') {
                    $protocol = $device;
                    $q = "SELECT * FROM `gs_user_cmd` WHERE `user_id` = '999' AND `protocol` = '$protocol' AND gateway = 'sms' AND `name` LIKE '%_movi%' ORDER BY `cmd_id` ASC";
                    $r = mysqli_query($ms, $q);

                    if ($r) {
                        while ($row = mysqli_fetch_assoc($r)) {
                            $comandos[$row['name']] = $row['cmd'];
                        }
                    }
                    if ($device == 'DUX' || $device == 'DUXPro') {
                        if ($Basico) {
                            $cmd_config = $comandos['Configuración Basico_movi'];
                            $nombre_configuracion = 'Configuración Básica_movi';
                        } elseif ($Sensor_1) {
                            $cmd_config = $comandos['Configuración Tanque 1_movi'];
                            $nombre_configuracion = 'Configuración Tanque 1_movi';
                        } elseif ($Sensor_2) {
                            $cmd_config = $comandos['Configuración Tanque 2_movi'];
                            $nombre_configuracion = 'Configuración Tanque 2_movi';
                        } elseif ($Sensor_3) {
                            $cmd_config = $comandos['Configuración Tanque 3_movi'];
                            $nombre_configuracion = 'Configuración Tanque 3_movi';
                        } elseif ($Tanques_2 && $device == 'DUXPro') {
                            $cmd_config = $comandos['Configuración Sensores_2_movi'];
                            $nombre_configuracion = 'Configuración Sensores_2_movi';
                        } elseif ($Tanques_2) {
                            $cmd_config = $comandos['Configuración Sensores_movi'];
                            $nombre_configuracion = 'Configuración Sensores_movi';
                        } elseif ($Tanques_3) {
                            $cmd_config = $comandos['Configuración Sensores_movi'];
                            $nombre_configuracion = 'Configuración Sensores_3_movi';
                        } elseif ($Tanques_2_3) {
                            $cmd_config = $comandos['Configuración Sensores_2_3_movi'];
                            $nombre_configuracion = 'Configuración Sensores_2_3_movi';
                        } elseif ($Temperatura) {
                            $cmd_config = $comandos['Configuración Temp_movi'];
                            $nombre_configuracion = 'Configuración Temp_movi';
                        }

                        if (isset($cmd_config)) {
                            $user = $_SESSION["user_id"];
                            sendObjectSMSConfig($user, $imei, $nombre_configuracion, $cmd_config, $sim_number, $sim_number_company, $device);
                            // CreateCommandConfig($imei, $cmd_config, $user, $nombre_configuracion);
                            addRowBinnacle($user, 'Envío de ' . $nombre_configuracion . ' Por Alta: ' . $name, $q);
                            addSensors($imei, $_POST);
                        }
                    }
                } elseif ($device && $operador == 'Teltonika') {
                    $protocol = $device;
                    $q = "SELECT * FROM `gs_user_cmd` WHERE `user_id` = '999' AND `protocol` = '$protocol' AND gateway = 'sms' AND `name` LIKE '%teltonika%' ORDER BY `cmd_id` ASC";
                    $r = mysqli_query($ms, $q);

                    if ($r) {
                        while ($row = mysqli_fetch_assoc($r)) {
                            $comandos[$row['name']] = $row['cmd'];
                        }
                    }
                    if ($device == 'DUX' || $device == 'DUXPro') {
                        if ($Basico) {
                            $cmd_config = $comandos['Configuración Basico_sim_teltonika'];
                            $cmd_apn = $comandos['Apn_sim_teltonika'];
                            $nombre_configuracion = 'Configuración Básica_sim_teltonika';
                        } elseif ($Sensor_1) {
                            $cmd_config = $comandos['Configuración Tanque 1_sim_teltonika'];
                            $cmd_apn = $comandos['Apn_sim_teltonika'];
                            $nombre_configuracion = 'Configuración Tanque 1_sim_teltonika';
                        } elseif ($Sensor_2) {
                            $cmd_config = $comandos['Configuración Tanque 2_sim_teltonika'];
                            $cmd_apn = $comandos['Apn_sim_teltonika'];
                            $nombre_configuracion = 'Configuración Tanque 2_sim_teltonika';
                        } elseif ($Sensor_3) {
                            $cmd_config = $comandos['Configuración Tanque 3_sim_teltonika'];
                            $cmd_apn = $comandos['Apn_sim_teltonika'];
                            $nombre_configuracion = 'Configuración Tanque 3_sim_teltonika';
                        } elseif ($Tanques_2 && $device == 'DUXPro') {
                            $cmd_config = $comandos['Configuración Sensores_2_sim_teltonika'];
                            $cmd_apn = $comandos['Apn_sim_teltonika'];
                            $nombre_configuracion = 'Configuración Sensores_2_sim_teltonika';
                        } elseif ($Tanques_2) {
                            $cmd_config = $comandos['Configuración Sensores_sim_teltonika'];
                            $cmd_apn = $comandos['Apn_sim_teltonika'];
                            $nombre_configuracion = 'Configuración Sensores_sim_teltonika';
                        } elseif ($Tanques_3) {
                            $cmd_config = $comandos['Configuración Sensores_sim_teltonika'];
                            $cmd_apn = $comandos['Apn_sim_teltonika'];
                            $nombre_configuracion = 'Configuración Sensores_3_sim_teltonika';
                        } elseif ($Tanques_2_3) {
                            $cmd_config = $comandos['Configuración Sensores_2_3_sim_teltonika'];
                            $cmd_apn = $comandos['Apn_sim_teltonika'];
                            $nombre_configuracion = 'Configuración Sensores_2_3_sim_teltonika';
                        } elseif ($Temperatura) {
                            $cmd_config = $comandos['Configuración Temp_sim_teltonika'];
                            $cmd_apn = $comandos['Apn_sim_teltonika'];
                            $nombre_configuracion = 'Configuración Temp_sim_teltonika';
                        }

                        $user = $_SESSION["user_id"];

                        if (isset($cmd_apn)) {
                            sendObjectSMSConfig($user, $imei, $nombre_configuracion, $cmd_apn, $sim_number, $sim_number_company, $device);
                            sleep(10);
                        }


                        if (isset($cmd_config)) {
                            sendObjectSMSConfig($user, $imei, $nombre_configuracion, $cmd_config, $sim_number, $sim_number_company, $device);
                            // CreateCommandConfig($imei, $cmd_config, $user, $nombre_configuracion);
                            addRowBinnacle($user, 'Envío de ' . $nombre_configuracion . ' Por Alta: ' . $name, $q);
                            addSensors($imei, $_POST);
                        }
                    }
                    if ($device == 'teltonikafm130' || $device == 'teltonikafm920') {
                        $user = $_SESSION["user_id"];
                        $cmd_config = $comandos['FOTA_sim_teltonika'];
                        $nombre_configuracion = 'Configuración Fota_sim_teltonika';
                        sendObjectSMSCommand($user, $imei, '**ALTA**', $device);
                        addSensors($imei, $_POST);

                        CreateCommandConfig($imei, $cmd_config, $user, $nombre_configuracion);
                        addRowBinnacle($user, 'Envío de ' . $nombre_configuracion . ' Por Alta: ' . $name, $q);
                    }

                    if ($device == 'teltonikafm150' || $device == 'teltonikafmOBD') {
                        $user = $_SESSION["user_id"];
                        $cmd_config = $comandos['FOTA_sim_teltonika'];
                        $nombre_configuracion = 'Configuración Fota_sim_teltonika';
                        sendObjectSMSCommand($user, $imei, '**ALTA**', $device);

                        CreateCommandConfig($imei, $cmd_config, $user, $nombre_configuracion);
                        addRowBinnacle($user, 'Envío de ' . $nombre_configuracion . ' Por Alta: ' . $name, $q);
                        addSensors($imei, $_POST);
                    }
                }
            }
            addObjectSystemExtended($name, $plan, $imei, $model, $device, $sim_number, $sim_number_company, $cuenta_padre, $sensor_trademark, $active, $object_expire, $object_expire_dt, $manager_id, $no_sensor1, $no_sensor2, $no_sensor3, $acc);

            createObjectDataTable($imei);

            addVehicleData($imei, $vehicle_year, $vehicle_brand, $vehicle_model, $vehicle_color, $vehicle_plate, $vehicle_vin, $vehicle_odometer, $vehicle_insurance, $vehicle_insurance_exp, $vehicle_fuel);

            $device_mapping = array(
                'DUX' => 409,
                'DUXPro' => 408,
                'queclinkgv300' => 413,
                'queclinkgv75w' => 407,
                'LUXPro' => 388,
                'LUX' => 395,
                'Er-100(4G)' => 398,
                'Er-100(3G)' => 399,
                'ILMA' => 404,
                'suntechST3310U' => 405,
                'suntechST310U' => 389,
                'suntechst910' => 403,
                'suntechst3940' => 402,
                'suntechst300' => 411,
                'suntechST600MD' => 412,
                'cellocatorCR300B' => 397,
                'queclinkgl300w' => 391,
                'TEMPUS' => 414,
                'CondorKeny' => 387,
                'android' => 396,
                'iphone' => 396
            );
            if (empty($user_ids_)) {
                $user_ids_ = ['172', '621'];
            }
            if (!isset($device_mapping[$device])) {
                $device_mapping[$device] = 0;
            }



            for ($i = 0; $i < count($user_ids_); $i++) {
                $user_id = $user_ids_[$i];

                $q = "SELECT JSON_EXTRACT(info, '$.number') AS number FROM `gs_users` WHERE `id`='" . $user_id . "'";
                $r = mysqli_query($ms, $q);
                $row = mysqli_fetch_array($r);
                $client_id = $row['number'];
                if (!$client_id) {
                    $client_id = 0;
                }
                $client_id = trim($client_id, '"');
                if ($user_id != '172' && $user_id != '621') {
                    addObjectUser($user_id, $imei, 0, 0, 0, $client_id);
                    addObjectUser(172, $imei, $device_mapping[$device], 0, 0, 0);
                }
                if (isset($device_mapping[$device]) && $user_id == '172') {
                    addObjectUser($user_id, $imei, $device_mapping[$device], 0, 0, 0);
                } elseif (!isset($device_mapping[$device]) && $user_id == '172') {
                    addObjectUser($user_id, $imei, 0, 0, 0, 0);
                } elseif ($user_id == '621') {
                    addObjectUser($user_id, $imei, 0, 0, 0, 0);
                }
            }


            $user_id = implode(',', $user_ids_);


            $sell = empty($seller) ? 0 : $seller;
            $query = "INSERT INTO gs_object_observations (imei, imei_old, fecha_alta, observacion, fecha_creacion, fecha_modificacion, mtto, mtto_old, users_old, users_new, status, dt_user, usuario_creador, usuario_modificacion, renta, clienteid) 
            VALUES('" . $imei . "',  '', '" . HORA_ACTUAL() . "', '" . $observaciones . "', null, '" . HORA_ACTUAL() . "', '', '', '" . $user_id . "', '', '" . $user_id . "', ";

            if ($_SESSION["user_id"] == '2' || $_SESSION["user_id"] == '766') {
                $query .= "'2023-02-22 00:00:00'";
            } else {
                $query .= "'" . HORA_ACTUAL() . "'";
            }

            $query .= ", '" . $_SESSION['username'] . "', null, " . $renta . ", '" . $sell . "')";

            CONSULTAR($query, $conexion_share);

            echo 'OK';
        } else {
            echo 'ERROR_IMEI_EXISTS';
        }
    }
    die;
} // end add_object

if (@$_POST['cmd'] == 'load_object_data') {
    $imei = $_POST['imei'];

    checkCPanelToObjectPrivileges($imei);

    // get users where object is available
    $users = array();

    $q = "SELECT * FROM `gs_user_objects` WHERE `imei`='" . $imei . "' ORDER BY `user_id` ASC";
    $r = mysqli_query($ms, $q);

    while ($row = mysqli_fetch_array($r)) {
        $q2 = "SELECT * FROM `gs_users` WHERE `id`='" . $row['user_id'] . "'";
        $r2 = mysqli_query($ms, $q2);
        $row2 = mysqli_fetch_array($r2);

        $data['value'] = $row['user_id'];
        $data['text'] = stripslashes($row2['username']);
        $users[] = $data;
    }

    $q = "SELECT * FROM gs_objects WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);
    $acc = $row['acc'];
    $name = $row["name"];
    $sensor_trademark = $row["sensor_trademark"];

    $query = "SELECT * FROM gs_object_observations goo WHERE imei = '" . $imei . "'";
    $response = CONSULTAR($query, $conexion_share);

    $fecha = null;
    $renta = 0.00;
    $observacion = '';
    $seller = '';
    $mtto = '';

    if ($response && mysqli_num_rows($response) > 0) {
        $row2 = mysqli_fetch_assoc($response);

        $fecha = empty($row2['fecha_alta']) ? null : $row2['fecha_alta'];
        $renta = empty($row2['renta']) ? 0.00 : $row2['renta'];
        $observacion = empty($row2['observacion']) ? '' : $row2['observacion'];
        $seller = empty($row2['clienteid']) ? '' : $row2['clienteid'];

        if (!empty($row2['mtto'])) {
            $mtto_values = preg_split('/,\s*/', $row2['mtto']);
            $mtto = end($mtto_values);
        }
    }

    if (preg_match('/,\s/', $name)) {
        $query_select = "SELECT * FROM gs_object_observations WHERE imei_old = '$imei'";
        $r3 = CONSULTAR($query_select, $conexion_share);
        $row3 = mysqli_fetch_array($r3);

        if ($row3) {
            $mtto_ = $row3['mtto_old'];
            $fecha = empty($row3['fecha_alta']) ? null : $row3['fecha_alta'];
            $mtto_values = preg_split('/,\s*/', $mtto_);
            $mtto = end($mtto_values);
        } else {
            $mtto = null;
            $fecha = null;
        }
    }

    $imei_esc = mysqli_real_escape_string($ms, $imei);

    $qVeh  = "SELECT * FROM gs_object_vehicle_data WHERE imei = '$imei_esc' LIMIT 1";
    $rVeh  = mysqli_query($ms, $qVeh);
    $rowVeh = mysqli_fetch_assoc($rVeh) ?: [];

    $qSens = "SELECT param, type FROM gs_object_sensors WHERE imei = '$imei_esc'";
    $rSens = mysqli_query($ms, $qSens);

    $formulas = [];

    $qf = "
      SELECT param, formula
      FROM gs_object_sensors
      WHERE imei = '$imei_esc'
        AND param IN (
          'AdcBT1','AdcBT2','AdcBT3',
          'adc1','adc2','adc3',
          'TempAdcBT1','TempAdcBT2','TempAdcBT3',
          'TempBT1','TempBT2','TempBT3',
          'temp1','temp2','temp3'
        )
    ";
    $rf = mysqli_query($ms, $qf);
    while ($rowf = mysqli_fetch_assoc($rf)) {
        $formulas[$rowf['param']] = $rowf['formula'];
    }

    $hasSensor = [];
    while ($s = mysqli_fetch_assoc($rSens)) {
        $hasSensor[$s['param']] = $s['type'] ?: true;
    }

    $paramToKey = [
        'tempMotor'     => 't_motor',
        'fuelUsed'      => 'consumo',
        'cinturon'      => 'c_seguridad',
        'lucesCruce'    => 'l_frontales',
        'lucesEstac'    => 'l_estacionamiento',
        'embrague'      => 'clutch',
        'frenoPie'      => 'freno',
        'maletero'      => 'maletero',
        'doorFrIzq'     => 'p_conductor',
        'doorFrDer'     => 'p_copiloto',
        'out1'          => 'bloqueo',
        'sos'           => 'panico',
        'rpm'           => 'rpm',
        'vel'           => 'velocidad',
        'fuelLvl'       => 'nivel_combustible',
        'power'         => 'alimentacion_principal',
        'ignition'      => 'ignicion',
        'batteryLevel'  => 'bateria',
        'frenoMano'     => 'f_mano',
        'ch_mag'        => 'ch_mag',
    ];

    $sensorFlags = [];

    foreach ($paramToKey as $param => $key) {
        $sensorFlags[$key] = isset($hasSensor[$param]) ? 'true' : 'false';
    }


    $s_types = ['', '', ''];
    $s_dims  = [
        ['sl' => null, 'sa' => null],
        ['sl' => null, 'sa' => null],
        ['sl' => null, 'sa' => null]
    ];
    $s_entries = [];

    for ($i = 1; $i <= 3; $i++) {

        if (isset($hasSensor["AdcBT{$i}"]) && $hasSensor["AdcBT{$i}"] === 'fuel') {
            $s_entries[] = [
                'type'     => 'DieselBT',
                'num'      => $i,
                'param'    => "AdcBT{$i}",
                'priority' => 1
            ];
        }
        if (isset($hasSensor["adc{$i}"]) && strtolower($hasSensor["adc{$i}"]) === 'fuel') {
            $s_entries[] = [
                'type'     => 'Diesel',
                'num'      => $i,
                'param'    => "adc{$i}",
                'priority' => 2
            ];
        }
        if (isset($hasSensor["TempBT{$i}"])) {
            $s_entries[] = [
                'type'     => 'TemperaturaBT',
                'num'      => $i,
                'param'    => "TempBT{$i}",
                'priority' => 3
            ];
        }
        if (isset($hasSensor["temp{$i}"])) {
            $s_entries[] = [
                'type'     => 'Temperatura',
                'num'      => $i,
                'param'    => "temp{$i}",
                'priority' => 3
            ];
        }
    }

    usort($s_entries, function ($a, $b) {
        if ($a['priority'] === $b['priority']) {
            return $a['num'] <=> $b['num'];
        }
        return $a['priority'] <=> $b['priority'];
    });

    $s_types = ['', '', ''];
    $s_nums  = [null, null, null];
    $s_dims  = [
        ['sl' => null, 'sa' => null],
        ['sl' => null, 'sa' => null],
        ['sl' => null, 'sa' => null]
    ];

    for ($slot = 0; $slot < 3; $slot++) {
        if (!isset($s_entries[$slot])) break;

        $entry = $s_entries[$slot];

        $s_types[$slot] = $entry['type'];
        $s_nums[$slot]  = $entry['num'];

        $paramForDims = $entry['param'];

        if (!empty($formulas[$paramForDims])) {
            $dims = parseSensorDims($formulas[$paramForDims]);
            $s_dims[$slot]['sl'] = $dims['sl'];
            $s_dims[$slot]['sa'] = $dims['sa'];
        }
    }

    $result = array(
        'active'            => $row["active"],
        'object_expire'     => $row["object_expire"],
        'object_expire_dt'  => $row["object_expire_dt"],
        'name'              => $name,
        'imei'              => $row["imei"],
        'plan'              => $row["plan"],
        'model'             => $row["model"],
        'device'            => $row["device"],
        'sim_number'        => $row["sim_number"],
        'sim_number_company' => $row["sim_number_company"],
        'sim_iccid'         => $row["sim_iccid"],
        'cuenta_padre'      => $row['cuenta_padre'],
        'sensor_trademark'  => $row['sensor_trademark'],
        'no_sensor1'        => $row["no_sensor1"],
        'no_sensor2'        => $row["no_sensor2"],
        'no_sensor3'        => $row["no_sensor3"],
        'manager_id'        => $row["manager_id"],
        'acc'               => $acc,
        'mtto'              => $mtto,
        'users'             => $users,
        'renta'             => $renta,
        'observacion'       => $observacion,
        'fecha'             => $fecha,
        'seller'            => $seller,

        'vehicle_year'          => $rowVeh['year']          ?? null,
        'vehicle_brand'         => $rowVeh['brand']         ?? null,
        'vehicle_model'         => $rowVeh['model_']        ?? null,
        'vehicle_color'         => $rowVeh['color']         ?? null,
        'vehicle_plate'         => $rowVeh['plate']         ?? null,
        'vehicle_vin'           => $rowVeh['vin']           ?? null,
        'vehicle_odometer'      => $rowVeh['odometer']      ?? null,
        'vehicle_insurance'     => $rowVeh['insurance']     ?? null,
        'vehicle_insurance_exp' => $rowVeh['insurance_exp'] ?? null,
        'vehicle_fuel'          => $rowVeh['fuel']          ?? null,
    );

    // Datos Sensores
    $result['s_1']        = $s_types[0] ?: '';
    $result['s_1_sl']     = $s_dims[0]['sl'] ?? '';
    $result['s_1_sa']     = $s_dims[0]['sa'] ?? '';
    $result['sn_1']       = $s_nums[0] ?? '';

    $result['s_2']        = $s_types[1] ?: '';
    $result['s_2_sl']     = $s_dims[1]['sl'] ?? '';
    $result['s_2_sa']     = $s_dims[1]['sa'] ?? '';
    $result['sn_2']       = $s_nums[1] ?? '';

    $result['s_3']        = $s_types[2] ?: '';
    $result['s_3_sl']     = $s_dims[2]['sl'] ?? '';
    $result['s_3_sa']     = $s_dims[2]['sa'] ?? '';
    $result['sn_3']       = $s_nums[2] ?? '';

    $result = array_merge($result, $sensorFlags);


    addRowBinnacle($_SESSION["user_id"], 'Edición de equipo:' . json_encode($result), $q);
    echo json_encode($result);
    die;
}

function parseSensorDims($formula)
{
    $out = ['sl' => null, 'sa' => null, 'adc_max' => null, 'offset' => null];

    if (!is_string($formula) || $formula === '') return $out;

    if (preg_match('/^\s*\(?\s*([0-9]+(?:\.[0-9]+)?)\s*\*/', $formula, $m)) {
        $out['sl'] = (float)$m[1];
    }

    if (preg_match('/pow\(\(\s*([0-9]+(?:\.[0-9]+)?)\s*\/\s*2\)\s*,\s*2\)/i', $formula, $m)) {
        $out['sa'] = (float)$m[1];
    }

    if (preg_match('/\(\s*[0-9]+(?:\.[0-9]+)?\s*\/\s*([0-9]+(?:\.[0-9]+)?)\s*\)\s*\*\s*x/i', $formula, $m)) {
        $out['adc_max'] = (float)$m[1];
    }

    if (preg_match('/\+\s*([0-9]+(?:\.[0-9]+)?)\s*$/', trim($formula), $m)) {
        $out['offset'] = (float)$m[1];
    }

    return $out;
}


if (isset($_POST['cmd']) && $_POST['cmd'] === 'load_object_data_crm') {
    $imei = mysqli_real_escape_string($ms, $_POST['imei']);
    $result = [];

    checkCPanelToObjectPrivileges($imei);

    $q = "SELECT name FROM `gs_objects` WHERE `imei` = '$imei'";
    $r = mysqli_query($ms, $q);
    if (!$r || !($row = mysqli_fetch_assoc($r))) {
        echo json_encode(['error' => 'Objeto no encontrado']);
        exit;
    }

    $name = $row['name'] ?: getObjectName($imei);

    $q0 = "SELECT * FROM `gs_object_data` WHERE `imei` = '$imei'";
    $r0 = mysqli_query($ms, $q0);
    $row0 = mysqli_fetch_assoc($r0);

    $q1 = "SELECT * FROM `gs_object_data_details` WHERE `imei` = '$imei'";
    $r1 = mysqli_query($ms, $q1);
    $row1 = mysqli_fetch_assoc($r1);

    if ($row1) {
        $details_list = '<br/><br/>';
        if (!empty($row1['attended_status'])) {
            $user_id = mysqli_real_escape_string($ms, $row1['user_id']);
            $q_ = "
                SELECT gd.details, gd.fecha, gd.event, gd.attended_status, u.username  
                FROM gs_object_data g
                INNER JOIN gs_users u ON u.id = g.user_id_
                INNER JOIN gs_object_data_details gd ON g.imei = gd.imei
                WHERE g.user_id_ = '$user_id' AND g.imei = '$imei'
                ORDER BY gd.fecha DESC
            ";

            $r_ = mysqli_query($ms, $q_);
            while ($row_detail = mysqli_fetch_assoc($r_)) {
                $fecha_local = convUserTimezone($row_detail['fecha']);
                $fecha_fmt = date('Y-m-d H:i:s', strtotime($fecha_local));

                $details_list .= "<strong>{$row_detail['username']}</strong> - <strong>{$fecha_fmt}</strong><br/>";
                $details_list .= nl2br($row_detail['details']) . "<br/><br/>";
            }
        }

        $q2 = "SELECT * FROM `gs_object_data_details` WHERE `imei` = '$imei' ORDER BY `id` DESC LIMIT 1";
        $r2 = mysqli_query($ms, $q2);
        $row2 = mysqli_fetch_assoc($r2);
        $fecha_final = isset($row2['fecha']) ? convUserTimezone($row2['fecha']) : '';


        $result = [
            'name' => $name,
            'imei' => $imei,
            'fecha_service' => $fecha ?? $fecha_final,
            'event_desc' => $row0['event'] ?? '',
            'attended_status' => ($row2['attended_status'] === 'Agendado') ? 'Agendar' : ($row2['attended_status'] ?? ''),
            'details' => $details_list,
        ];
    } else {
        $q2 = "SELECT * FROM `gs_object_data` WHERE `imei` = '$imei'";
        $r2 = mysqli_query($ms, $q2);
        $row2 = mysqli_fetch_assoc($r2);

        $result = [
            'name' => $name,
            'imei' => $imei,
            'event_desc' => $row0['event'] ?? '',
            'attended_status' => $row0['attended'] ?? '',
        ];
    }

    header('Content-type: application/json');
    echo json_encode($result);
    exit;
}

if (@$_POST['cmd'] == 'edit_object') {
    $active = $_POST['active'];
    $object_expire = $_POST['object_expire'];
    $object_expire_dt = $_POST['object_expire_dt'];
    $name = $_POST['name'];
    $imei = strtoupper($_POST['imei']);
    $new_imei = strtoupper($_POST['new_imei']);
    $model = $_POST['model'];
    $plan = $_POST['plan'];
    $device = $_POST['device'];
    $sim_number = $_POST['sim_number'];
    $sim_number_company = $_POST['sim_number_company'];
    $new_sim_number = $_POST['new_sim_number'];
    $cuenta_padre = $_POST['cuenta_padre'];
    $sensor_trademark = $_POST['sensor_trademark'];
    $new_cuenta_padre = $_POST['new_cuenta_padre'];
    $no_sensor1 = $_POST['no_sensor1'];
    $new_no_sensor1 = $_POST['new_no_sensor1'];
    $no_sensor2 = $_POST['no_sensor2'];
    $new_no_sensor2 = $_POST['new_no_sensor2'];
    $no_sensor3 = $_POST['no_sensor3'];
    $new_no_sensor3 = $_POST['new_no_sensor3'];
    $manager_id = $_POST['manager_id'];
    $acc = $_POST['acc'];
    $mtto = $_POST['mtto'];
    $new_mtto = $_POST['new_mtto'];
    $user_ids = $_POST['user_ids'];
    $fecha_alta = $_POST['fecha_alta'];
    $renta = $_POST['renta'];
    $observaciones = $_POST['observaciones'];
    $seller = $_POST['seller'];

    // Datos de vehiculo
    $vehicle_year         = $_POST['v_year'];
    $vehicle_brand        = $_POST['v_brand'];
    $vehicle_model        = $_POST['v_model'];
    $vehicle_color        = $_POST['v_color'];
    $vehicle_plate        = $_POST['v_plate'];
    $vehicle_vin          = $_POST['v_vin'];
    $vehicle_odometer     = $_POST['v_odo'];
    $vehicle_insurance    = $_POST['v_ins'];
    $vehicle_insurance_exp = $_POST['v_insx'];
    $vehicle_fuel         = $_POST['v_fuel'];

    // Datos de sensores / señales del vehículo
    $alimentacion_principal = $_POST['alimentacion_principal'];
    $bateria                = $_POST['bateria'];
    $ignicion               = $_POST['ignicion'];
    $bloqueo                = $_POST['bloqueo'];
    $velocidad              = $_POST['velocidad'];
    $t_motor                = $_POST['t_motor'];
    $consumo                = $_POST['consumo'];
    $c_seguridad            = $_POST['c_seguridad'];
    $l_frontales            = $_POST['l_frontales'];
    $l_estacionamiento      = $_POST['l_estacionamiento'];
    $clutch                 = $_POST['clutch'];
    $freno                  = $_POST['freno'];
    $maletero               = $_POST['maletero'];
    $p_conductor            = $_POST['p_conductor'];
    $p_copiloto             = $_POST['p_copiloto'];
    $rpm                    = $_POST['rpm'];
    $nivel_combustible      = $_POST['nivel_combustible'];
    $f_mano                 = $_POST['f_mano'];

        //Sensores Temp_Diesel
        $sn_1  = $_POST['sn_1'];
        $s_1  = $_POST['s_1'];
        $sl_1 = $_POST['sl_1'];
        $sa_1 = $_POST['sa_1'];
    
        $sn_2  = $_POST['sn_2'];
        $s_2  = $_POST['s_2'];
        $sl_2 = $_POST['sl_2'];
        $sa_2 = $_POST['sa_2'];
    
        $sn_3  = $_POST['sn_3'];
        $s_3  = $_POST['s_3'];
        $sl_3 = $_POST['sl_3'];
        $sa_3 = $_POST['sa_3'];

    checkCPanelToObjectPrivileges($imei);
    $user_ids_ = json_decode(stripslashes($user_ids), true);
    $user = $_SESSION['user_id'];

    if (!checkCPanelToObjectUserPrivileges($user)) {
        echo 'FALSE';
        die;
    }
    if ($new_sim_number > '1') { // solo buscara si existe el sim si este es mayor a "1"
        $q = "SELECT * FROM `gs_objects` WHERE `sim_number`='" . $new_sim_number . "'";
        $r = mysqli_query($ms, $q);


        if ($row = mysqli_fetch_array($r)) {
            echo 'ERROR_SIM_NUMBER_EXISTS';
            die;
        } else {
            addRowBinnacle($_SESSION["user_id"], 'Edición de equipo:' . $name . " sim anterior: " . $sim_number . " sim nuevo: " . $new_sim_number, $q);
            $sim_number = $new_sim_number;
        }
    }
    if ($new_no_sensor1 > '1') { // solo buscara si existe el sensor si este es mayor a "1"
        $q = "SELECT * FROM `gs_objects` WHERE `no_sensor1` ='" . $new_no_sensor1 . "' OR `no_sensor2` ='" . $new_no_sensor1 . "' OR `no_sensor3` ='" . $new_no_sensor1 . "' LIMIT 1";
        $r = mysqli_query($ms, $q);


        if ($row = mysqli_fetch_array($r)) {
            if ($imei != $row['imei']) {
                echo 'ERROR_SENSOR1_NUMBER_EXISTS';
                die;
            }
        } else {
            addRowBinnacle($_SESSION["user_id"], 'Edición de equipo:' . $name . " sensor-1 anterior: " . $no_sensor1 . " sensor-1 nuevo: " . $new_no_sensor1, $q);
            $no_sensor1 = $new_no_sensor1;
        }
    }
    if ($new_no_sensor2 > '1') { // solo buscara si existe el sensor si este es mayor a "1"
        $q = "SELECT * FROM `gs_objects` WHERE `no_sensor1` ='" . $new_no_sensor2 . "' OR `no_sensor2` ='" . $new_no_sensor2 . "' OR `no_sensor2` ='" . $new_no_sensor2 . "' LIMIT 1";
        $r = mysqli_query($ms, $q);


        if ($row = mysqli_fetch_array($r)) {
            if ($imei != $row['imei']) {
                echo 'ERROR_SENSOR2_NUMBER_EXISTS';
                die;
            }
        } else {
            addRowBinnacle($_SESSION["user_id"], 'Edición de equipo:' . $name . " sensor-2 anterior: " . $no_sensor2 . " sensor-2 nuevo: " . $new_no_sensor2, $q);
            $no_sensor2 = $new_no_sensor2;
        }
    }
    if ($new_no_sensor3 > '1') { // solo buscara si existe el sensor si este es mayor a "1"
        $q = "SELECT * FROM `gs_objects` WHERE `no_sensor1` ='" . $new_no_sensor3 . "' OR `no_sensor2` ='" . $new_no_sensor3 . "' OR `no_sensor3` ='" . $new_no_sensor3 . "' LIMIT 1";
        $r = mysqli_query($ms, $q);


        if ($row = mysqli_fetch_array($r)) {
            if ($imei != $row['imei']) {
                echo 'ERROR_SENSOR3_NUMBER_EXISTS';
                die;
            }
        } else {
            addRowBinnacle($_SESSION["user_id"], 'Edición de equipo:' . $name . " sensor-3 anterior: " . $no_sensor3 . " sensor-3 nuevo: " . $new_no_sensor3, $q);
            $no_sensor3 = $new_no_sensor3;
        }
    }


    // change imei
    if ($new_imei != '') {

        $data = array(
            'active' => $_POST['active'],
            'object_expire' => $_POST['object_expire'],
            'object_expire_dt' => $_POST['object_expire_dt'],
            'name' => $_POST['name'],
            'imei' => strtoupper($_POST['imei']),
            'new_imei' => strtoupper($_POST['new_imei']),
            'model' => $_POST['model'],
            'vin' => $_POST['vin'],
            'plan' => $_POST['plan'],
            'plate_number' => $_POST['plate_number'],
            'device' => $_POST['device'],
            'sim_number' => $_POST['sim_number'],
            'sim_number_company' => $_POST['sim_number_company'],
            'new_sim_number' => $_POST['new_sim_number'],
            'cuenta_padre' => $_POST['cuenta_padre'],
            'sensor_trademark' => $_POST['sensor_trademark'],
            'new_cuenta_padre' => $_POST['new_cuenta_padre'],
            'no_sensor1' => $no_sensor1,
            'new_no_sensor1' => $_POST['new_no_sensor1'],
            'no_sensor2' => $no_sensor2,
            'new_no_sensor2' => $_POST['new_no_sensor2'],
            'no_sensor3' => $no_sensor3,
            'new_no_sensor3' => $_POST['new_no_sensor3'],
            'manager_id' => $_POST['manager_id'],
            'acc' => $_POST['acc'],
            'mtto' => $_POST['mtto'],
            'new_mtto' => $_POST['new_mtto'],
            'user_ids' => $_POST['user_ids'],
            'fecha_alta' => $_POST['fecha_alta'],
            'renta' => $_POST['renta'],
            'observaciones' => $_POST['observaciones'],
            'seller' => $_POST['seller']
        );



        if ($mtto == 'Remplazo de Equipo' || $mtto == 'Remplazo de Equipo Garantia') {
            addObjectChangeUser(311, $data);
            echo 'OK';
            die;
        }
        if ($mtto == 'Remplazo de Equipo Cliente') {
            addObjectChangeUser(1669, $data);
            echo 'OK';
            die;
        }

        if (changeObjectIMEI($imei, $new_imei, $mtto)) {
            $imei = $new_imei;
            $new_sim_number = $sim_number;
        } else {
            echo 'ERROR_IMEI_EXISTS';
            die;
        }


        $q = "UPDATE gs_object_vehicle_data SET 
    imei = '" . mysqli_real_escape_string($ms, $imei) . "',
    year = '" . mysqli_real_escape_string($ms, $vehicle_year) . "',
    brand = '" . mysqli_real_escape_string($ms, $vehicle_brand) . "',
    model_ = '" . mysqli_real_escape_string($ms, $vehicle_model) . "',
    color = '" . mysqli_real_escape_string($ms, $vehicle_color) . "',
    plate = '" . mysqli_real_escape_string($ms, $vehicle_plate) . "',
    vin = '" . mysqli_real_escape_string($ms, $vehicle_vin) . "',
    odometer = '" . mysqli_real_escape_string($ms, $vehicle_odometer) . "',
    insurance = '" . mysqli_real_escape_string($ms, $vehicle_insurance) . "',
    insurance_exp = '" . mysqli_real_escape_string($ms, $vehicle_insurance_exp) . "',
    fuel = '" . mysqli_real_escape_string($ms, $vehicle_fuel) . "'
    WHERE imei = '" . mysqli_real_escape_string($ms, $new_imei) . "'";

        $r = mysqli_query($ms, $q);
    }

    if ($_SESSION["cpanel_privileges"] == 'manager') {
        $manager_id = $_SESSION["cpanel_manager_id"];

        if ($_SESSION["obj_days"] == 'true') {
            if (($object_expire == 'false') || ($object_expire_dt == '')) {
                echo 'ERROR_EXPIRATION_DATE_NOT_SET';
                die;
            }

            if (strtotime($_SESSION["obj_days_dt"]) < strtotime($object_expire_dt)) {
                echo 'ERROR_EXPIRATION_DATE_TOO_LATE';
                die;
            }
        }
    }
    if ($new_sim_number == '0') { // no elimina sim, se debe colocar "0" para que continue.
        $sim_number = $new_sim_number;
    }
    if ($new_no_sensor1 == '0') { // no elimina sensor, se debe colocar "0" para que continue.
        $no_sensor1 = $new_no_sensor1;
    }
    if ($new_no_sensor2 == '0') { // no elimina sensor, se debe colocar "0" para que continue.
        $no_sensor2 = $new_no_sensor2;
    }
    if ($new_no_sensor3 == '0') { // no elimina sensor, se debe colocar "0" para que continue.
        $no_sensor3 = $new_no_sensor3;
    }
    if ($new_cuenta_padre == '0') {
        $cuenta_padre = $new_cuenta_padre;
    } else {
        $cuenta_padre = $new_cuenta_padre;
    }
    if ($sim_number == '0' && $cuenta_padre >= '1') { // elimina cuenta padre al eliminar el sim de la unidad.
        $cuenta_padre = 0;
    }

    $q = "UPDATE `gs_objects` SET 	`name`='" . $name . "',
						`model`='" . $model . "',
						`plan`='" . $plan . "',
						`device`='" . $device . "',
						`sim_number`='" . $sim_number . "',
						`sim_number_company`='" . $sim_number_company . "',
                        `cuenta_padre`='" . $cuenta_padre . "',
                        `sensor_trademark`='" . $sensor_trademark . "',
						`active`='" . $active . "',
						`object_expire`='" . $object_expire . "',
						`object_expire_dt`='" . $object_expire_dt . "',
						`manager_id`='" . $manager_id . "',
                        `no_sensor1`='" . $no_sensor1 . "',
                        `no_sensor2`='" . $no_sensor2 . "',
                        `no_sensor3`='" . $no_sensor3 . "',
                        `acc`='" . $acc . "'
                        WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    $q = "UPDATE gs_object_vehicle_data SET 
    year = '" . mysqli_real_escape_string($ms, $vehicle_year) . "',
    brand = '" . mysqli_real_escape_string($ms, $vehicle_brand) . "',
    model_ = '" . mysqli_real_escape_string($ms, $vehicle_model) . "',
    color = '" . mysqli_real_escape_string($ms, $vehicle_color) . "',
    plate = '" . mysqli_real_escape_string($ms, $vehicle_plate) . "',
    vin = '" . mysqli_real_escape_string($ms, $vehicle_vin) . "',
    odometer = '" . mysqli_real_escape_string($ms, $vehicle_odometer) . "',
    insurance = '" . mysqli_real_escape_string($ms, $vehicle_insurance) . "',
    insurance_exp = '" . mysqli_real_escape_string($ms, $vehicle_insurance_exp) . "',
    fuel = '" . mysqli_real_escape_string($ms, $vehicle_fuel) . "'
    WHERE imei = '" . mysqli_real_escape_string($ms, $imei) . "'";

    $r = mysqli_query($ms, $q);

    resetSensors($imei, $_POST);

    if ($renta) {

        $usuario =  $_SESSION["username"];
        date_default_timezone_set("Mexico/General");
        $dt_now = date("Y-m-d H:i:s");

        $query = "UPDATE gs_object_observations
    SET
      fecha_modificacion = '$dt_now',
      fecha_alta = '$fecha_alta',
      renta = '$renta',
      usuario_modificacion = '$usuario'
    WHERE imei = '$imei'";

        $r = mysqli_query($ms, $query);
        CONSULTAR($query, $conexion_share);
    }

    $users = array();
    // get object group, driver and trailer settings (we do not want to to lose them)
    $gs_user_objects = array();

    $q = "SELECT * FROM `gs_user_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    while ($row = mysqli_fetch_array($r)) {
        $users[] = $row['user_id'];
        $gs_user_objects[] = $row;
    }

    // delete object from all users
    $q = "DELETE FROM `gs_user_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);


    // add object to all users
    $diff_1 = array_diff($users, $user_ids_);
    $diff_2 = array_diff($user_ids_, $users);
    $differences = array_merge($diff_1, $diff_2);
    $diff_1 = implode(',', $diff_1);
    $diff_2 = implode(',', $diff_2);
    $old = implode(',', $users);
    $new = implode(',', $user_ids_);
    $string_differences = implode(',', $differences);



    $usuario =  $_SESSION["username"];
    $observacion = '';

    date_default_timezone_set("Mexico/General");
    $dt_now = date("Y-m-d H:i:s");

    $query_select = "SELECT * FROM gs_object_observations WHERE imei = '$imei'";
    $r3 = CONSULTAR($query_select, $conexion_share);
    $row3 = mysqli_fetch_array($r3);
    $fecha_creacion_old = $row3['fecha_creacion'];
    $users_old = $row3['users_old'];
    $fecha_alta_old = $row3['fecha_alta'];
    $dt_user_old = $row3['dt_user'];
    $mtto_old = $row3['mtto'];
    $observacion_old = $row3['observacion'];
    $seller_old = $row3['clienteid'];

    if ($seller != $seller_old) {

        $query = "UPDATE gs_object_observations SET clienteid = '$seller'
            WHERE imei = '$imei'";

        $r = mysqli_query($ms, $query);
        CONSULTAR($query, $conexion_share);
    }

    if ($fecha_alta != $fecha_alta_old) {

        date_default_timezone_set("Mexico/General");
        $dt_now = date("Y-m-d H:i:s");

        $query = "UPDATE gs_object_observations
    SET
      fecha_alta = '$fecha_alta'
    WHERE imei = '$imei'";

        $r = mysqli_query($ms, $query);
        CONSULTAR($query, $conexion_share);
    }

    if (!empty($new_mtto)) {
        if ($mtto_old != $new_mtto) {
            if ($mtto_old != '') {
                $mtto_concat = $mtto_old . ', ' . $new_mtto;
            } else {
                $mtto_concat = $new_mtto;
            }
        } else {
            $mtto_concat = $mtto_old;
        }

        $mtto_concat = rtrim($mtto_concat, ',');



        $observacion_concat = $observacion_old;
        if (!empty($observaciones) && !empty($observacion_old)) {
            $observacion_concat .= ', ' . $observaciones;
        } elseif (!empty($observaciones)) {
            $observacion_concat = $observaciones;
        }

        $query = "UPDATE gs_object_observations
              SET
                observacion = '$observacion_concat',
                fecha_modificacion = '$dt_now',
                fecha_creacion = '$dt_now',
                mtto = '$mtto_concat',
                usuario_modificacion = '$usuario'
              WHERE imei = '$imei'";

        if (
            $new_mtto == 'Batería ER-100' ||
            $new_mtto == 'Mantenimiento Basico' ||
            $new_mtto == 'Mantenimiento Sensor' ||
            $new_mtto == 'Cambio de Sensor 1' ||
            $new_mtto == 'Cambio de Sensor 2' ||
            $new_mtto == 'Cambio de Sensor 3' ||
            $new_mtto == 'Cambio Kitt P/Motor' ||
            $new_mtto == 'Cambio Kitt Panico' ||
            $new_mtto == 'Cambio Kitt Voz' ||
            $new_mtto == 'Cambio de Sensor Temp' ||
            $new_mtto == 'Remplazo de Equipo'
        ) {

            $r = mysqli_query($ms, $query);
            CONSULTAR($query, $conexion_share);

            // Añadir registro de actividad
            addRowBinnacle($_SESSION["user_id"], 'Edición de Mtto: ' . $new_mtto . ': ' . $name . " imei: " . $imei, $q);
        }
    } elseif (!empty($observaciones)) {
        if ($observacion_old != $observaciones) {
            if ($observacion_old != '') {
                $observacion_concat = $observacion_old . ', ' . $observaciones;
            } else {
                $observacion_concat = $observaciones;
            }
        } else {
            $observacion_concat = $observacion_old;
        }


        $mtto_concat = $mtto_old;
        if (!empty($new_mtto) && !empty($mtto_old)) {
            $mtto_concat .= ', ' . $new_mtto;
        } elseif (!empty($new_mtto)) {
            $mtto_concat = $new_mtto;
        }

        $query = "UPDATE gs_object_observations
              SET
                observacion = '$observacion_concat',
                fecha_modificacion = '$dt_now',
                fecha_creacion = '$dt_now',
                mtto = '$mtto_concat',
                usuario_modificacion = '$usuario'
              WHERE imei = '$imei'";

        $r = mysqli_query($ms, $query);
        CONSULTAR($query, $conexion_share);

        addRowBinnacle($_SESSION["user_id"], 'Observacion : ' . $observaciones . ': ' . $name . " imei: " . $imei, $q);
    }




    if ($differences) {
        global $CONEXION;

        if ($diff_2) {
            $mtto_concat = $mtto_old;
            if ($new_mtto && $mtto_old) {
                $mtto_concat .= ', ' . $new_mtto;
            } elseif ($new_mtto) {
                $mtto_concat = $new_mtto;
            }
            $observacion_concat = $observacion;
            if ($observaciones && $observacion_old) {
                $observacion_concat .= ', ' . $observacion_old;
            } elseif ($observacion_old) {
                $observacion_concat = $observacion_old;
            }

            $query = "UPDATE gs_object_observations
            SET
                fecha_alta = '$fecha_alta',
                observacion = '$observacion_concat',
                fecha_modificacion = '$dt_now',
                usuario_modificacion = '$usuario',
                users_old = '$old',
                users_new = '$new',
                status = '$string_differences',
                dt_user = '$dt_now',
                renta = '$renta',
                clienteid = '$seller_old',
                mtto = '$mtto_concat'
            WHERE imei = '$imei'";

            $r = mysqli_query($ms, $query);
            CONSULTAR($query, $conexion_share);
            $q1 = "SELECT * FROM `gs_users` WHERE `id`='" . $diff_2 . "'";
            $r1 = mysqli_query($ms, $q1);
            $row1 = mysqli_fetch_array($r1);
            $username = $row1['username'];
            addRowBinnacle($_SESSION["user_id"], 'Alta de Equipo a :' . $username . " imei: " . $imei, $q);
        }
        if ($diff_1) {
            $query = "UPDATE gs_object_observations
            SET
                fecha_alta = '$fecha_alta',
                observacion = '$observacion',
                fecha_modificacion = '$dt_now',
                usuario_modificacion = '$usuario',
                users_old = '$old',
                users_new = '$new',
                status = '$string_differences',
                renta = '$renta',
                clienteid = '$seller_old'
            WHERE imei = '$imei'";

            $r = mysqli_query($ms, $query);
            CONSULTAR($query, $conexion_share);
            $q1 = "SELECT * FROM `gs_users` WHERE `id`='" . $diff_1 . "'";
            $r1 = mysqli_query($ms, $q1);
            $row1 = mysqli_fetch_array($r1);
            $username = $row1['username'];

            addRowBinnacle($_SESSION["user_id"], 'Baja de Equipo a :' . $username . " imei: " . $imei, $q);
        }
    }




    if (!in_array('172', $user_ids_)) {
        array_push($user_ids_, '172');
    }
    createObjectDataTable($imei);

    for ($i = 0; $i < count($user_ids_); $i++) {
        $user_id = $user_ids_[$i];

        $query = "SELECT info FROM gs_users WHERE id = '$user_id'";
        $result = mysqli_query($ms, $query);
        $row = mysqli_fetch_assoc($result);

        $info = json_decode($row['info'], true);
        $number = isset($info['number']) ? $info['number'] : 0;

        $group_id = 0;
        $driver_id = 0;
        $trailer_id = 0;

        for ($j = 0; $j < count($gs_user_objects); $j++) {
            if ($gs_user_objects[$j]['user_id'] == $user_id) {
                $group_id = $gs_user_objects[$j]['group_id'];
                $driver_id = $gs_user_objects[$j]['driver_id'];
                $trailer_id = $gs_user_objects[$j]['trailer_id'];
            }
        }

        addObjectUser($user_id, $imei, $group_id, $driver_id, $trailer_id, $number);
    }


    echo 'OK';
    die;
}

if (@$_POST['cmd'] == 'clear_history_object') {
    $imei = $_POST['imei'];

    $user = $_SESSION['user_id'];
    if (!checkCPanelToObjectUserPrivileges($user)) {
        echo 'FALSE';
        die;
    }

    checkCPanelToObjectPrivileges($imei);

    clearObjectHistory($imei);

    echo 'OK';
    die;
}

if (@$_POST['cmd'] == 'clear_history_selected_objects') {
    $imeis = $_POST["imeis"];

    $user = $_SESSION['user_id'];
    if (!checkCPanelToObjectUserPrivileges($user)) {
        echo 'FALSE';
        die;
    }

    for ($i = 0; $i < count($imeis); ++$i) {
        $imei = $imeis[$i];

        checkCPanelToObjectPrivileges($imei);

        clearObjectHistory($imei);
    }

    echo 'OK';
    die;
}

if (@$_POST['cmd'] == 'delete_object') {
    $imei = $_POST['imei'];

    $user = $_SESSION['user_id'];
    if (!checkCPanelToObjectUserPrivileges($user)) {
        echo 'FALSE';
        die;
    }
    checkCPanelToObjectPrivileges($imei);

    $q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);
    $name = $row['name'];
    addRowBinnacle($_SESSION["user_id"], 'Baja de equipo:' . $name . " imei: " . $imei, $q);
    delObjectSystem($imei);
    $query_select = "delete from gs_object_observations where imei = '" . $imei . "'";
    $response = CONSULTAR($query_select, $conexion_share);

    echo 'OK';
    die;
}

if (@$_POST['cmd'] == 'activate_object') {
    $imei = $_POST["imei"];

    $user = $_SESSION['user_id'];
    if (!checkCPanelToObjectUserPrivileges($user)) {
        echo 'FALSE';
        die;
    }

    checkCPanelToObjectPrivileges($imei);

    $q = "UPDATE `gs_objects` SET `active`='true' WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    echo 'OK';
    die;
}

if (@$_POST['cmd'] == 'Activate_object_exceed') {
    $imei = $_POST["imei"];
    $_SESSION["username"];
    $username = $_SESSION["username"];
    date_default_timezone_set("Mexico/General");
    $dt_now = date("Y-m-d H:i:s");

    checkCPanelToObjectPrivileges($imei);

    $q1 = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
    $r1 = mysqli_query($ms, $q1);
    $row1 = mysqli_fetch_array($r1);
    $name = $row1['name'];
    $sim = $row1['sim_number'];

    $q = "UPDATE gs_objects o INNER JOIN gs_objects_reports r ON o.imei = r.imei SET  o.seguimiento = 'true', r.seguimiento = '" . $dt_now . "', r.usuario = '" . $username . "' WHERE o.imei ='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    echo 'OK';
    addRowBinnacle($_SESSION["user_id"], 'Seguimiento de equipo: ' . $name . " imei: " . $imei . " sim: " . $sim, $q);
    die;
}

if (@$_POST['cmd'] == 'Desactivate_object_exceed') {
    $imei = $_POST["imei"];

    checkCPanelToObjectPrivileges($imei);

    $q = "UPDATE `gs_objects` SET `seguimiento`='false' WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    echo 'OK';
    die;
}

if (isset($_POST['cmd']) && $_POST['cmd'] === 'save_event_data_crm') {
    $result = '';
    $attended_status = mysqli_real_escape_string($ms, $_POST["attended_status"]);
    $imei = mysqli_real_escape_string($ms, $_POST["imei"]);
    $detail = mysqli_real_escape_string($ms, $_POST["detail"]);
    $fecha_input = $_POST["fecha_service"];

    $fecha_service = date("Y-m-d H:i:s", strtotime($fecha_input . ' +6 hours'));

    $dt_now = gmdate("Y-m-d H:i:s");

    $q_check = "SELECT * FROM `gs_object_data_details` WHERE `imei` = '$imei'";
    $r_check = mysqli_query($ms, $q_check);

    if (!$r_check || mysqli_num_rows($r_check) == 0) {
        $q = "SELECT * FROM `gs_object_data` WHERE `imei` = '$imei' ORDER BY id DESC LIMIT 1";
        $r = mysqli_query($ms, $q);
        $row = mysqli_fetch_assoc($r);
        $previous_status = $row['attended'];
        $name = $row['name'];
        $event = $row['event'];
        $user_id = $row['user_id_'];
    } else {
        $row = mysqli_fetch_assoc($r_check);
        $previous_status = $row['attended_status'];
        $user_id = $row['user_id'];
        $event = $row['event'];
    }

    if ($previous_status === $attended_status) {
        $details = "<i>[ $attended_status ]</i><br/>$detail";
        $result = 'CHANGE_STATE';
        echo json_encode($result);
        exit;
    } else {
        $details = "<i>[ $previous_status -> $attended_status ]</i><br/>$detail";
    }
    if (gmdate("Y-m-d H", strtotime($fecha_service)) === gmdate("Y-m-d H")) {
        $result = 'SELECT_DIFERENT_DATE';
        echo json_encode($result);
        exit;
    }
    if (gmdate("Y-m-d", strtotime($fecha_service)) <= gmdate("Y-m-d")) {
        $result = 'DATE_INVALID';
        echo json_encode($result);
        exit;
    }

    $q1 = "SELECT * FROM `gs_object_data` WHERE `imei` = '$imei' ORDER BY id DESC LIMIT 1";
    $r1 = mysqli_query($ms, $q1);
    $row1 = mysqli_fetch_assoc($r1);
    $user_email = $row1['email_user'];
    $email_client = $row1['email_client'];

    $status = 'Seguimiento con Cliente';
    $fecha_insert = $dt_now;

    if ($attended_status === 'Agendar') {
        $attended_status = 'Agendado';
        $status = 'En agenda de servicios';
        $fecha_insert = $fecha_service;
    } elseif ($attended_status === 'Atendido') {
        $status = 'Servicio Atendido';
        $fecha_insert = $fecha_service;
    }

    $q_insert = "
        INSERT INTO `gs_object_data_details`
        (`imei`, `details`, `comment`, `event`, `fecha`, `user_id`, `attended_status`)
        VALUES ('$imei', '$details', '$detail', '$event', '$dt_now', '$user_id', '$attended_status')
    ";
    mysqli_query($ms, $q_insert);

    $q_update = "
        UPDATE `gs_object_data`
        SET `status` = '$status',
            `fecha_servicio` = '$fecha_service',
            `fecha_edit` = '$dt_now'
        WHERE `imei` = '$imei'
    ";
    mysqli_query($ms, $q_update);

    if (!$result) {
        $result = [
            'status' => 'OK',
            'event' => $event,
            'attended_status' => $attended_status,
            'detail' => $detail,
            'status_label' => $status,
            'fecha_edit' =>  $dt_now = date("d-m-Y H:i:s"),
            'fecha_servicio' =>  $fecha_service = date("d-m-Y H:i:s", strtotime($fecha_input . ' -0 hours')),
            'email_client' => $email_client,
            'user_email' => $user_email
        ];
    }

    echo json_encode($result);
    exit;
}


if (@$_POST['cmd'] == 'deactivate_object') {
    $imei = $_POST["imei"];

    $user = $_SESSION['user_id'];
    if (!checkCPanelToObjectUserPrivileges($user)) {
        echo 'FALSE';
        die;
    }

    checkCPanelToObjectPrivileges($imei);

    $q = "UPDATE `gs_objects` SET `active`='false' WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    echo 'OK';
    die;
}

if (@$_POST['cmd'] == 'activate_selected_objects') {
    $imeis = $_POST["imeis"];

    $user = $_SESSION['user_id'];
    if (!checkCPanelToObjectUserPrivileges($user)) {
        echo 'FALSE';
        die;
    }

    for ($i = 0; $i < count($imeis); ++$i) {
        $imei = $imeis[$i];

        checkCPanelToObjectPrivileges($imei);

        $q = "UPDATE `gs_objects` SET `active`='true' WHERE `imei`='" . $imei . "'";
        $r = mysqli_query($ms, $q);
    }

    echo 'OK';
    die;
}

if (@$_POST['cmd'] == 'deactivate_selected_objects') {
    $imeis = $_POST["imeis"];

    $user = $_SESSION['user_id'];
    if (!checkCPanelToObjectUserPrivileges($user)) {
        echo 'FALSE';
        die;
    }

    for ($i = 0; $i < count($imeis); ++$i) {
        $imei = $imeis[$i];

        checkCPanelToObjectPrivileges($imei);

        $q = "UPDATE `gs_objects` SET `active`='false' WHERE `imei`='" . $imei . "'";
        $r = mysqli_query($ms, $q);
    }

    echo 'OK';
    die;
}

if (@$_POST['cmd'] == 'delete_selected_objects') {
    $imeis = $_POST["imeis"];

    $user = $_SESSION['user_id'];
    if (!checkCPanelToObjectUserPrivileges($user)) {
        echo 'FALSE';
        die;
    }

    for ($i = 0; $i < count($imeis); ++$i) {
        $imei = $imeis[$i];

        checkCPanelToObjectPrivileges($imei);
        $q = "SELECT * FROM `gs_objects` WHERE `imei`='" . $imei . "'";
        $r = mysqli_query($ms, $q);
        $row = mysqli_fetch_array($r);
        $name = $row['name'];
        addRowBinnacle($_SESSION["user_id"], 'Baja de equipo:' . $name . " imei: " . $imei, $q);
        delObjectSystem($imei);
    }

    echo 'OK';
    die;
}

if (@$_POST['cmd'] == 'delete_unused_object') {
    $imei = $_POST['imei'];

    $q = "DELETE FROM `gs_objects_reports` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    echo 'OK';
    die;
}

if (@$_POST['cmd'] == 'delete_selected_unused_objects') {
    $imeis = $_POST["imeis"];

    for ($i = 0; $i < count($imeis); ++$i) {
        $imei = $imeis[$i];

        $q = "DELETE FROM `gs_objects_reports` WHERE `imei`='" . $imei . "'";
        $r = mysqli_query($ms, $q);
    }

    echo 'OK';
    die;
}

if (@$_GET['cmd'] == 'load_imei_device_list') {
    // tabla de dispositivos
    $page = $_GET['page']; // get the requested page
    $limit = $_GET['rows']; // get how many rows we want to have into the grid
    $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
    $sord = $_GET['sord']; // get the direction
    $search = caseToUpper(@$_GET['s']); // get search
    $manager_id = @$_GET['manager_id'];

    if (!$sidx) {
        $sidx = 1;
    }

    // check if admin or manager
    if (($_SESSION["cpanel_privileges"] == 'super_admin') || ($_SESSION["cpanel_privileges"] == 'admin')) {
        if ($manager_id == 0) {
            $q = "SELECT imei, protocol as marca, device as modelo, supplier as proveedor, rent_cost_device as costo, dt_purchase_device as fecha_compra , dt_income_device as fecha_alta FROM `gs_objects` WHERE 
    UPPER(`imei`)LIKE'%%'OR UPPER(`protocol`)LIKE'%%'OR UPPER(`device`)LIKE'%%'OR UPPER(`supplier`)LIKE'%%'OR UPPER(`rent_cost_device`)LIKE'%%'OR UPPER(`dt_purchase_device`)LIKE'%%'OR UPPER(`dt_income_device`) ";
        } else {
            $q = "SELECT imei, protocol as marca, device as modelo, supplier as proveedor, cost_device as costo, dt_purchase_device as fecha_compra , dt_income_device as fecha_alta FROM `gs_objects` WHERE `manager_id`='" . $manager_id . "' AND (UPPER(`imei`)LIKE'%%'OR UPPER(`protocol`)LIKE'%%'OR UPPER(`device`)LIKE'%%'OR UPPER(`supplier`)LIKE'%%'OR UPPER(`cost_device`)LIKE'%%'OR UPPER(`dt_purchase_device`)LIKE'%%'OR UPPER(`dt_income_device`) )";
        }
    } else {
        $q = "SELECT imei, protocol as marca, device as modelo, supplier as proveedor, cost_device as costo, dt_purchase_device as fecha_compra , dt_income_device a FROM `gs_objects` WHERE `manager_id`='" . $_SESSION["cpanel_manager_id"] . "' AND (UPPER(`imei`)LIKE'%%'OR UPPER(`protocol`)LIKE'%%'OR UPPER(`device`)LIKE'%%'OR UPPER(`supplier`)LIKE'%%'OR UPPER(`cost_device`)LIKE'%%'OR UPPER(`dt_purchase_device`)LIKE'%%'OR UPPER(`dt_income_device`) )";
    }
    $r = mysqli_query($ms, $q);
    $count = mysqli_num_rows($r);

    if ($count > 0) {
        $total_pages = ceil($count / $limit);
    } else {
        $total_pages = 1;
    }

    if ($page > $total_pages) {
        $page = $total_pages;
    }
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
            $imei = $row['imei'];

            $response->rows[$i]['id'] = $imei;

            // set modify buttons
            $modify = '<a href="#" onclick="deviceEdit(\'' . $imei . '\');" title="' . $la['EDIT'] . '"><img src="theme/images/edit.svg" /></a>';
            $modify .= '<a href="#" onclick="objectDelete(\'' . $imei . '\');" title="' . $la['DELETE'] . '"><img src="theme/images/remove3.svg" /></a>';


            $response->rows[$i]['cell'] = array($imei, $row['marca'], $row['modelo'], $row['proveedor'], $row['costo'], $row['fecha_compra'], $row['fecha_alta'], $modify);
            $i++;
        }
    }

    header('Content-type: application/json');
    echo json_encode($response);
    die;
}

if (@$_GET['cmd'] == 'load_imei_list') {
    // tabla de dispositivos
    $page = $_GET['page']; // get the requested page
    $limit = $_GET['rows']; // get how many rows we want to have into the grid
    $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
    $sord = $_GET['sord']; // get the direction
    $search = caseToUpper(@$_GET['s']); // get search
    $manager_id = @$_GET['manager_id'];

    if (!$sidx) {
        $sidx = 1;
    }

    // check if admin or manager
    if (($_SESSION["cpanel_privileges"] == 'super_admin') || ($_SESSION["cpanel_privileges"] == 'admin')) {
        if ($manager_id == 0) {
            $q = "SELECT imei, sim_number as numero_linea, sim_iccid as iccid, plan, rent_cost_device as renta_costo, sim_number_company, supplier as proveedor, dt_closing_date as fecha_corte, dt_purchase_device as fecha_compra , dt_income_device as fecha_alta FROM `gs_objects` WHERE 
    UPPER(`sim_number`) LIKE'%%'OR UPPER(`sim_iccid`)LIKE'%%'OR UPPER(`plan`)LIKE'%%'OR UPPER(`sim_number_company`)LIKE'%%'OR UPPER(`supplier`)LIKE'%%'OR UPPER(`dt_purchase_device`)LIKE'%%'OR UPPER(`dt_income_device`) OR UPPER(`dt_closing_date`)";
        } else {
            $q = "SELECT imei, sim_number as numero_linea, sim_iccid as iccid, plan, rent_cost_device as renta_costo, sim_number_company, supplier as proveedor, dt_closing_date as fecha_corte, dt_purchase_device as fecha_compra , dt_income_device as fecha_alta FROM `gs_objects` WHERE `manager_id`='" . $manager_id . "' AND (UPPER(`sim_number`) LIKE'%%'OR UPPER(`sim_iccid`)LIKE'%%'OR UPPER(`plan`)LIKE'%%'OR UPPER(`sim_number_company`)LIKE'%%'OR UPPER(`supplier`)LIKE'%%'OR UPPER(`dt_purchase_device`)LIKE'%%'OR UPPER(`dt_income_device`) OR UPPER(`dt_closing_date`) )";
        }
    } else {
        $q = "SELECT imei, sim_number as numero_linea, sim_iccid as iccid, plan, rent_cost_device as renta_costo, sim_number_company, supplier as proveedor, dt_closing_date as fecha_corte, dt_purchase_device as fecha_compra , dt_income_device as fecha_alta FROM `gs_objects` WHERE `manager_id`='" . $_SESSION["cpanel_manager_id"] . "' AND (UPPER(`sim_number`) LIKE'%%'OR UPPER(`sim_iccid`)LIKE'%%'OR UPPER(`plan`)LIKE'%%'OR UPPER(`sim_number_company`)LIKE'%%'OR UPPER(`supplier`)LIKE'%%'OR UPPER(`dt_purchase_device`)LIKE'%%'OR UPPER(`dt_income_device`) OR UPPER(`dt_closing_date`) )";
    }
    $r = mysqli_query($ms, $q);
    $count = mysqli_num_rows($r);

    if ($count > 0) {
        $total_pages = ceil($count / $limit);
    } else {
        $total_pages = 1;
    }

    if ($page > $total_pages) {
        $page = $total_pages;
    }
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
            $imei = $row['imei'];

            $response->rows[$i]['id'] = $imei;

            // set modify buttons
            $modify = '<a href="#" onclick="imeiEdit(\'' . $imei . '\');" title="' . $la['EDIT'] . '"><img src="theme/images/edit.svg" /></a>';
            $modify .= '<a href="#" onclick="objectDelete(\'' . $imei . '\');" title="' . $la['DELETE'] . '"><img src="theme/images/remove3.svg" /></a>';


            $response->rows[$i]['cell'] = array($imei, $row['numero_linea'], $row['iccid'], $row['plan'], $row['renta_costo'], $row['sim_number_company'], $row['proveedor'], $row['fecha_corte'], $row['fecha_compra'], $row['fecha_alta'], $modify);
            $i++;
        }
    }

    header('Content-type: application/json');
    echo json_encode($response);
    die;
}


if (@$_POST['cmd'] == 'load_imei_data') {
    $imei = $_POST['imei'];

    checkCPanelToObjectPrivileges($imei);

    $q = "SELECT imei, sim_number as numero_linea, sim_iccid as iccid, plan, rent_cost_device as renta_costo, sim_number_company, supplier as proveedor, dt_closing_date as fecha_corte, dt_purchase_device as fecha_compra , dt_income_device as fecha_alta FROM `gs_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    $query = "SELECT * FROM gs_object_observations goo WHERE imei = '" . $imei . "'";
    $response = CONSULTAR($query, $conexion_share);

    $result = array(
        'imei' => $row["imei"],
        'numero_linea' => $row['numero_linea'],
        'iccid' => $row['iccid'],
        'plan' => $row['plan'],
        'renta_costo' => $row['renta_costo'],
        'sim_number_company' => $row['sim_number_company'],
        'proveedor' => $row['proveedor'],
        'fecha_corte' => $row['fecha_corte'],
        'fecha_compra' => $row['fecha_compra'],
        'fecha_alta' => $row['fecha_alta']

    );
    addRowBinnacle($_SESSION["user_id"], 'Edición de imei:' . json_encode($row), $q);
    echo json_encode($result);
    die;
}

if (@$_POST['cmd'] == 'edit_imei') {
    $imei = strtoupper($_POST['imei']);
    $sim_number = $_POST['iccid'];
    $sim_iccid = $_POST['numero_linea'];
    $plan = $_POST['plan'];
    $rent_cost_device = $_POST['renta_costo'];
    $sim_number_company = $_POST['sim_number_company'];
    $supplier = $_POST['proveedor'];
    $dt_closing_date = $_POST['fecha_corte'];
    $dt_purchase_device = $_POST['fecha_compra'];
    $dt_income_device = $_POST['fecha_alta'];


    checkCPanelToObjectPrivileges($imei);
    $user = $_SESSION['user_id'];

    if (!checkCPanelToObjectUserPrivileges($user)) {
        echo 'FALSE';
        die;
    }

    $q = "UPDATE `gs_objects` SET 	
                        sim_number='" . $sim_number . "', 
                        sim_iccid='" . $sim_iccid . "', 
                        plan='" . $plan . "', 
                        rent_cost_device='" . $rent_cost_device . "', 
                        sim_number_company='" . $sim_number_company . "', 
                        supplier='" . $supplier . "', 
                        dt_closing_date='" . $dt_closing_date . "', 
                        dt_purchase_device='" . $dt_purchase_device . "', 
                        dt_income_device='" . $dt_income_device . "'						
                        WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    if ($rent_cost_device) {

        $usuario =  $_SESSION["username"];
        date_default_timezone_set("Mexico/General");
        $dt_now = date("Y-m-d H:i:s");

        $query = "UPDATE gs_object_observations
    SET
      fecha_modificacion = '$dt_now',
      fecha_alta = '$dt_income_device',
      renta = '$rent_cost_device',
      usuario_modificacion = '$usuario'
    WHERE imei = '$imei'";

        $r = mysqli_query($ms, $query);
        CONSULTAR($query, $conexion_share);
    }

    $users = array();
    // get object group, driver and trailer settings (we do not want to to lose them)
    $gs_user_objects = array();

    $q = "SELECT * FROM `gs_user_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    while ($row = mysqli_fetch_array($r)) {
        $users[] = $row['user_id'];
        $gs_user_objects[] = $row;
    }

    // delete object from all users
    $q = "DELETE FROM `gs_user_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    echo 'OK';
    die;
}

if (@$_POST['cmd'] == 'load_device_data') {
    $imei = $_POST['imei'];

    checkCPanelToObjectPrivileges($imei);

    $q = "SELECT imei, protocol as marca, device as modelo, supplier as proveedor, rent_cost_device as costo, dt_purchase_device as fecha_compra , dt_income_device as fecha_alta FROM `gs_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);
    $row = mysqli_fetch_array($r);

    $query = "SELECT * FROM gs_object_observations goo WHERE imei = '" . $imei . "'";
    $response = CONSULTAR($query, $conexion_share);

    $result = array(
        'imei' => $row["imei"],
        'marca' => $row['marca'],
        'modelo' => $row['modelo'],
        'proveedor' => $row['proveedor'],
        'renta_costo' => $row['costo'],
        'fecha_compra' => $row['fecha_compra'],
        'fecha_alta' => $row['fecha_alta'],

    );
    addRowBinnacle($_SESSION["user_id"], 'Edición de imei:' . json_encode($row), $q);
    echo json_encode($result);
    die;
}

if (@$_POST['cmd'] == 'edit_device') {
    $imei = strtoupper($_POST['imei']);
    $protocol = $_POST['marca'];
    $device = $_POST['modelo'];
    $supplier = $_POST['proveedor'];
    $rent_cost_device = $_POST['renta_costo'];
    $dt_purchase_device = $_POST['fecha_compra'];
    $dt_income_device = $_POST['fecha_alta'];

    checkCPanelToObjectPrivileges($imei);
    $user = $_SESSION['user_id'];

    if (!checkCPanelToObjectUserPrivileges($user)) {
        echo 'FALSE';
        die;
    }

    $q = "UPDATE `gs_objects` SET 	
                        protocol='" . $protocol . "', 
                        device='" . $device . "', 
                        supplier='" . $supplier . "', 
                        rent_cost_device='" . $rent_cost_device . "', 
                        dt_purchase_device='" . $dt_purchase_device . "', 
                        dt_income_device='" . $dt_income_device . "'						
                        WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    if ($rent_cost_device) {

        $usuario =  $_SESSION["username"];
        date_default_timezone_set("Mexico/General");
        $dt_now = date("Y-m-d H:i:s");

        $query = "UPDATE gs_object_observations
    SET
      fecha_modificacion = '$dt_now',
      fecha_alta = '$dt_income_device',
      renta = '$rent_cost_device',
      usuario_modificacion = '$usuario'
    WHERE imei = '$imei'";

        $r = mysqli_query($ms, $query);
        CONSULTAR($query, $conexion_share);
    }

    $users = array();
    // get object group, driver and trailer settings (we do not want to to lose them)
    $gs_user_objects = array();

    $q = "SELECT * FROM `gs_user_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);

    while ($row = mysqli_fetch_array($r)) {
        $users[] = $row['user_id'];
        $gs_user_objects[] = $row;
    }

    // delete object from all users
    $q = "DELETE FROM `gs_user_objects` WHERE `imei`='" . $imei . "'";
    $r = mysqli_query($ms, $q);


    $usuario =  $_SESSION["username"];
    $observacion = '';

    date_default_timezone_set("Mexico/General");
    $dt_now = date("Y-m-d H:i:s");

    echo 'OK';
    die;
}
