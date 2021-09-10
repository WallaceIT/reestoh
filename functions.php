<?php
    require("config.php");

    $_POST['func']($db, $_POST);

function processOrder($db, $post){

    $time = date("d/m/Y H:i");

    $total = $post['total'];
    $sql = "INSERT INTO orders_$post[evday] (`ID`,`customer`, `order_content`, `total`, `staff`, `timestamp`) VALUES (NULL, '$post[customer]', '$post[order]', $total, $post[staff], '$time')";

    $db -> query($sql);
    $lastId = $db->lastInsertId();

    $items = preg_split("/;/", $post['order'], -1, PREG_SPLIT_NO_EMPTY);

    foreach($items as $item){

        $id = preg_split("/:/", $item, -1, PREG_SPLIT_NO_EMPTY);
        $qty = $id[1];
        $id = $id[0];

        if($post['staff'])
            $sql = "UPDATE items_$post[evday] SET `staff_given` = `staff_given` + $qty WHERE `id` = '$id'";
        else
            $sql = "UPDATE items_$post[evday] SET `sold` = `sold` + $qty WHERE `id` = '$id'";

        $db -> query($sql);
    }

    echo $lastId;
};

function getOrderDetails($db, $post){
    $order = $db -> query("SELECT * FROM orders_$post[evday] WHERE ID = $post[orderID]");
    $order = $order -> fetch(PDO::FETCH_ASSOC);
    $items = preg_split("/;/", $order['order_content'], -1, PREG_SPLIT_NO_EMPTY);

    foreach($items as $item){

        $id = preg_split("/:/", $item, -1, PREG_SPLIT_NO_EMPTY);
        $qty = $id[1];
        $id = $id[0];

        $sql = "SELECT * FROM items_$post[evday] WHERE ID = $id";
        $item_detail = $db -> query($sql);
        $item_detail = $item_detail -> fetch(PDO::FETCH_ASSOC);

        echo "$qty $item_detail[name]<br>";
    }

    echo "<br>Totale: <b>$order[total]&euro;</b>";
};

function deleteOrder($db, $post){
    $order = $db -> query("SELECT * FROM orders_$post[evday] WHERE ID = $post[orderID]");
    $order = $order -> fetch(PDO::FETCH_ASSOC);
    $items = preg_split("/;/", $order['order_content'], -1, PREG_SPLIT_NO_EMPTY);

    foreach($items as $item){

        $id = preg_split("/:/", $item, -1, PREG_SPLIT_NO_EMPTY);
        $qty = $id[1];
        $id = $id[0];

        if($order['staff'])
            $sql = "UPDATE items_$post[evday] SET `staff_given` = `staff_given` - $qty WHERE `id` = '$id'";
        else
            $sql = "UPDATE items_$post[evday] SET `sold` = `sold` - $qty WHERE `id` = '$id'";

        $db -> query($sql);
    }

    $db -> query("DELETE FROM orders_$post[evday] WHERE ID = $post[orderID]");
};

function activateEvent($db, $post){
    $events = $db -> query("SELECT * FROM events WHERE `id` = '$post[eventID]'");
    if (!$events->rowCount()) return;

    $lastdn = $post['evdayn'];

    $db -> query("UPDATE events SET `active` = 0");
    $db -> query("UPDATE events SET `active` = $lastdn WHERE `id` = '$post[eventID]'");
 };

function truncateEvent($db, $post){
    $events = $db -> query("SELECT * FROM events WHERE `id` = '$post[eventID]'");
    if (!$events->rowCount()) return;

    $row_events = $events -> fetch(PDO::FETCH_ASSOC);
    $days = preg_split("/;/", $row_events['days'], -1, PREG_SPLIT_NO_EMPTY);

    foreach($days as $day) {
        $dt = preg_split("/:/", $day, -1, PREG_SPLIT_NO_EMPTY);
        $evday = $post['eventID'].'_'.$dt[0];
        $db -> query("TRUNCATE TABLE `orders_$evday`");
        $db -> query("UPDATE items_$evday SET `sold` = 0");
        $db -> query("UPDATE items_$evday SET `staff_given` = 0");
    }
 };

