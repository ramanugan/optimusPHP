<?php

require('sendDataWS.php');

function enviaDataToMabe($loc, $loc_ws)
{
    $file = fopen("/var/www/html/ws-logs/log_Mabe_".date("d-m-Y").".txt", "a");

    $serviceURL = "http://api.skyangel.com.mx:8081/insertaMov/";
    $method = "POST";
    $sendData = new sendDataWS($serviceURL, $method);

    $ws_credentials = explode(',',$loc_ws["value"]);
    $type = $loc['params']['type'] ?? null;
    $posRaw = array(
        "usuario" => $ws_credentials[0],
        "password" => $ws_credentials[1],
        "imei" => $loc_ws['imei'],
        "neconomico" => $loc_ws['economico'],
        "fechahora" => $loc['dt_tracker'] . " +00:00",
        "latitud" => $loc['lat'],
        "longitud" => $loc['lng'],
        "altitud" => $loc['lng'],
        "velocidad" => $loc['speed'],
        "direccion" => $loc['angle'],
        "ubicacion" => "",
        "evento" => $type,
        "temperatura" => "",
        "gasolina" => ""
    );
    $mensaje = $sendData->setPosicion(json_encode($posRaw));
        
    unset($posRaw['usuario']);
    unset($posRaw['password']);
    $payload = array(
        'ws_name' => 'MABE',
        'msg' => '',
        'response' => $mensaje,
        'parameter' =>$posRaw
    );

    fwrite($file, print_r(json_encode($payload), true));
    return json_encode($payload);

}