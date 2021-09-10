<?php
date_default_timezone_set('Europe/Rome');

// DATABASE CONFIGURATION
$CONFIG_HOSTNAME = 'localhost';
$CONFIG_DBNAME = 'reestoh';
$CONFIG_USERNAME = 'user';
$CONFIG_PASSWORD = 'password';
$CONFIG_PORT = '3306';

// OPTIONS
$CONFIG_PRINT_INVOICE = true;
$CONFIG_PRINT_MODE = 'html'; // can be 'html' or 'thermal'
$CONFIG_PRINT_TRANSPORT = 'usb'; // can be 'usb' or 'net'
/* if CONFIG_PRINT_TRANSPORT is 'usb' */
$CONFIG_PRINTER = '/dev/thermal_a7';
/* if CONFIG_PRINT_TRANSPORT is 'net' */
$CONFIG_PRINTER_ADDRESS = '192.168.10.125';
$CONFIG_PRINTER_PORT = 9100;
/* print options */
$CONFIG_THERMAL_MIN_LINES = 8;

// DATABASE CONNECTION SETUP
$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
$dsn = 'mysql:host='.$CONFIG_HOSTNAME.';dbname='.$CONFIG_DBNAME.';port='.$CONFIG_PORT;
$db = new PDO($dsn, $CONFIG_USERNAME, $CONFIG_PASSWORD, $options) or die("Unable to Connect to the specified DB!");

?>
