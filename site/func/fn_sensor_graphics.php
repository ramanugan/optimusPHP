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

if (@$_POST['cmd'] == 'data_sensors_graphics') {

    $imei = $_POST['imei'];
    $accuracy = getObjectAccuracy($imei);
    $dtf = convUserUTCTimezone($_POST['dtf']);
    $dtt = convUserUTCTimezone($_POST['dtt']);

    $result = array();
    $params_prev = array();
    $formula = array();    

    $q = "SELECT DISTINCT	dt_tracker,
					lat,
					lng,
					altitude,
					angle,
					speed,
					params
					FROM `gs_object_data_" . $imei . "` WHERE dt_tracker BETWEEN '" . $dtf . "' AND '" . $dtt . "' ORDER BY dt_tracker ASC";

    $r = mysqli_query($ms, $q);

    // Si esta apagado hay que ignorar los sensores
    $ignore_sensors = array();
    $acc_sensor = getSensorFromType($imei, 'acc');

    if ($acc_sensor) {
        $sensors = getObjectSensors($imei);

        foreach ($sensors as $key => $value) {
            if ($value['acc_ignore'] == 'true') {
                $ignore_sensors[] = $value;
            }
        }
    }

    while ($result_data = mysqli_fetch_array($r)) {
        $dt_tracker = convUserTimezone($result_data['dt_tracker']);
        $lat = $result_data['lat'];
        $lng = $result_data['lng'];
        $altitude = $result_data['altitude'];
        $angle = $result_data['angle'];
        $speed = $result_data['speed'];

        $params = json_decode($result_data['params'], true);

        // Si esta apagado hay que ignorar los sensores
        if ($acc_sensor) {
            foreach ($ignore_sensors as $key => $value) {
                $sensor_data = getSensorValue($params, $acc_sensor[0]);

                if ($sensor_data['value'] == 0) {
                    if (isset($params_prev[$value['param']])) {
                        $params[$value['param']] = $params_prev[$value['param']];
                    }
                }
            }
        }

        $params = mergeParams($params_prev, $params);

        if (!isset($_SESSION["unit_distance"])) {
            $_SESSION["unit_distance"] = 'km';
        }

        $speed = convSpeedUnits($speed, 'km', $_SESSION["unit_distance"]);
        $altitude = convAltitudeUnits($altitude, 'km', $_SESSION["unit_distance"]);

        if (isset($params['gpslev']) && ($accuracy['use_gpslev'] == true)) {
            $gpslev = $params['gpslev'];
            $min_gpslev = $accuracy['min_gpslev'];
        } else {
            $gpslev = 0;
            $min_gpslev = 0;
        }

        if (isset($params['hdop']) && ($accuracy['use_hdop'] == true)) {
            $hdop = $params['hdop'];
            $max_hdop = $accuracy['max_hdop'];
        } else {
            $hdop = 0;
            $max_hdop = 0;
        }

        if (($gpslev >= $min_gpslev) && ($hdop <= $max_hdop)) {

            if (($lat != 0) && ($lng != 0)) {
                $result[] = array(
                    $dt_tracker,
                    $lat,
                    $lng,
                    $altitude,
                    $angle,
                    $speed,
                    $params
                );
            }
        }

        // store prev params
        $params_prev = $params;
    }

    // get fuel sensors
    $fuel_sensors = getSensorFromType($imei, 'fuel');
    // Algoritmo de Suavizado GR
    if ($fuel_sensors != false) {
        // guardamos la formula que convierte el voltaje en litros
        if ($fuel_sensors != false) {
            array_map(function ($sensor) use (&$formula) {
                if ($sensor['param'] == 'adc1') {
                    $formula['adc1'] = $sensor['formula'];
                } else if ($sensor['param'] == 'adc2') {
                    $formula['adc2'] = $sensor['formula'];
                } else if ($sensor['param'] == 'adc3') {
                    $formula['adc3'] = $sensor['formula'];
                } else if ($sensor['param'] == 'adc4') {
                    $formula['adc4'] = $sensor['formula'];
                } else if ($sensor['param'] == 'AdcBT1') {
                    $formula['AdcBT1'] = $sensor['formula'];
                } else if ($sensor['param'] == 'AdcBT2') {
                    $formula['AdcBT2'] = $sensor['formula'];
                }
            }, $fuel_sensors);
        }

        for ($i = 0; $i < count($fuel_sensors); ++$i) {
            $param = $fuel_sensors[$i]['param'];

            for ($j = 0; $j < count($result); ++$j) {
                $prev = isset($result[$j - 1]) ? getParamValue($result[$j - 1][6], $param) : false;
                $curr = isset($result[$j]) ? getParamValue($result[$j][6], $param) : false;
                $next = isset($result[$j + 1]) ? getParamValue($result[$j + 1][6], $param) : false;

                if (($prev > $curr) && ($curr < $next)) {
                    if (isset($result[$j])) {
                        if (isset($result[$j][6][$param])) {
                            $result[$j][6][$param] = $next;
                        }
                    }
                }
            }
        }
    }
    $result = array (
        'formula_fuel' => $formula,
        'history' => $result 
    );

    echo json_encode($result);
    die;
}

