<?php


function enviaDataToLALA($loc, $user, $pass, $economico)
{

    $file = fopen("/var/www/html/ws-logs/log_Lala_" . date("d-m-Y") . ".txt", "a");

    global $ms;

    $credentials = array(
        "strLogin" => $user,
        "strPassword" => $pass,
        "intLang" => 1
    );

    $serviceURL = "http://locationreporter.shareservice.co/API/rest/LoginUserOauth";
    $method = "GET";
    $sendData = new sendDataWS($serviceURL, $method);
    $credentials_auth = $sendData->getObjectAuthLala(json_encode($credentials));
    $credentials_obj = json_decode($credentials_auth, true);

    if ($credentials_obj['Err']['Code'] == 110) { //Sigue vigente el token... dura 2 hrs (pufff)
        $credentials_obj = array(
            "AuthObj" => array(
                "Err" => array(
                    "Code" => 0,
                    "Desc" => "OK"
                ),
                "ExpirationDate" => $credentials_obj['ExpirationDate'],
                "Sign" => $credentials_obj['Sign'],
                "Token" => $credentials_obj['Token']
            )
        );
    } else {
        $credentials_obj = array(
            "AuthObj" => $credentials_obj
        );
    }


    $q2 = "SELECT * FROM `gs_objects` WHERE `imei`='" . $loc['imei'] . "'";
    $r2 = mysqli_query($ms, $q2);
    $row2 = mysqli_fetch_array($r2);

    $dataimei = $loc['imei'];
    $q2MsgSequence = "SELECT COUNT(*) as contador FROM gs_object_data_" . $dataimei;
    $r2MsgSequence = mysqli_query($ms, $q2MsgSequence);
    $MsgSequence = mysqli_fetch_assoc($r2MsgSequence);


    switch ($loc['params']['type']) {

        case 'SOS':
            $codigo = '11';
            break;

        case 'PNA':
            $codigo = '1';
            break;

        case 'PFA':
            $codigo = '207';
            break;

        case 'BPL':
            $codigo = '81';
            break;

        case 'STC':
            $codigo = '88';
            break;

        case 'IGN':
            if ($loc['params']['ignition'] == "1") {
                $codigo = '15';
            }
            break;

        case 'IGL':
            if ($loc['params']['ignition'] == "0") {
                $codigo = '14';
            }
            break;

        case 'HBM':
            if ($loc['params']['alarm'] == "hardBraking") {
                $codigo = '121';
            } else if ($loc['params']['alarm'] == "hardAcceleration") {
                $codigo = '122';
            }
            break;


        case 'FRI':
            if ($loc['params']['output'] == "0" && $loc['params']['motion'] == "1") {
                $codigo = '16';
            } else if ($loc['params']['output'] == "1" && $loc['params']['motion'] == "1") {
                $codigo = '442';
            } else if ($loc['params']['output'] == "0" && $loc['result'] == "Command OUT accepted") {
                $codigo = '440';
            } else if ($loc['params']['output'] == "1" && $loc['result'] == "Command OUT accepted") {
                $codigo = '441';
            }
            break;


        default:
            $codigo = 133;
            break;
    }


    $posRaw = array(
        "objLocReporter" => array(
            "strGpsID"         => $loc['imei'],
            "strAlias"         => $economico,
            "intEvent"         => $codigo ?? 133,
            "intGpsDate"       => castDateSQL_UNIX_LALA($loc['dt_tracker']),
            "intServerDate"    => castDateSQL_UNIX_LALA($loc['dt_server']),
            "dbLatitude"       => (float)$loc['lat'],
            "dbLongitude"      => (float)$loc['lng'],
            "dbAltitude"       => (float)$loc['altitude'],
            "intCourse"        => (int)$loc['angle'],
            "dbSpeed"          => (float)$loc['speed'],
            "dbOdometer"       => (float)$loc['params']['odometer'],
            "strLocation"      => getGeocoderCache($row2['lat'], $row2['lng']),
            "intMsgSequence"   => (int)$MsgSequence['contador'],
            "intGpsFix"        => "3",
            "intSatellites"    => "5",
            "intHDOP"          => (int)$loc['params']['hdop'],
            "intInputStatus"   => (int)$loc['params']['input'],
            "intOutputStatus"  => (int)$loc['params']['output'],
            "strDriverID"      => "",
            "strGenericData"   => "",
            "dbBatteryLevel"   => isset($loc['params']['batteryLevel']) ? intval($loc['params']['batteryLevel']) : 0
        )
    );

    $dataPosition = array_merge($credentials_obj, $posRaw);

    $serviceURL = "http://locationreporter.shareservice.co/API/rest/SendLocation";
    $method = "POST";
    $sendDataPos = new sendDataWS($serviceURL, $method);

    $mensaje = $sendDataPos->setPosicionLala(json_encode($dataPosition));

    $payload = array(
        'ws_name' => 'BAFAR',
        'msg' => '',
        'response' => $mensaje,
        'parameter' => $posRaw
    );

    fwrite($file, print_r(json_encode($payload), true));
    return json_encode($payload);
}

function castDateSQL_T_LALA($date)
{
    return str_replace(' ', 'T', trim($date));
}

function castDateSQL_UNIX_LALA($date)
{
    return strtotime($date);
}
