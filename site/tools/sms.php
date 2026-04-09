<?
function sendSMSHTTPQueue($gateway_url, $filter, $number, $message)
{
        global $ms;

        $q = "INSERT INTO `gs_sms_queue` 	(`dt_sms`,
							`gateway_url`,
							`filter`,
							`number`, 
							`message`)
							VALUES
							('" . gmdate("Y-m-d H:i:s") . "',
							'" . $gateway_url . "',
							'" . $filter . "',
							'" . $number . "',
							'" . mysqli_real_escape_string($ms, $message) . "')";
        $r = mysqli_query($ms, $q);

        if ($r) {
                // multiple phone numbers
                $numbers = explode(",", $number);
                return count($numbers);
        } else {
                return false;
        }
}

function sendSMSHTTP($gateway_url, $filter, $number, $message)
{
        global $ms;

        if (($gateway_url != '') && ($number != '') && ($message != '')) {
                // multiple phone numbers
                $numbers = explode(",", $number);

                // fitler array
                if ($filter != '') {
                        $filters = explode(",", $filter);
                }

                for ($i = 0; $i < count($numbers); ++$i) {
                        if ($i > 4) {
                                break;
                        }

                        $number = trim($numbers[$i]);

                        //IMPORTANT
                        $number_encoded = urlencode($number);
                        $message_encoded = urlencode($message);
                        //IMPORTANT

                        $url = str_replace("%NUMBER%", $number_encoded, $gateway_url);
                        $url = str_replace("%MESSAGE%", $message_encoded, $url);

                        sleep(1);

                        $context = stream_context_create(array(
                                'http' => array(
                                        'timeout' => 3
                                )
                        ));

                        if (isset($filters)) {
                                foreach ($filters as $value) {
                                        if (strpos($number, $value) !== false) {
                                                $result = @file_get_contents($url, false, $context);
                                        }
                                }
                        } else {
                                $result = @file_get_contents($url, false, $context);
                        }
                }

                return count($numbers);
        } else {
                return false;
        }
}

function sendSMSAPP($identifier, $filter, $number, $message)
{
        global $ms;

        if (($identifier != '') && ($number != '') && ($message != '')) {
                $message = substr($message, 0, 160);

                // multiple phone numbers
                $numbers = explode(",", $number);

                // filter array
                if ($filter != '') {
                        $filters = explode(",", $filter);
                }

                for ($i = 0; $i < count($numbers); ++$i) {
                        if ($i > 4) {
                                break;
                        }

                        $number = trim($numbers[$i]);

                        $dt_sms = gmdate("Y-m-d H:i:s");

                        if (isset($filters)) {
                                foreach ($filters as $value) {
                                        if (strpos($number, $value) !== false) {
                                                $q = "INSERT INTO `gs_sms_gateway_app`( `dt_sms`,
                                                                                                `identifier`,
                                                                                                `number`,
                                                                                                `message`
                                                                                                ) VALUES (
                                                                                                '" . $dt_sms . "',
                                                                                                '" . $identifier . "',
                                                                                                '" . $number . "',
                                                                                                '" . mysqli_real_escape_string($ms, $message) . "')";
                                                $r = mysqli_query($ms, $q);
                                        }
                                }
                        } else {
                                $q = "INSERT INTO `gs_sms_gateway_app`( `dt_sms`,
                                                                                `identifier`,
                                                                                `number`,
                                                                                `message`
                                                                                ) VALUES (
                                                                                '" . $dt_sms . "',
                                                                                '" . $identifier . "',
                                                                                '" . $number . "',
                                                                                '" . mysqli_real_escape_string($ms, $message) . "')";
                                $r = mysqli_query($ms, $q);
                        }
                }

                return count($numbers);
        } else {
                return false;
        }
}

function getSMSAPPTotalInQueue($identifier)
{
        global $ms;

        $q = "SELECT * FROM `gs_sms_gateway_app` WHERE `identifier`='" . $identifier . "'";
        $r = mysqli_query($ms, $q);

        $count = mysqli_num_rows($r);

        return $count;
}

function clearSMSAPPQueue($identifier)
{
        global $ms;

        $q = "DELETE FROM `gs_sms_gateway_app` WHERE `identifier`='" . $identifier . "'";
        $r = mysqli_query($ms, $q);
}