if (@$_POST['cmd'] == 'data_sensors_graphics_bluetooth') {

    $imei = $_POST['imei'];
    $accuracy = getObjectAccuracy($imei);
    $dtf = convUserUTCTimezone($_POST['dtf']);
    $dtt = convUserUTCTimezone($_POST['dtt']);

    $result_tramas = array();
    $params_prev = array();
    $formula = array();    

    $q = "SELECT DISTINCT	dt_tracker,	params
					FROM `gs_object_data_" . $imei . "` WHERE dt_tracker BETWEEN '" . $dtf . "' AND '" . $dtt . "' ORDER BY dt_tracker ASC";

    $r = mysqli_query($ms, $q);

    // Si esta apagado hay que ignorar los sensores
    $ignore_sensors = array();
    $acc_sensor = getSensorFromType($imei, 'acc');

    if ($acc_sensor) {
        $sensors = getObjectSensors($imei);

        foreach ($sensors as $key => $value) {
            if ($value['acc_ignore'] == 'true') {
                $ignore_sensors[] = $value;
            }
        }
    }

    while ($result_data = mysqli_fetch_array($r)) {
        $dt_tracker = convUserTimezone($result_data['dt_tracker']);
        
        $params = json_decode($result_data['params'], true);
        // Eliminamos los parametros que no son necesarios para el grafico
        unset($params['batteryLevel']);
        unset($params['distance']);
        unset($params['event']);
        unset($params['hdop']);
        unset($params['hours']);
        unset($params['ignition']);
        unset($params['motion']);
        unset($params['pdop']);
        unset($params['power']);
        unset($params['priority']);
        unset($params['raw']);
        unset($params['rssi']);
        unset($params['sat']);
        unset($params['totalDistance']);

        // Si esta apagado hay que ignorar los sensores
        if ($acc_sensor) {
            foreach ($ignore_sensors as $key => $value) {
                $sensor_data = getSensorValue($params, $acc_sensor[0]);

                if ($sensor_data['value'] == 0) {
                    if (isset($params_prev[$value['param']])) {
                        $params[$value['param']] = $params_prev[$value['param']];
                    }
                }
            }
        }

        $params = mergeParams($params_prev, $params);
                $result_tramas[] = array(
                    $dt_tracker,
                    $params
                );

        // store prev params
        $params_prev = $params;
    }

    $sensors_bluetooth = getSensorFromType($imei, 'temp'); //temperature

    if ($sensors_bluetooth != false) {
        if ($sensors_bluetooth != false) {
            array_map(function ($sensor) use (&$formula) {
                if ($sensor['param'] == 'TempBT1') {
                    $formula['TempBT1'] = $sensor['formula'];
                } else if ($sensor['param'] == 'TempBT2') {
                    $formula['TempBT2'] = $sensor['formula'];
                } else if ($sensor['param'] == 'TempBT3') {
                    $formula['TempBT3'] = $sensor['formula'];
                } else if ($sensor['param'] == 'temp1') {
                    $formula['temp1'] = $sensor['formula'];
                } 
            }, $sensors_bluetooth);
        }
    }

    $sensors_bluetooth_cust = getSensorFromType($imei, 'cust'); // humidity
    if ($sensors_bluetooth_cust != false) {
        if ($sensors_bluetooth_cust != false) {
            array_map(function ($sensor) use (&$formula) {
                if ($sensor['param'] == 'HumBT1') {
                    $formula['HumBT1'] = $sensor['formula'];
                } else if ($sensor['param'] == 'HumBT2') {
                    $formula['HumBT2'] = $sensor['formula'];
                } else if ($sensor['param'] == 'HumBT3') {
                    $formula['HumBT3'] = $sensor['formula'];
                }
            }, $sensors_bluetooth_cust);
        }
    }

    foreach ($result_tramas as &$trama) { // using formula to calculate the value : temperature and humidity
        if (isset($trama[1]['TempBT1'])) { //contains temperature 
            if (isset($formula['TempBT1']) && $formula['TempBT1'] != null) {
                $equation = $formula['TempBT1'];
                $variables = array('x' => $trama[1]['TempBT1']);
                $new_value = evaluateEquation($equation, $variables);
                $trama[1]['TempBT1'] = $new_value;
            }
        } else {
            $trama[1]['TempBT1'] = null;
        }

        if (isset($trama[1]['TempBT2'])) {
            if (isset($formula['TempBT2']) && $formula['TempBT2'] != null) {
                $equation = $formula['TempBT2'];
                $variables = array('x' => $trama[1]['TempBT2']);
                $new_value = evaluateEquation($equation, $variables);
                $trama[1]['TempBT2'] = $new_value;
            }
        } else {
            $trama[1]['TempBT2'] = null;
        }

        if (isset($trama[1]['TempBT3'])) {
            if (isset($formula['TempBT3']) && $formula['TempBT3'] != null) {
                $equation = $formula['TempBT3'];
                $variables = array('x' => $trama[1]['TempBT3']);
                $new_value = evaluateEquation($equation, $variables);
                $trama[1]['TempBT3'] = $new_value;
            }
        } else {
            $trama[1]['TempBT3'] = null;
        }

        if (isset($trama[1]['temp1'])) {
            if (isset($formula['temp1']) && $formula['temp1'] != null) {
                $equation = $formula['temp1'];
                $variables = array('x' => $trama[1]['temp1']);
                $new_value = evaluateEquation($equation, $variables);
                $trama[1]['temp1'] = $new_value;
            }
        } else {
            $trama[1]['temp1'] = null;
        }

        if (isset($trama[1]['HumBT1'])) { //contains humidity
            if (isset($formula['HumBT1']) && $formula['HumBT1'] != null) {
                $equation = $formula['HumBT1'];
                $variables = array('x' => $trama[1]['HumBT1']);
                $new_value = evaluateEquation($equation, $variables);
                $trama[1]['HumBT1'] = $new_value;
            }
        } else {
            $trama[1]['HumBT1'] = null;
        }

        if (isset($trama[1]['HumBT2'])) {
            if (isset($formula['HumBT2']) && $formula['HumBT2'] != null) {
                $equation = $formula['HumBT2'];
                $variables = array('x' => $trama[1]['HumBT2']);
                $new_value = evaluateEquation($equation, $variables);
                $trama[1]['HumBT2'] = $new_value;
            }
        } else {
            $trama[1]['HumBT2'] = null;
        }

        if (isset($trama[1]['HumBT3'])) {
            if (isset($formula['HumBT3']) && $formula['HumBT3'] != null) {
                $equation = $formula['HumBT3'];
                $variables = array('x' => $trama[1]['HumBT3']);
                $new_value = evaluateEquation($equation, $variables);
                $trama[1]['HumBT3'] = $new_value;
            }
        } else {
            $trama[1]['HumBT3'] = null;
        }
    }

    echo json_encode($result_tramas);
    die;
}
