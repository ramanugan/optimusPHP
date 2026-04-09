<?php

//$DB_HOSTNAME = 'gps-server-dev-2021-09-29.c9un9ye6g92k.us-west-2.rds.amazonaws.com'; // DESARROLLO
//$DB_HOSTNAME = 'gps-server-prod-instance-1.c9un9ye6g92k.us-west-2.rds.amazonaws.com'; // PRODUCCION
$DB_HOSTNAME = getenv('DB_HOSTNAME_CIA');
$DB_PORT     = getenv('DB_PORT_CIA'); // database port
$DB_NAME     = getenv('DB_NAME_CIA'); // database name
$DB_USERNAME = getenv('DB_USERNAME_CIA'); // database user name
$DB_PASSWORD = getenv('DB_PASSWORD_CIA'); // database password