<?php
    require('config.php');
    
    $events = $db -> query('SELECT * FROM events WHERE active = TRUE');
    if(!$events)
        header("Location: admin.php");

    $count = $events->rowCount();
    if($count){
        $row_events = $events -> fetch(PDO::FETCH_ASSOC);
        $event = $row_events['name'];
        $eventID = $row_events['ID'];
    }
    else header("Location: admin.php?noactive");
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Ordini - <?php echo $event; ?></title>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
    <link rel="stylesheet" href="style.css"/>
    <link rel="stylesheet" href="js/jquery-ui.css"/>
    <script src="js/jquery.min.js" type="text/javascript"></script>
    <script src="js/jquery-ui.min.js" type="text/javascript"></script>
    <script type="text/javascript">var eventID = <?php echo $eventID;?>;</script>
    <script type="text/javascript">var printMethod = '<?php echo $CONFIG_PRINT_MODE; ?>';</script>
    <script src="js/order_list.js" type="text/javascript"></script>
</head>
<body>
    <?php include('toolbar.htm'); ?>
    <div id="event_name"><?php echo $event; ?> - Lista degli ordini</div>
    <div id="olist_orders_container">
        <?php 
        $orders = $db -> query("SELECT * FROM orders_$eventID ORDER BY ID DESC");
        while ($row_orders = $orders -> fetch(PDO::FETCH_ASSOC)) {
            $orderID = $row_orders['ID'];?>
        <h3 id="order_<?php echo $orderID;?>" order="<?php echo $orderID;?>" class="olist_order_header">
            <?php
                echo "#$orderID - $row_orders[customer] [$row_orders[timestamp]]";
                if ($row_orders['staff']) echo " ***SERVIZIO***";
            ?>
            <span style="float:right"><button class="delete_order" order="<?php echo $orderID;?>"></button></span>
            <span style="float:right"><button class="print_order"  order="<?php echo $orderID;?>"></button></span>
        </h3>
        <div id="details_order_<?php echo $orderID;?>"></div>
        <?php ;} ?>
        <p>&nbsp;</p>
    </div>
</body>
</html>