<?php

function envioUniGis($loc, $user, $pass, $temp_value, $type, $economico, $placa)
{

    $file = fopen("/var/www/html/ws-logs/log_Unigis_".date("d-m-Y").".txt", "a");

    $type = $loc['params']['type'] ?? null;
    $codigo = 0;

    switch ($type) {

        case 'SOS':
            $codigo = 'P01';
            break;

        case 'JDS':
            $codigo = 'J01';
            break;

        case 'IGN':
            if ($loc['params']['ignition'] == "1") {
                $codigo = 'I01';
            }
            break;

        case 'IGL':
            if ($loc['params']['ignition'] == "0") {
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
    
    $parametros = array();
    $parametros['SystemUser'] = $user;
    $parametros['Password'] = $pass;
    $parametros['Dominio'] = $placa;
    $parametros['NroSerie'] = -1;
    $parametros['Codigo'] = $codigo;
    $parametros['Latitud'] = (float)$loc['lat'];
    $parametros['Longitud'] = (float)$loc['lng'];
    $parametros['Altitud'] = (float)$loc['altitude'];
    $parametros['Velocidad'] = (int)$loc['speed'];
    $parametros['FechaHoraEvento'] =  str_replace( ' ','T', $loc['dt_tracker']); //2019-03-29T18:00:00
    $parametros['FechaHoraRecepcion'] = str_replace(' ', 'T', $loc['dt_tracker']);
    $parametros['Sensores'] = array(
                        'Clave' => 'Temperatura',
                        'Valor' => (float)$temp_value,
    );

    $wsdl = "http://hub.unisolutions.com.ar/hub/unigis/MAPI/SOAP/gps/service.asmx?wsdl";

    try { 
    $client = new SoapClient($wsdl, array('exceptions' => 0));

    $result = $client->LoginYInsertarEvento( $parametros);

    } catch (Exception $e) {
        echo 'Error --> '. $e->getMessage();
        echo "<br>".$e->getTraceAsString();
        echo "<br>".$e->getCode();
        echo "<br>".$e->getLine();
    }

    unset($parametros['SystemUser']);
    unset($parametros['Password']);
    $payload = array(
        'ws_name' => 'Unigis',
        'msg' => '',
        'response' => $result,
        'parameter' => $parametros
    );

    fwrite($file, print_r(json_encode($payload), true));
    return json_encode($payload);

}

?>