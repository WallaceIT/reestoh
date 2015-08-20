<?php
    require('db.php');
    
    $events = $db -> query('SELECT * FROM events ORDER BY ID DESC LIMIT 0,1');
    $count = $events->rowCount();
    if($count){
        $row_events = $events -> fetch(PDO::FETCH_ASSOC);
        $event = $row_events['name'];
        $eventID = $row_events['ID'];
    }
    else{
        header("Location: manage.php");
    };
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Ordini - <?php echo $event; ?> - Reestoh 2014</title>
    <link rel="stylesheet" href="style.css"/>
    <link rel="stylesheet" href="js/jquery-ui.css"/>
    <script src="js/jquery.min.js" type="text/javascript"></script>
    <script src="js/jquery-ui.min.js" type="text/javascript"></script>
    
</head>
<body>
    <div id="toolbar">
        <a href="index.php" id="index" title="Nuovo Ordine"></a>
        <a href="report.php" id="report" title="Statistiche"></a>
        <a href="order_list.php" id="order_list" title="Lista Ordini"></a>
        <a href="manage.php" id="manage" title="Modifica Menù"></a>
    </div>
    <div id="event_name"><?php echo $event; ?> - Lista degli ordini</div>
    <div id="olist_orders_container">
        <?php 
        $orders = $db -> query("SELECT * FROM orders_$eventID");
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
    </div>

<!------------ JQUERY -------------->
<script type="text/javascript">
    
    $('#index').button({icons: {primary: 'ui-icon-document'}});
    $('#report').button({icons: {primary: 'ui-icon-calculator'}});
    $('#order_list').button({icons: {primary: 'ui-icon-note'}});
    $('#manage').button({icons: {primary: 'ui-icon-key'}});
    $('.delete_order').button({icons: {primary: 'ui-icon-closethick'}});
    $('.print_order').button({icons: {primary: 'ui-icon-print'}});
    
    $("#olist_orders_container").accordion({
        collapsible: true,
        active: false,
        clearStyle: true,
        autoHeight: false,
        header: '.olist_order_header',          // point to the class that is used as a header
        heightStyle: 'content',
        icons: false,
        beforeActivate: function (event,ui) {
            if (ui.newPanel.html() == '') {
                $.ajax({
                    type: "POST",
                    url: "functions.php",
                    data: {
                        func: "getOrderDetails",
                        orderID: $(ui.newHeader[0]).attr("order"),
                        eventID: <?php echo $eventID;?>
                    },
                    dataType: "text",
                    success: function(response){
                        ui.newPanel.html(response);
                    }
                });
            }
        }
    });
                
    $(".delete_order").click(function(){
        var orderID = $(this).attr("order");
        if (confirm("Eliminare l'ordine numero "+orderID+"?")) {
            
            $.ajax({
                type: "POST",
                url: "functions.php",
                data: {
                    func: "deleteOrder",
                    orderID: orderID,
                    eventID: <?php echo $eventID;?>
                },
                dataType: "text",
                success: function(response){
                    $("#order_"+orderID).remove();
                    $("#details_order_"+orderID).remove();
                }
            });
        }
    });
    
    $(".print_order").click(function(){
        var orderID = $(this).attr("order");
        window.open('print.php?ID='+orderID);
    });
    
</script>

</body>
</html>