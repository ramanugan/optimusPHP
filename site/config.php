<?php
// ############################################################
// All listed setting can be changed only by editing this file
// Other settings can be changed from CPanel/Manage server
// ############################################################

$gsValues['VERSION_ID'] = 40121;
$gsValues['VERSION'] = '3.3.3';

$gsValues['URL_BASE'] = getenv('URL_BASE') ?: $_SERVER['HTTP_HOST'];
$gsValues['URL_BASE_TIENDA'] = 'optimusgpstiendaenlinea.com';
$gsValues['URL_BASE_CONTACT'] = 'optimusgpstiendaenlinea.com';
$gsValues['URL_BASE_SHOP'] = '';
$gsValues['URL_BASE_GATEWAY_APP'] = 'www.gps-server.net/sms-gateway-android';
$gsValues['URL_ROUTING_OSMR_SERVICE_URL'] = 'router.project-osrm.org/route/v1';
$gsValues['URL_BILLING_CUSTOM_URL'] = 'cedis.connectfdi.com/login';

$gsValues['HTTP_MODE'] = getenv('HTTP_MODE') ?: 'https'; // options: http/https

// lock admin to IP addresses, example $gsValues['ADMIN_IP'] = '127.0.0.1,222.222.222.222,333.333.333.333';
$gsValues['ADMIN_IP'] = '';

// log out admin user if IP changes during active session, provides additional security from session stealing
$gsValues['ADMIN_IP_SESSION_CHECK'] = false; // options: false/true

$gsValues['SERVER_IP'] = '52.11.108.185'; // used only as information in CPanel

// multi server login
$gsValues['MULTI_SERVER_LOGIN'] = false; // options: false/true
$gsValues['MULTI_SERVER_LIST'] = array('' => '');

$gsValues['OBJECT_LIMIT'] = 0; // options: 0 means no limit, number sets limit
$gsValues['LOCATION_FILTER'] = true; // options: false/true
$gsValues['CURL'] = false; // options: false/true

// path to root of web application
// if application is installed not in root folder of web server, then folder name must be added, for example we install it in track folder: $_SERVER['DOCUMENT_ROOT'].'/track';
// very often web servers have no $_SERVER['DOCUMENT_ROOT'] set at all, then direct path should be used, for example c:/wamp/www or any other leading to www or public_html folder
$gsValues['PATH_ROOT'] = $_SERVER['DOCUMENT_ROOT'];
// url to root of web application, example: $gsValues['URL_ROOT'] = 'YOUR_DOMAIN/track';
$gsValues['URL_ROOT'] = $gsValues['HTTP_MODE'] . '://' . $gsValues['URL_BASE'];

// hardware key, should be same as in GPS-Server.exe
$gsValues['HW_KEY'] = '';

// Opcion para generar el tokens en flask y pasarlo al angular
$gsValues['IP_SERVER_FLASK'] = getenv('IP_SERVER_FLASK');
$gsValues['USERNAME_FLASK'] = getenv('USERNAME_FLASK');
$gsValues['PASSWORD_FLASK'] = getenv('PASSWORD_FLASK');
$gsValues['DNS_ANGULAR'] = getenv('DNS_ANGULAR');

// connection to MySQL database
$gsValues['DB_HOSTNAME'] = getenv("DB_HOSTNAME");
$gsValues['DB_PORT'] = getenv("DB_PORT"); // database port
$gsValues['DB_NAME'] = getenv("DB_NAME"); // database name
$gsValues['DB_USERNAME'] = getenv("DB_USERNAME"); // database user name
$gsValues['DB_PASSWORD'] = getenv("DB_PASSWORD"); // database password