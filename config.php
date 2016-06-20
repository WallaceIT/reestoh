<?php
// DATABASE CONFIGURATION
$CONFIG_HOSTNAME = 'localhost';
$CONFIG_DBNAME = 'reestoh';
$CONFIG_USERNAME = 'root';
$CONFIG_PASSWORD = '';

// OPTIONS
$CONFIG_PRINT_INVOICE = true;
$CONFIG_PRINT_MODE = 'thermal'; // can be 'html', 'pdf' or 'thermal'
$CONFIG_PRINTER = '/dev/thermal_a7';
$CONFIG_THERMAL_MIN_LINES = 8;

// DATABASE CONNECTION SETUP
$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
$dsn = 'mysql:host='.$CONFIG_HOSTNAME.';dbname='.$CONFIG_DBNAME;
$db = new PDO($dsn, $CONFIG_USERNAME, $CONFIG_PASSWORD, $options) or die("Unable to Connect to the specified DB!");

?>