<?php
    require('config.php');
    if(!isset($_GET['default'])){
        $events = $db -> query('SELECT * FROM events WHERE active > 0');
        if(!$events)
            header("Location: admin.php");

        $count = $events->rowCount();
        if($count){
            $row_events = $events -> fetch(PDO::FETCH_ASSOC);
            $event = $row_events['name'];
            $eventID = $row_events['ID'];
            $day = $row_events['active'];
            $discount = $row_events['discount'];
            $evday = $eventID.'_'.$day;
        }
        else header("Location: admin.php?noactive");
    }
    else {
        $event = 'Default';
        $eventID = '0_1';
        $discount = 100;
    }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Management - <?php echo $event; ?></title>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
    <link rel="stylesheet" href="style.css"/>
    <link rel="stylesheet" href="js/jquery-ui.css"/>
    <script src="js/jquery.min.js" type="text/javascript"></script>
    <script src="js/jquery-ui.min.js" type="text/javascript"></script>
    <script src="js/manage.js" type="text/javascript"></script>
</head>
<body>
    <?php include('toolbar.htm'); ?>
    <div id="event_name">
        <input type="text" id="mng_event_name" size="40" value="<?php echo $event; ?>">
    </div>
    <div id="mng_cat_container">
        <?php
            $item_lastID = 0;
            $cat_lastID = 0;
            $cats = $db -> query("SELECT * FROM categories_$evday ORDER BY displayorder asc");
            while ($row_cats = $cats -> fetch(PDO::FETCH_ASSOC)) {
                $ID = $row_cats['ID'];
                if($ID > $cat_lastID) $cat_lastID = $ID;
                ?>
                <div class="group">
                    <h3 id="cat_<?php echo $ID;?>" cat="<?php echo $ID;?>" name="<?php echo $row_cats['name'];?>">
                        <?php
                            if($ID != 1) echo '<span class="handle cat_handle ui-icon ui-icon-arrowthick-2-n-s"></span>';
                            else echo '<span class="handle ui-icon ui-icon-locked"></span>';
                            echo $row_cats['name'];
                            if($ID>1) {?>
                                <button style="float:right" class="remove_cat" cat="<?php echo $ID;?>"></button>
                            <?php ;} ?>
                    </h3>
                    <div id="cat_<?php echo $ID;?>_items" class="sortable_cat">
                        <?php
                            $items = $db -> query("SELECT * FROM items_$evday WHERE category = $ID ORDER BY displayorder asc");
                            if (!$items) break;
                            while ($row_items = $items -> fetch(PDO::FETCH_ASSOC)) {
                            $item_ID = $row_items['ID'];
                            if($item_ID > $item_lastID) $item_lastID = $item_ID; ?>
                                <div class="mng_item ui-accordion-header ui-state-default ui-accordion-icons" cat="<?php echo $row_items['category'];?>" id="mng_item_<?php echo $item_ID;?>" item="<?php echo $item_ID;?>">
                                    <span class="item_handle handle handle ui-icon ui-icon-arrowthick-2-n-s"></span>
                                    <input size="30" type="text" id="mng_item_name_<?php echo $row_items['ID'];?>" value="<?php echo $row_items['name']; ?>" required>
                                    &euro; <input type="number" id="mng_item_price_<?php echo $item_ID;?>" min="0" step="0.5" value="<?php echo $row_items['price'];?>" required>
                                    <span style="float:right"><a class="ui-icon ui-icon-closethick remove_item" href="" item="<?php echo $item_ID;?>">X</a></span>
                                </div>
                        <?php ;} ?>
                        <button class="mng_add_item" cat="<?php echo $ID;?>">+</button>
                    </div>
                </div>
        <?php ;} ?>
    </div>
    <br>

    <div id="event_discount" class="mc_discount">
        <label for='mng_event_discount' class='text-center'>% sconto per servizio</label>&nbsp;<input type="text" id="mng_event_discount" size="20" value="<?php echo $discount;?>">&nbsp;%
    </div>

    <button id="mng_add_cat">Aggiungi Categoria</button>
    <input tye="submit" id="mng_save" value="Salva">

    <div id="mng_modal_add_cat" title="Aggiungi categoria:" class="hidden">
        <br>
        <input type="text" id="modal_add_cat_name" required>
        <br><br>
        <button style="float:right" id="mng_add_cat_ok">Ok</button>
    </div>
<!-- JS variables -->
<script type="text/javascript">
    var eventID = '<?php echo $eventID;?>';
    var evday = '<?php echo $evday;?>';
    var cat_lastid = <?php echo $cat_lastID;?>;
    var item_lastid = <?php echo $item_lastID;?>;
</script>
</body>
</html>