function sendCommandApi($messageData, $iccid)
{
        $messageData = [
                'text' => $messageData
        ];

        $jsonData = json_encode($messageData);

        $apiURL = "http://119.8.11.135/api/icc/$iccid/send_sms";

        $headers = [
                'Content-Type: application/json',
                'Authorization: Basic MjFmMjg0OTk4NmJlMTVjZjJhN2Q2ZmMzM2YxNjZjOGFkY2JhNjFiYTlmMDhlYWQ0NTg2YzlhM2ExNWE1MGE5MjpFQi1tdXBYUTBWWkFadVZsQkYzYlZuMzRTaTh1YTIzbzFhLUJvN1FKODVIS2FoYVVaSXBBVHVSYVhZMnhDdlgyOWRfNlBaVnBQbkJSdmw1X3d4WEVNUQ=='
        ];

        $options = [
                'http' => [
                        'header' => implode("\r\n", $headers),
                        'method' => 'POST',
                        'content' => $jsonData
                ]
        ];

        $context = stream_context_create($options);

        $result = file_get_contents($apiURL, false, $context);

        if ($result !== false) {
                return true;
        } else {
                return false;
        }
}
function sendTelefonicaCommandApi($cmd_, $number, $device)
{
        // URL del servicio SOAP
        $url = 'https://kiteplatform-api.telefonica.com:8010/services/SOAP/GlobalM2M/Inventory/v9/r12';

        // Estructura del mensaje SOAP
        $xml_post_string = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:typ="http://www.telefonica.com/schemas/UNICA/SOAP/Globalm2m/inventory/v9/types">
 <soapenv:Header/>
 <soapenv:Body>
 <typ:sendSMS> 
 <typ:destSubscriptions>
 <typ:icc>{$number}</typ:icc>
 </typ:destSubscriptions>
 <typ:text>{$cmd_}</typ:text>
 <typ:hasDeliveryReport>true</typ:hasDeliveryReport>
 </typ:sendSMS>
 </soapenv:Body>
</soapenv:Envelope>
XML;

        if ($device != 'EYESPro') {
                $cert_file = __DIR__ . '/customer-COMERCIALIZADORA_E_1914e3ad6b68e-1724265046698.pfx';
                $cert_password = 'Opkg2RwGdp';
        } else {
                $cert_file = __DIR__ . '/customer-COMERCIALIZADORA_E_1914e546e3fRX-1725290056454.pfx';
                $cert_password = 'GWsuBZ50O0';
        }

        // Inicializar cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Content-Type: text/xml; charset=utf-8",
                "Content-Length: " . strlen($xml_post_string)
        ));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string);
        curl_setopt($ch, CURLOPT_VERBOSE, 1); // Habilitar modo detallado para depuración

        // Configurar cURL para usar el certificado PFX
        curl_setopt(
                $ch,
                CURLOPT_SSLCERTTYPE,
                'P12'
        ); // Especificar el tipo de certificado
        curl_setopt($ch, CURLOPT_SSLCERT, $cert_file);
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $cert_password);

        // Ejecutar y obtener la respuesta
        $response = curl_exec($ch);

        if ($response === false) {
                // Obtener detalles del error
                $error = curl_error($ch);
                $info = curl_getinfo($ch);
                echo 'Error: ' . $error . "\n";
                echo 'HTTP Code: ' . $info['http_code'] . "\n";
        } else {
                return true;
        }

        // Cerrar cURL
        curl_close($ch);
}

function sendCommandApiTeltonika($text, $iccid)
{
    $messageData = [
        "iccid" => is_array($iccid) ? $iccid : [$iccid],
        "text" => $text
    ];

    $jsonData = json_encode($messageData);

    $apiURL = "https://iot.truphone.com/api/v2.0/sims/send_sms";

    $headers = [
        'Content-Type: application/json',
        'Authorization: Token c8c44160b6ae7e096ae3827677357ebbd7893106'
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $apiURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Error cURL: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    if ($result !== false) {
        return true;
    } else {
        return false;
    }
}

function CreateConfigBasicApi($cmd, $imei)
{
        $url = "https://api.teltonika.lt/tasks";

        $map = [
                'teltonikafm130' => '3836363',
                'teltonikafm920' => '3836361',
                'teltonikafm150' => '4553939',
                'teltonikafmOBD' => '3917053'
        ];

        if (isset($map[$cmd])) {
                $cmd = $map[$cmd];
        }
            

        if ($cmd == 'fota') {
                $apiToken = "6759|MIpVEUkKoM198puSktBMmvoi3nlKpJzXVZEA6qLZ";
                $url = "https://api.teltonika.lt/devices?imei=$imei";

                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        "accept: application/json",
                        "Authorization: Bearer $apiToken"
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                curl_close($ch);
                if ($httpCode === 200) {
                        $data = json_decode($response, true);

                        if (isset($data['data'][0]['current_configuration'])) {
                                return $data['data'][0]['current_configuration'];
                        } else {
                                return null;
                        }
                }
                    
        }
        

        $apiToken = "11177|A1w6BT5SoawuZClqfmIH1MhLzp95og84HBhc3BTo";
    
        $data = [
            "file_id" =>  $cmd,
            "device_imei" => $imei,
            "type" => "TxConfiguration",
            "expire_existing_tasks" => true
        ];
    
        // Configuración de cURL
        $ch = curl_init($url);
        $headers = [
            "Authorization: Bearer $apiToken",
            "User-Agent: COMERCIALIZADORA E IMPORTADORA OPTIMUS S.A DE C.V. /optimusrastreogps.net/1.0",
            "Accept: application/json",
            "Content-Type: application/json"
        ];
    
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
        $response = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
        curl_close($ch);

        if ($response !== false) {
                return true;
        } else {
                return false;
        }
}

function sendCommandApiAtt($text, $iccid)
{
    $username = "BGarzaAADMIN";
    $apiKey   = "8e060442-3b13-4eeb-87b0-6fc3305f5bfb";
    $auth     = base64_encode($username . ":" . $apiKey);

    $apiURL = "https://restapi7.jasper.com/rws/api/v1/devices/$iccid/smsMessages";

    $body = [
        "messageText" => $text
    ];

    $jsonBody = json_encode($body);

    $headers = [
        "Content-Type: application/json",
        "Accept: application/json",
        "Authorization: Basic $auth",
        "Content-Length: " . strlen($jsonBody)
    ];

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL            => $apiURL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $jsonBody,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 60
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);

        return [
            "success" => false,
            "error"   => $error
        ];
    }

    curl_close($ch);

    $data = json_decode($response, true);

    if ($httpCode == 200 || $httpCode == 201) {
        return [
            "success" => true,
            "smsId"   => $data["smsMessageId"] ?? null,
            "raw"     => $data
        ];
    } else {
        return [
            "success"  => false,
            "httpCode" => $httpCode,
            "response" => $data
        ];
    }
}
