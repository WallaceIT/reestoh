<?php
    require('config.php');

    if(isset($_GET['eventID']))
        $events = $db -> query("SELECT * FROM events WHERE ID = $_GET[eventID]");
    else
        $events = $db -> query("SELECT * FROM events WHERE active > 0");

    if(!$events)
        header("Location: admin.php");

    $count = $events->rowCount();
    if($count){
        $row_events = $events -> fetch(PDO::FETCH_ASSOC);
        $event = $row_events['name'];
        $eventID = $row_events['ID'];
        $evdayn = $row_events['active'];
        $evday = $eventID.'_'.$evdayn;
    }
    else header("Location: admin.php?noactive");

    if(isset($_GET['evdayn']) && $_GET['evdayn'] != '') {
        $evdayn = $_GET['evdayn'];
        $evday = $eventID.'_'.$_GET['evdayn'];
    }

    // search for date of day
    $date = "00/00/0000";
    $days = preg_split("/;/", $row_events['days'], -1, PREG_SPLIT_NO_EMPTY);
    foreach($days as $day) {
        $dt = preg_split("/:/", $day, -1, PREG_SPLIT_NO_EMPTY);
        if ($dt[0] == $evdayn)
            $date = $dt[1];
            break;
    }

    // force csv download
    header('Content-disposition: attachment; filename='.str_replace(' ', '', $event).'.csv');
    header('Content-type: text/plain');

    $cats = $db -> query("SELECT * FROM categories_$evday");

    echo "$event;$date;;;;".PHP_EOL;
    echo ";;;;;".PHP_EOL;
    echo "Categoria;Prodotto;Venduti;Serizio;Totale;".PHP_EOL;
    echo ";;;;;".PHP_EOL;
    $total = 0;
    while ($row_cats = $cats -> fetch(PDO::FETCH_ASSOC)) {
        $catID = $row_cats['ID'];
        $items = $db -> query("SELECT * FROM items_$evday WHERE category = $catID");
        $count = $items -> rowCount();
        if($count){
            $sold_cat_total = 0;
            $cash_cat_total = 0;
            $staff_given_cat_total = 0;

            while ($row_items = $items -> fetch(PDO::FETCH_ASSOC)) {
                $cash = $row_items['sold']*$row_items['price'];
                $sold_cat_total += $row_items['sold'];
                $staff_given_cat_total += $row_items['staff_given'];
                $cash_cat_total += $cash;
                echo "$row_cats[name];$row_items[name];$row_items[sold];$row_items[staff_given];$cash;".PHP_EOL;
            }
            echo "$row_cats[name];;$sold_cat_total;$staff_given_cat_total;$cash_cat_total;".PHP_EOL;
            echo ";;;;;".PHP_EOL;
            $total += $cash_cat_total;
        }
    }
    echo ";;;Totale;$total;".PHP_EOL;
?>
