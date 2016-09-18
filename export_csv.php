<?php
    require('config.php');

    if(isset($_GET['eventID']))
        $events = $db -> query("SELECT * FROM events WHERE ID = $_GET[eventID]");
    else
        $events = $db -> query("SELECT * FROM events WHERE active = TRUE");

    if(!$events)
        header("Location: admin.php");

    $count = $events->rowCount();
    if($count){
        $row_events = $events -> fetch(PDO::FETCH_ASSOC);
        $event = $row_events['name'];
        $eventID = $row_events['ID'];
    }
    else header("Location: admin.php?noactive");

    // force csv download
    header('Content-disposition: attachment; filename='.str_replace(' ', '', $event).'.csv');
    header('Content-type: text/plain');

    $cats = $db -> query("SELECT * FROM categories_$eventID");
    
    echo "$event;;;;;".PHP_EOL;
    echo ";;;;;".PHP_EOL;
    echo "Categoria;Prodotto;Venduti;Serizio;Totale;".PHP_EOL;
    echo ";;;;;".PHP_EOL;
    $total = 0;
    while ($row_cats = $cats -> fetch(PDO::FETCH_ASSOC)) {
        $catID = $row_cats['ID'];
        $items = $db -> query("SELECT * FROM items_$eventID WHERE category = $catID");
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
