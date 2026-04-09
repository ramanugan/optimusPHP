<?php


class Customer
{
    var $id;          //    Identidicador       A0
    var $name;        //    Nombre              A0
    //var $id = 'A12';                    //    Identidicador       A0
    //var $name = 'Guadalupe';            //    Nombre              A0
}

class Event
{
    function __construct($comp)
    {
        $this->customer = $comp;
    }

    var $code;        // ** Código              A0
    var $date;        // ** Fecha/Hora          2020-02-20T13:15:22
    var $latitude;    // ** Latitud             28.2882
    var $longitude;   // ** Longitud            -105.5069
    var $asset;       // ** Placa               123ABC

    var $serialNumber; //    Número              A0
    var $direction;   //    Dirección           A0
    var $speed;       // ** Velocidad           170
    var $altitude;    //    Altitud             99
    var $customer;    //    OBJETO

    var $shipment;    // ** Shipment            A0
    var $odometer;    //    Odómetro            99
    var $ignition;    //    Ignición            True
    var $battery;     //    Batería             99
    var $course;      //    Curso               A0
}


function castDateSQL_T_RC($date)
{
    return str_replace(' ', 'T', trim($date));
}

function envioRecursoConfiable($loc, $user, $pass, $id, $name, $placa)
{
    $file = fopen("/var/www/html/ws-logs/log_Rconfiable_" . date("d-m-Y") . ".txt", "a");

    $wsdl = 'http://gps.rcontrol.com.mx/Tracking/wcf/RCService.svc?wsdl';
    // $user = 'user_avl_cimexpress';
    // $pass = 'huZe#!665xliw&&5';


    // echo $loc;

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

    $user_name = getUserName($loc['imei']);

    $soap = new SoapClient($wsdl, array('classmap' => array('Event' => 'Event', 'Customer' => 'Customer')));

    try {

        $context = stream_context_create([
            'http' => ['timeout' => 5]
        ]);
    
        $soap = new SoapClient($wsdl, [
            'stream_context' => $context,
            'connection_timeout' => 5,
            'exceptions' => true,
            'trace' => false
        ]);
    
        $response = $soap->GetUserToken([
            'userId' => $user,
            'password' => $pass
        ]);
    
        $token = $response->GetUserTokenResult->token ?? null;
    
    } catch (Throwable $e) {
    
        $token = null;
    
        error_log("SOAP TOKEN ERROR: " . $e->getMessage());
    }
    


    $CustomerType = new Customer();

    $CustomerType->id = $id;
    $CustomerType->name = $name;


    $EventType = new Event($CustomerType);

    $type = $loc['params']['type'] ?? null;
    $codigo = 0;

    switch ($type) {

        case 'SOS':
            if ($user_name == 'interline' || $user_name == 'fleteslogisticos') {
                $codigo = '911';
            } else {
                $codigo = 'P01';
            }
            break;

        case 'JDS':
            $codigo = 'J01';
            break;

        case 'IGN':
            if (($loc['params']['ignition'] ?? null) === "1") {
                $codigo = 'I01';
            }
            break;

        case 'IGL':
            if (($loc['params']['ignition'] ?? null) === "0") {
                $codigo = 'I00';
            }
            break;


        case 'HBM':
            if ($loc['params']['alarm'] == "hardBraking") {
                $codigo = 'FB01';
            } else if ($loc['params']['alarm'] == "hardAcceleration") {
                $codigo = 'AB01';
            }
            break;


        case 'FRI':
            if ($loc['params']['output'] == "0" && $loc['params']['motion'] == "1") {
                $codigo = 'O00';
            } else  if ($loc['params']['output'] == "1" && $loc['params']['motion'] == "0") {
                $codigo = 'O01';
            } else  if ($loc['params']['output'] == "0" && $loc['params']['motion'] == "0") {
                $codigo = '1';
            } else  if ($loc['params']['output'] == "1" && $loc['params']['motion'] == "1") {
                $codigo = '1';
            }
            break;


        default:
            $codigo = 0;
            break;
    }


    $EventType->code = $codigo;
    $EventType->date = castDateSQL_T_RC($loc['dt_tracker']);
    $EventType->latitude = (float)$loc['lat'];
    $EventType->longitude = (float)$loc['lng'];
    $EventType->asset = (empty($placa) ? 0 : $placa);
    $EventType->serialNumber = $loc['imei'];
    $EventType->direction = $orientation;
    $EventType->speed = (int)$loc['speed'];
    $EventType->altitude = (int)$loc['altitude'];
    $EventType->shipment = '0';
    $EventType->odometer = '';
    $EventType->ignition = isset($loc['params']['ignition']) ? intval($loc['params']['ignition']) : 0;
    $EventType->battery = isset($loc['params']['batteryLevel']) ? intval($loc['params']['batteryLevel']) : 0;
    $EventType->course = $orientation;

    $events = array();
    $events[0] = $EventType;


    $response = $soap->GPSAssetTracking(array('token' => $token, 'events' => $events));

    $payload = array(
        'ws_name' => 'RConfiable',
        'msg' => '',
        'response' => $response,
        'parameters' => (array) $EventType
    );

    fwrite($file, print_r(json_encode($payload), true));
    return json_encode($payload);
}
