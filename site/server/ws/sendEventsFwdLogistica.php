<?php

$AuthServiceURL = "https://sru029yrw1.execute-api.us-east-2.amazonaws.com/produccion/proveedores/token";
$EventServiceURL = "https://sru029yrw1.execute-api.us-east-2.amazonaws.com/produccion/proveedores/tracking/evento";
$TestURL = "https://sru029yrw1.execute-api.us-east-2.amazonaws.com/produccion/proveedores/tracking/evento/prueba";

function CallAPIFwd($method, $url, $data = false)
{
    $curl = curl_init();

    switch ($method) {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    $headers = array("Content-Type: application/json", "accept: application/json");

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $result = curl_exec($curl);

    curl_close($curl);

    return $result;
}

function getToken($User, $Password)
{
    global $AuthServiceURL;

    $parameters = json_encode(array("strUsuario" => $User, "strPassword" => $Password));

    $result = CallAPIFwd("POST", $AuthServiceURL, $parameters);
    $jObj = json_decode($result);

    return $jObj->{'strToken'};
}

function castDateSQL_T_UG($date)
{
    return str_replace(' ', 'T', trim($date));
}

function envioFwdLogistica($PositionRequest, $User, $Password, $placa, $folioServicio = "0")
{
    
    $file = fopen("/var/www/html/ws-logs/log_Fwd_Logistica_".date("d-m-Y").".txt", "a");

    global $EventServiceURL; // Cambiar variable en produccion

    $angle = $PositionRequest['angle'];
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

    $params = $PositionRequest["params"];
    $dataRaw = array(
        "strCodigo" => "0",//$params['type'], // ** Código del evento
        "datFechaHora" => "" . castDateSQL_T_UG($PositionRequest['dt_tracker']), // ** Fecha en formato UTC - {año}-{mes}-{día}T{hora}:{min}:{seg}.{ms} - Ej. 2020-08-27T10:32:33.000
        "dblLatitud" => doubleval($PositionRequest['lat']), // ** Latitud de ubicación
        "dblLongitud" => doubleval($PositionRequest['lng']), // ** Longitud de ubicación
        "strNumeroPlaca" => "" . $placa, // ** Número de placas del vehículo
        "strNumeroServicio" => $folioServicio, // ** Folio del embarque ej. 0001MX
        "strNumeroSerie" => "" . $PositionRequest['imei'], // Número de serie del dispositivo de rastreo satelital
        "dblVelocidad" => doubleval($PositionRequest['speed']), // Velocidad actual del vehículo
        "dblAltitud" => doubleval($PositionRequest['altitude']), // Altitud de ubicación
        "dblKilometraje" => 0, // Total de kilómetros recorridos por el vehículo
        "intNivelBateria" => isset($PositionRequest['params']['batteryLevel']) ? intval($PositionRequest['params']['batteryLevel']) : 0, // Nivel de batería de 0 a 100
        "strOrientacion" => "" . $orientation, // Orientación cardinal. Ej. Norte, Sur, etc...
        "strDireccion" => "", // Dirección conocida de la ubicación actual del vehículo
        "bitEncendido" => 1 // Representación de estado actual del vehículo 0 para apagado y 1 para encendido
    );

    $Position = array(
        "strToken" => "",
        "eventos" => array( $dataRaw )
    );

    try {
        $Position["strToken"] = getToken($User, $Password);

        $result = CallAPIFwd("POST", $EventServiceURL, json_encode($Position));

        $payload = array(
            'ws_name' => 'Logistica',
            'msg' => '',
            'response' => $result,
            'parameters' => $dataRaw
        );

        fwrite($file, print_r(json_encode($payload), true));
        return json_encode($payload);

    } catch (RestFault $e) {
        $payload = array(
            'ws_name' => 'Logistica',
            'msg' => 'Error',
            'response' => $e->getMessage()
        );
        
        fwrite($file, print_r(json_encode($payload), true));
        return  json_encode($payload);
    }

}


// Obligatorio = **
// Nota: Objeto de pruebas


$dummyLoc = array(
    'imei' => '868789022104953',
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

    'event' => '0',
    'net_protocol' => '',
    'ip' => '',
    'port' => ''
);

// envioFwdLogistica($dummyLoc,"prueba","password",1234);
?>