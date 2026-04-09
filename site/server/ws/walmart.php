<?php

class CustomerWalmart
{
    var $id;          //    Identidicador       A0
    var $name;        //    Nombre              A0
    //var $id = 'A12';                    //    Identidicador       A0
    //var $name = 'Guadalupe';            //    Nombre              A0
}

class EventWalmart
{
    function __construct($comp)
    {
        $this->customer = $comp;
    }

    var $code;        // ** Código              [0, 911, 912]
    var $date;        // ** Fecha/Hora          2020-02-20T13:15:22
    var $latitude;    // ** Latitud             28.2882
    var $longitude;   // ** Longitud            -105.5069
    var $asset;       // ** Placa               123ABC

    var $serialNumber;//    Número              A0
    var $direction;   //    Dirección           A0
    var $speed;       // ** Velocidad           170
    var $altitude;    //    Altitud             99
    var $customer;    //    OBJETO

    var $shipment;    // ** Shipment            A0
    var $odometer;    //    Odómetro            99
    var $ignition;    //    Ignición            True/False
    var $battery;     //    Batería             99
    var $course;      //    Curso               ['N ', 'NE', 'E', 'SE', 'S', 'SO', 'O', 'NO']
}


function castDateSQL_T($date)
{
    return str_replace(' ', 'T', trim($date));
}

function envioWalmart($loc, $user, $pass, $id, $name, $placa)
{

    $file = fopen("/var/www/html/ws-logs/log_Walmart_".date("d-m-Y").".txt", "a");

    $wsdl = 'http://gps.rcontrol.com.mx/Tracking/wcf/RCService.svc?wsdl';
    // $user = 'wm_10066_OPTIMUS';
    // $pass = 'DZTo/!046urqB/!3';

    $angle = $loc['angle'];
    $orientation = "";
    if ($angle > 0 and $angle < 90) {
        $orientation = "NO";
    } else if ($angle > 90 and $angle < 180) {
        $orientation = "NE";
    } else if ($angle > 180 and $angle < 270) {
        $orientation = "SE";
    } else if ($angle > 270 and $angle < 369) {
        $orientation = "SO";
    } else if ($angle == 0) {
        $orientation = "O";
    } else if ($angle == 90) {
        $orientation = "N";
    } else if ($angle == 180) {
        $orientation = "E";
    } else if ($angle == 0) {
        $orientation = "S";
    }

    $soap = new SoapClient($wsdl, array('classmap' => array('Event' => 'Event', 'Customer' => 'Customer')));

    $response = $soap->GetUserToken(array('userId' => $user, 'password' => $pass));
    $token = $response->GetUserTokenResult->token;


    $CustomerType = new CustomerWalmart();

    $CustomerType->id = $id;
    $CustomerType->name = $name;


    $EventType = new EventWalmart($CustomerType);

    $EventType->code = '0';
    $EventType->date = castDateSQL_T($loc['dt_tracker']);
    $EventType->latitude = (double)$loc['lat'];
    $EventType->longitude = (double)$loc['lng'];
    $EventType->asset = (empty($placa) ? 0 : $placa);
    $EventType->serialNumber = $loc['imei'];
    $EventType->direction = '';
    $EventType->speed = (int)$loc['speed'];
    $EventType->altitude = (int)$loc['altitude'];
    $EventType->shipment = '0';
    $EventType->odometer = '';
    $EventType->ignition = '';
    $EventType->battery = '87';
    $EventType->course = $orientation;

    $events = array();
    $events[0] = $EventType;

    $response = $soap->GPSAssetTracking(array('token' => $token, 'events' => $events));

    $payload = array(
        'ws_name' => 'Walmart',
        'msg' => '',
        'response' => $response,
        'parameters' => (array) $EventType
    );

    fwrite($file, print_r(json_encode($payload), true));
    return json_encode($payload);

}


// ?>