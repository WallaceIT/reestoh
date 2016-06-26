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
    else{
        header("Location: admin.php?noactive");
    }

    $CATEGORIES_HTML = "";
    $ORDER_HTML = "";

    $tabler_counter = 0;

    $cats = $db -> query("SELECT * FROM categories_$eventID ORDER BY displayorder ASC");
    if(!$cats){
        echo "FAIL $eventID";
    }
    else{
        while ($row_cats = $cats -> fetch(PDO::FETCH_ASSOC)) {
            $ID = $row_cats['ID'];
            $items = $db -> query("SELECT * FROM items_$eventID WHERE category = $ID ORDER BY displayorder asc");
            $count = $items -> rowCount();
            if($count){

                $tabler_counter++;
                if($tabler_counter%2 == 0)
                    $CATEGORIES_HTML .= "<div style='heigth:0;clear:both'></div>".PHP_EOL;   

                $CATEGORIES_HTML .="<div class='category'>".PHP_EOL;

                if($ID > 1) // no name for special category
                    $CATEGORIES_HTML .="<div class='category_name'>$row_cats[name]</div>".PHP_EOL;

                while ($row_items = $items -> fetch(PDO::FETCH_ASSOC)) {
                    if(strlen($row_items['name'])>25){$fsize="0.8em";}else{$fsize="1em";};
                    $itemID = $row_items['ID'];
                    $CATEGORIES_HTML .="<div class='item_container'>
                                        <div id='sold_item_$itemID' item='$itemID' class='sold_item ui-button ui-widget ui-state-default ui-button-text-only'>
                                            <span class='ui-button-text'>$row_items[sold]</span>
                                        </div>
                                        <div id='item_$itemID' item='$itemID' class='item ui-button ui-widget ui-state-default ui-button-text-only' price='$row_items[price]'>
                                            <span style='font-size:$fsize'>".mb_strimwidth($row_items['name'], 0, 40, '..')." ($row_items[price]&euro;)"."</span>
                                            <input type='text' size='3' style='float:right' readonly value='0' id='qty_item_$itemID' item='$itemID' cat='$row_items[category]' class='qty_item'>
                                        </div>
                                        <div id='minus_item_$itemID' item='$itemID' class='minus_item ui-corner-right'>-</div>
                                        </div>".PHP_EOL;

                    $ORDER_HTML .= "<div id='order_item_$itemID' class='order_item hidden ui-state-default'>
                                    <button item='$itemID' class='minus_item button_minus_item'>-</button>
                                    <input type='text' size='2' class='order_item_qty' value='0' readonly>
                                    <button item='$itemID' class='item button_plus_item' price='$row_items[price]'>+</button>
                                        $row_items[name]
                                    </div>".PHP_EOL;
                }
                $CATEGORIES_HTML .="</div>";
            }
        }
    };

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title><?php echo $event; ?></title>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
    <link rel="stylesheet" href="style.css"/>
    <link rel="stylesheet" href="js/jquery-ui.css"/>
    <script src="js/jquery.min.js" type="text/javascript"></script>
    <script src="js/jquery-ui.min.js" type="text/javascript"></script>
    <script type="text/javascript">var printMethod = '<?php echo $CONFIG_PRINT_MODE; ?>';</script>
    <script src="js/index.js" type="text/javascript"></script>
</head>
<body>
    <?php include('toolbar.htm'); ?>
    <div id="categories_container">
        <div id="event_name_category"><?php echo $event; ?></div>
        <!-- CAT -->
        <?php echo $CATEGORIES_HTML;?>
        <p>&nbsp;</p>
    </div>
    <div id="controls_container">
        <br>
        Riepilogo
        <div id="order_container">
             <!-- ORD -->
            <?php echo $ORDER_HTML;?>
        </div>
        <div id="total_container">
            Totale: &euro;<input type="text" size="5" id="total" value="0.00" readonly>
        </div>
        <div id="confirm_container">
            <form id="confirm_form">
                Servizio <input type="checkbox" id="staff" value="1">
                <br><br>
                Nome Cliente <input type="text" id="customer" required>
                <br><br>
                <input type="submit" id="order_confirm" value="Conferma">
                <input type="hidden" id="event_id" value="<?php echo $eventID;?>">
            </form>
        </div>
    </div>
    <div id="printing_dialog" class="hidden">Ordine in stampa.<br>Totale da riscuotere: <span id="dialog_total" style="font-weight:bold;"></span>&euro;<br><br><button id="printing_dialog_close" class="hidden">Nuovo ordine</button></div>
    <iframe id="frame" src="" class="hidden"></iframe>
</body>
</html>