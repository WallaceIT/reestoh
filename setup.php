<?php
    require('db.php');

    if(!isset($_SERVER['HTTP_REFERER'])){
        header('HTTP/1.0 403 Forbidden');
        die('You are not allowed to directly access this file.');     
    }

    $db -> query("CREATE TABLE IF NOT EXISTS `events` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `name` text COLLATE utf8_unicode_ci NOT NULL,
    `active` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`ID`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1") or die("Unable to create table 'events'");

    $db -> query("CREATE TABLE IF NOT EXISTS `categories_0` (
    `ID` tinyint(4) NOT NULL AUTO_INCREMENT,
    `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`ID`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1") or die("Unable to create table 'categories_0'");;

    $db -> query("INSERT INTO `categories_0` (`ID`, `name`) VALUES (1, 'Speciale')") or die("Unable to insert default values into 'categories_0'");;

    $db -> query("CREATE TABLE IF NOT EXISTS `items_0` (
    `ID` smallint(11) NOT NULL AUTO_INCREMENT,
    `name` text COLLATE utf8_unicode_ci NOT NULL,
    `price` decimal(5,2) NOT NULL,
    `category` tinyint(4) NOT NULL,
    `sold` smallint(6) NOT NULL DEFAULT '0',
    `staff_given` smallint(6) NOT NULL DEFAULT '0',
    PRIMARY KEY (`ID`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3") or die("Unable to create table 'items_0'");;

    $db -> query("INSERT INTO `items_0` (`ID`, `name`, `price`, `category`, `sold`, `staff_given`) VALUES (1, 'Coperto', '1.50', 1, 0, 0), (2, 'Asporto', '0.50', 1, 0, 0)") or die("Unable to insert default values into 'items_0'");;
    
    $db -> query("CREATE TABLE IF NOT EXISTS `orders_0` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `customer` text COLLATE utf8_unicode_ci NOT NULL,
    `order_content` text COLLATE utf8_unicode_ci NOT NULL,
    `total` decimal(5,0) NOT NULL,
    `staff` tinyint(1) NOT NULL,
    `timestamp` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`ID`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1") or die("Unable to create table 'orders_0'");;

    echo 'OK';

?>