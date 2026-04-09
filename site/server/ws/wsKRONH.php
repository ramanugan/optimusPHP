<?php

//$User = "Usuario";
//$Password = "Password";
$ServiceURL = "https://kws.kronh.com/TrackerWebServices/gps.asmx?wsdl";
$Method = "ExternalGPSInputs_V3";

function envioKRONH($loc, $User, $Password, $economico)
{

    $file = fopen("/var/www/html/ws-logs/log_Kronh_".date("d-m-Y").".txt", "a");

    global $ServiceURL;
    global $Method;
    // global $User;
    // global $Password;

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
    $dt_tracker = explode(' ', $loc['dt_tracker']);
    $Positions = array(
        array(
            "DeviceID" => $loc['imei'], // Codigo unico GPS
            "DeviceAlias" => $economico, // Id de Unidad (No. Economico)
            "Date" => $dt_tracker[0], // Fecha del GPS (Formato: YYYY-MM-DD)
            "Time" => $dt_tracker[1], // Hora del GPS (Formato: HH:MM:SS)
            "Latitude" => $loc['lat'], // Latitud en grados decimales
            "Longitude" => $loc['lng'], // Longitud en grados decimales
            "IgnitionStatus" => true, // Estatus del motor (Encendido/Apagado)
            "Speed" => $loc['speed'], // Velocidad registrada
            "Course" => $orientation, // Direccion registrada en grados (000)
            "TempFrozen" => "NA", // Temperatura Congelado (si no tiene registro se envía NA) - NA / +- 00.00
            "TempCold" => "NA", // Temperatura Refrigerado (si no tiene registro se envía NA) - NA / +- 00.00
            "EventNumber" => 110,// Número de Evento Registrado
            "GPSProvider" => 'OptimusGps' // Nombre de la compañía de GPS
        )
    );

    try {
        $OpenHedaer = "<ArrayOfPositions> <Position>";
        $CloseHeader = "</Position> </ArrayOfPositions>";
        $client = new SoapClient($ServiceURL, ["trace" => 1, "soap_version" => SOAP_1_1]);
        $PositionsRequest = array(
            "User" => $User,
            "Password" => $Password,
            "PositionsList" => ""
        );


        foreach ($Positions as $Value) {
            $XmlPosition = $OpenHedaer;

            foreach ($Value as $PositionKey => $PositionValue) {
                $XmlPosition .= "<" . $PositionKey . ">" . $PositionValue . "</" . $PositionKey . ">";
            }

            $XmlPosition .= $CloseHeader;
            $PositionsRequest["PositionsList"] = $XmlPosition;
        }

        $result = $client->ExternalGPSInputs_V3($PositionsRequest);
        unset($PositionsRequest['User']);
        unset($PositionsRequest['Password']);
        $xml = array2xml($PositionsRequest);
        $payload = array(
            'ws_name' => 'Kronh',
            'msg' => 'Ok',
            'response' => $result,
            'xml' => $xml
        );
        fwrite($file, print_r(json_encode($payload), true));
        return json_encode($payload);

    } catch (SoapFault $e) {
        $payload = array(
            'ws_name' => 'Kronh',
            'msg' => 'Error controlado',
            'response' => $e->getMessage()
        );

        fwrite($file, print_r(json_encode($payload), true));
        return json_decode($payload);
    }
}

function array2XML($data, $rootNodeName = 'results', $xml = NULL)
{
    if ($xml == null) {
        $xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
    }

    foreach ($data as $key => $value) {
        if (is_numeric($key)) {
            $key = "nodeId_" . (string)$key;
        }

        if (is_array($value)) {
            $node = $xml->addChild($key);
            array2XML($value, $rootNodeName, $node);
        } else {
            $value = htmlentities($value);
            $xml->addChild($key, $value);
        }

    }

    return html_entity_decode($xml->asXML());
}

/*

SendPosition($Positions);
 */
?>