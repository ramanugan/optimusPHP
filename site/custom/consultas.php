<?php

/**
 * @return false|mysqli|null
 * funciones que permiten hacer consultas especificas a la base de datos alterna
 * Fecha_creacion: 23/Julio/2021
 */
$DB_HOSTNAME='';
$DB_USERNAME='';
$DB_PASSWORD='';
$DB_NAME='';
$DB_PORT='';

function CONEXION()
{
    include ('../config_db_company.php');
    $CONEXION = mysqli_connect($DB_HOSTNAME, $DB_USERNAME, $DB_PASSWORD, $DB_NAME, $DB_PORT)
    or die("NO SE PUDO CONECTAR AL BASE DE DATOS " . $DB_NAME . " CON EL PUERTO " . $DB_PORT);
    return $CONEXION;
}

function DESCONEXION($conexion)
{
    $CLOSE = mysqli_close($conexion)
    or die("Ha sucedido un error inexperado en la desconexion de la base de datos");
    return $CLOSE;
}

function CODIFICACION()
{
    // Consulta para la configuracion de la codificacion de caracteres
    $CONEXION = CONEXION();
    mysqli_query($CONEXION, "SET NAMES 'UTF8'");
}

function CONSULTAR($SQL, $conexion)
{
    CODIFICACION();
    $RES = mysqli_query($conexion, $SQL);
    return $RES;
}

function ABC($SQL)
{
    $CONEXION = CONEXION();
    $RES = mysqli_query($CONEXION, $SQL);
    DESCONEXION($CONEXION);
    return $RES;
}

function HORA_ACTUAL() {
    date_default_timezone_set('America/Mexico_City');
    $DateAndTime = date('Y-m-d H:i:s', time());
    return $DateAndTime;
}

function PARSET_FECHA($fecha) {
    date_default_timezone_set('America/Mexico_City');
    $Time = date('H:i:s', time());
    return $fecha . ' ' . $Time;
}