function newEvent($db, $post){
    $db -> query("UPDATE events SET `active` = 0");
    $fdy = '1:'.$post['firstday'].';';
    $db -> query("INSERT INTO events (`ID`, `name`, `num_days`, `days`, `active`) VALUES (NULL, '$post[name]', 1, '$fdy', 1)");
    $lastID = $db -> lastInsertId();
    $evday = $lastID.'_1';
    if ($post['copyID'] != 0)
        $evday_orig = $post['copyID'].'_1';
    else
        $evday_orig = 0;
    $db -> query("CREATE TABLE categories_$evday LIKE categories_$evday_orig");
    $db -> query("INSERT categories_$evday SELECT * FROM categories_$evday_orig;");
    $db -> query("CREATE TABLE items_$evday LIKE items_$evday_orig");
    $db -> query("INSERT items_$evday SELECT * FROM items_$evday_orig;");
    $db -> query("UPDATE items_$evday SET `sold` = 0");
    $db -> query("UPDATE items_$evday SET `staff_given` = 0");
    $db -> query("CREATE TABLE orders_$evday LIKE orders_$evday_orig");
 };

function deleteEvent($db, $post){
    $events = $db -> query("SELECT * FROM events WHERE `id` = '$post[eventID]'");
    if (!$events->rowCount()) return;

    $row_events = $events -> fetch(PDO::FETCH_ASSOC);
    $days = preg_split("/;/", $row_events['days'], -1, PREG_SPLIT_NO_EMPTY);

    foreach($days as $day) {
        $dt = preg_split("/:/", $day, -1, PREG_SPLIT_NO_EMPTY);
        $evday = $post['eventID'].'_'.$dt[0];
        $db -> query("DROP TABLE categories_$evday");
        $db -> query("DROP TABLE items_$evday");
        $db -> query("DROP TABLE orders_$evday");
    }

    $db -> query("DELETE FROM events WHERE ID = $post[eventID]");
 };

function newDay($db, $post){

    $event = $db -> query("SELECT * FROM events WHERE ID = $post[eventID]");
    if(!$event)
        header("Location: admin.php");

    if(!$event->rowCount())
        header("Location: admin.php");

    $row_event = $event -> fetch(PDO::FETCH_ASSOC);

    $evday = $post['eventID'].'_'.$post['evdayn'];
    $evdayn_next = $row_event['num_days'] + 1;
    $evday_next = $post['eventID'].'_'.$evdayn_next;
    $db -> query("CREATE TABLE categories_$evday_next LIKE categories_$evday");
    $db -> query("INSERT categories_$evday_next SELECT * FROM categories_$evday;");
    $db -> query("CREATE TABLE items_$evday_next LIKE items_$evday");
    $db -> query("INSERT items_$evday_next SELECT * FROM items_$evday;");
    $db -> query("UPDATE items_$evday_next SET `sold` = 0");
    $db -> query("UPDATE items_$evday_next SET `staff_given` = 0");
    $db -> query("CREATE TABLE orders_$evday_next LIKE orders_$evday");

    $day_next = $evdayn_next.":".$post['nextdate'].";";

    $db -> query("UPDATE events SET `num_days` = $evdayn_next");
    $db -> query("UPDATE events SET `days` = CONCAT(days,'$day_next') WHERE ID = $post[eventID]");
    $db -> query("UPDATE events SET `active` = $evdayn_next WHERE ID = $post[eventID]");
};

function editMenu($db, $post){
    $queries = preg_split('/§/', $post['sql'], -1, PREG_SPLIT_NO_EMPTY);
    foreach ($queries as $query)
        $events = $db -> query($query);
    echo 'OK';
};

function getDiscount($db, $post){
    $event = $db -> query("SELECT discount FROM events WHERE ID = $post[eventID]");
    $event = $event -> fetch(PDO::FETCH_ASSOC);
    echo $event['discount'];
};
?>
