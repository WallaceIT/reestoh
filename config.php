<?php
// DATABASE CONFIGURATION
$dbname = 'reestoh';
$dsn = 'mysql:host=localhost;dbname='.$dbname;
$username = 'root';
$password = '';
$options = array(
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
); 

$db = new PDO($dsn, $username, $password, $options) or die("Unable to Connect to the specified DB!");

// OPTIONS
$CONFIG_PRINT_RECEIPT = true;
$CONFIG_PRINT_MODE = 'html'; // can be 'html' or 'pdf'
?>