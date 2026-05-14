<?php

define("ABOUT_US_URL", "/SEM 6 - WP/Project/");
define("CONTACT_US_URL", "/SEM 6 - WP/Project/");

date_default_timezone_set('Asia/Kolkata');

$host = "";
$username = "";
$password = "";
$database_name = "";
$port = 3306;

$connection = new mysqli($host, $username, $password, $database_name, $port);

$connection->set_charset("utf8mb4");

?>