<?php


function castDateSQL_T_Guda($date)
{
    return str_replace(' ', 'T', trim($date));
}

function castDateSQL_UNIX_Guda($date)
{
    return strtotime($date);
}

function envioGuda1($loc, $user, $pass, $placa)
{

    $wsdl = 'http://54.190.237.12/ws/API?wsdl';

    $soap = new SoapClient($wsdl);


    $response = $soap->LoginUser(array('strLogin' => $user, 'strPassword' => $pass, 'intLang' => 1));

    $file = fopen("/var/www/html/ws-logs/log_Guda_".date("d-m-Y").".txt", "a");

    $AuthObj = array(
        'Err' => $response->LoginUserResult->Err,
        'ExpirationDate' => $response->LoginUserResult->ExpirationDate,
        'Sign' => $response->LoginUserResult->Sign,
        'Token' => $response->LoginUserResult->Token);

    $LocRep = array(
        'strGpsID' => $loc['imei'],
        'strAlias' => $placa,
        'intEvent' => 1,
        'intGpsDate' => castDateSQL_UNIX_Guda($loc['dt_tracker']),
        'intServerDate' => castDateSQL_UNIX_Guda($loc['dt_server']),
        'dbLatitude' => (double)$loc['lat'],
        'dbLongitude' => (double)$loc['lng'],
        'dbAltitude' => (double)$loc['altitude'],
        'intCourse' => (int)$loc['angle'],
        'dbSpeed' => (double)$loc['speed'],
        'dbOdometer' => (double)1.0,
        'strLocation' => 'string',
        'intMsgSequence' => (int)1,
        'intGpsFix' => (int)1, /* [0, 1, 2, 3, 4, 5, 6] */
        'intSatellites' => (int)3,
        'intHDOP' => (int)1, /* [1, ... 50] */
        'intInputStatus' => 0,//(int)$loc['params']['input'],
        'intOutputStatus' => 0,//(int)$loc['params']['output'],
        'strDriverID' => 'string',
        'strGenericData' => 'string',
        'dbBatteryLevel' => isset($loc['params']['batteryLevel']) ? intval($loc['params']['batteryLevel']) : 0,
        'strNameProvider' => 'string');


    $responseLocation = $soap->SendLocation(array('AuthObj' => $AuthObj, 'objLocReporter' => $LocRep));

    $payload = array(
        'ws_name' => 'Guda',
        'msg' => '',
        'response' => $responseLocation,
        'parameters' =>$LocRep
    );

	fwrite($file, print_r(json_encode($payload), true));

    return json_encode($payload);

}

$dummyLoc = array(
    'imei' => '867162029153038',
    'protocol' => 'gl200',
    'dt_server' => '2020-08-17 19:56:25',
    'dt_tracker' => '2020-08-17T19:54:32',
    'lat' => '20.125278',
    'lng' => '-98.11349',
    'altitude' => 0,
    'angle' => 0,
    'speed' => 0,
    'loc_valid' => 1,
    'params' => array
    (
        'hdop' => 1,
        'batteryLevel' => 86,
        'ignition' => 0,
        'input' => 0,
        'output' => 0,
        'type' => 'FRI',
        'distance' => 0,
        'totalDistance' => '5358133.37',
        'motion' => '',
        'hours' => '364990000'
    ),

    'event' => '',
    'net_protocol' => '',
    'ip' => '',
    'port' => ''
);

?>