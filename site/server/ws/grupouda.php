<?php



function envioGrupoUda($loc, $user, $pass, $placa, $odometer)
{

    $wsdl = 'http://terceros.grupouda.com.mx/ws/API?wsdl';

    $soap = new SoapClient($wsdl);


    $response = $soap->LoginUser(array('strLogin' => $user, 'strPassword' => $pass, 'intLang' => 1));

    $file = fopen("/var/www/html/ws-logs/log_GrupoUda_".date("d-m-Y").".txt", "a");

    $AuthObj = array(
        'Err' => $response->LoginUserResult->Err,
        'ExpirationDate' => $response->LoginUserResult->ExpirationDate,
        'Sign' => $response->LoginUserResult->Sign,
        'Token' => $response->LoginUserResult->Token);


    $codigo = 18;

    switch ($loc['params']['type']) {

        case 'PNA':
            $codigo = '1';
            break;

        case 'SOS':
            $codigo = '11';
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

        case 'BPL':
            $codigo = '20';
            break;

        case 'MPN':
            $codigo = '88';
            break;

        case 'PFA':
            $codigo = '207';
            break;

        case 'JDS':
            $codigo = '450';
            break;

        case 'HBM':
            if ($loc['params']['alarm'] == "hardBraking") {
                $codigo = '122';
            } else if ($loc['params']['alarm'] == "hardAcceleration") {
                $codigo = '121';
            }
            break;

        case 'FRI':
            if ($loc['params']['output'] == "1" && $loc['params']['motion'] == "0") {
                $codigo = '451';
            }
            else if ($loc['params']['power'] <= "11") {
                $codigo = '81';
            }
            break;
    }

    if ($_GET['valid'] == "false"){
        $codigo = '106';
    }

    $LocRep = array(
        'strGpsID' => $loc['imei'],
        'strAlias' => $placa,
        'intEvent' => $codigo,
        'intGpsDate' => castDateSQL_UNIX_Guda($loc['dt_tracker']),
        'intServerDate' => castDateSQL_UNIX_Guda($loc['dt_server']),
        'dbLatitude' => (double)$loc['lat'],
        'dbLongitude' => (double)$loc['lng'],
        'dbAltitude' => (double)$loc['altitude'],
        'intCourse' => (int)$loc['angle'],
        'dbSpeed' => (double)$loc['speed'],
        'dbOdometer' => $odometer,
        'strLocation' => getGeocoderCache($loc['lat'], $loc['lng']),
        'intMsgSequence' => (int)1,
        'intGpsFix' => (int)1, /* [0, 1, 2, 3, 4, 5, 6] */
        'intSatellites' => (int)3,
        'intHDOP' => (int)1, /* [0, 1, 2, 3, 4, 5, 6] */
        'intInputStatus' => 0,//(int)$loc['params']['input'],
        'intOutputStatus' => 0,//(int)$loc['params']['output'],
        'strDriverID' => 'string',
        'strGenericData' => 'string',
        'dbBatteryLevel' => isset($loc['params']['batteryLevel']) ? intval($loc['params']['batteryLevel']) : 0,);


    $responseLocation = $soap->SendLocation(array('AuthObj' => $AuthObj, 'objLocReporter' => $LocRep));

    $payload = array(
        'ws_name' => 'GrupoUda',
        'msg' => '',
        'response' => $responseLocation,
        'parameters' =>$LocRep
    );

	fwrite($file, print_r(json_encode($payload), true));

    return json_encode($payload);

}

?>