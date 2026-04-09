<?php

function array_to_xml_01($array, &$xml)
{
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            if (!is_numeric($key)) {
                $subnode = $xml->addChild("$key");
                array_to_xml_01($value, $subnode);
            } else {
                $subnode = $xml->addChild("item$key");
                array_to_xml_01($value, $subnode);
            }
        } else {
            $xml->addChild("$key", htmlspecialchars("$value"));
        }
    }
}

function envioAltoTrack($loc, $economico, $placa)
{

    $file = fopen("/var/www/html/ws-logs/log_AltoTrack_".date("d-m-Y").".txt", "a");

    $wsdl = 'http://ws4.altotrack.com/WSPosiciones_Chep/WSPosiciones_Chep.svc?wsdl';
    $dummy = true;

    $soap = new SoapClient($wsdl);

    $object = array();

    if ($dummy) {
        $object = array(
            'proveedor' => 'OptimusGPS',
            'nombremovil' => $economico,
            'patente' => $placa,
            'fecha' => $loc['dt_tracker'],
            'latitud' => $loc['lat'],
            'longitud' => $loc['lng'],
            'direccion' => 0,
            'velocidad' => $loc['speed'],
            'ignicion' => 0,
            'GPSLinea' => 1,
            'LOGGPS' => 0,
            'puerta1' => 0,
            'evento' => 0
        );
    } else {
        $object = array(
            'proveedor' => $_POST["proveedor"],
            'nombremovil' => $_POST["nombremovil"],
            'patente' => $_POST["patente"],
            'fecha' => $_POST["fecha"],
            'latitud' => $_POST["latitud"],
            'longitud' => $_POST["longitud"],
            'direccion' => $_POST["direccion"],
            'velocidad' => $_POST["velocidad"],
            'ignicion' => $_POST["ignicion"],
            'GPSLinea' => $_POST["GPSLinea"],
            'LOGGPS' => $_POST["LOGGPS"],
            'puerta1' => $_POST["puerta1"],
            'evento' => $_POST["evento"],
        );
    }

    $xml = new SimpleXMLElement('<registro></registro>');
    $movil = $xml->addChild('movil');

    array_to_xml_01($object, $movil);

    $xmlSerializado = $xml->asXML();

    $response = $soap->ProcessXML(array('xmlSerializado' => $xmlSerializado));

    $payload = array(
        'ws_name' => 'Altotrack',
        'msg' => '',
        'response' => $response,
        'parameters' => $object
    );

    fwrite($file, print_r(json_encode($payload), true));
    return json_encode($payload);
}

?>