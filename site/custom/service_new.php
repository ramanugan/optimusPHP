<?php
error_reporting(0);
session_start();
include("../config.php");

//Peticion que se usa solo para cuando el usuario se logea


//Checando el privilegio y obtener el usuario
if ($_SESSION["privileges"] == 'subuser') {
    $user_id = $_SESSION["manager_id"];
} else {
    $user_id = $_SESSION["user_id"];
}


if (@$_POST['cmd'] == 'openNuevasFunciones') {
    $dns_flask = $gsValues['IP_SERVER_FLASK'];
    $payload = [
        "username" => $gsValues['USERNAME_FLASK'],
        "password" => $gsValues['PASSWORD_FLASK']
    ];
    // Peticion Post
    $options = array(
        'http' => array(
            "header" => "Content-type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($payload)
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($dns_flask, false, $context);
    if ($result === FALSE) { /* Handle error */
        $error = array(
            'error' => true,
            'msg' => "302 - Servicio no diponible por el momento",
            'url' => ""
        );
        echo json_encode($error);
        die;
    }

    $token = json_decode($result) -> access_token;
    $url = $gsValues['DNS_ANGULAR'] . "?param=" . $token . '&id=' . $user_id;
    $payload = array(
        'error' => false,
        'msg' => '',
        'url' => $url
    );
    // $_SESSION['TOKENS'] -> access_token
    echo json_encode($payload);
    die;
}