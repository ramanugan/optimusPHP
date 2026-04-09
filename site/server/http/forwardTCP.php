<?
ob_start();
echo "OK";
header("Connection: close");
header("Content-length: " . (string) ob_get_length());
ob_end_flush();

if (!isset($_GET["trama"])) {
    echo "die";
    die;
}

$enviotraccar = $_GET['envio'] ?? null;

if ($enviotraccar != '1') {
    $envio = pack("H*", $_GET['trama']);
    $port = 29107;
    $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if ($sock === false) {
        echo "socket_create() failed: reason: " .
            socket_strerror(socket_last_error()) . "\n";
    }

    socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);
    if (!socket_connect($sock, '176.31.141.99', $port)) {
        $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);

        die("Could not connect: [$errorcode] $errormsg \n");
    }

    if (!socket_send($sock, $envio, strlen($envio), 0)) {
        $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);

        die("Could not send data: [$errorcode] $errormsg \n");
    }
    usleep(1000000);
    socket_close($sock);
} else {

    
    $envio = pack("H*", $_GET['trama']);
    $port = 5004;
    $server = '147.182.248.136';

    $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if ($sock === false) {
        echo "socket_create() failed: reason: " .
            socket_strerror(socket_last_error()) . "\n";
    }

    socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);
    if (!socket_connect($sock, $server, $port)) {
        $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);

        die("Could not connect: [$errorcode] $errormsg \n");
    }

    if (!socket_send($sock, $envio, strlen($envio), 0)) {
        $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);

        die("Could not send data: [$errorcode] $errormsg \n");
    }
    usleep(1000000);
    socket_close($sock);
}
