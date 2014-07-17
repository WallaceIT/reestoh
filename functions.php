<?php 
    require("db.php");

    $_POST['func']($db, $_POST);
 
function processOrder($db, $post){
    $sql = "INSERT INTO orders_$post[eventID] (`ID`,`customer`, `order_content`, `total`, `staff`) VALUES (NULL, '$post[customer]', '$post[order]', '$post[total]', $post[staff])";

    $db -> query($sql);

    $items = preg_split("/;/", $post['order'], -1, PREG_SPLIT_NO_EMPTY);

    foreach($items as $item){

        $id = preg_split("/:/", $item, -1, PREG_SPLIT_NO_EMPTY);
        $qty = $id[1];
        $id = $id[0];

        if($post['staff'])
            $sql = "UPDATE items_$post[eventID] SET `staff_given` = `staff_given` + $qty WHERE `id` = '$id'";
        else
            $sql = "UPDATE items_$post[eventID] SET `sold` = `sold` + $qty WHERE `id` = '$id'";
            
        $db -> query($sql);
    }
};

function getOrderDetails($db, $post){
    $order = $db -> query("SELECT * FROM orders_$post[eventID] WHERE ID = $post[orderID]");
    $order = $order -> fetch(PDO::FETCH_ASSOC);
    $items = preg_split("/;/", $order['order_content'], -1, PREG_SPLIT_NO_EMPTY);

    foreach($items as $item){
        
        $id = preg_split("/:/", $item, -1, PREG_SPLIT_NO_EMPTY);
        $qty = $id[1];
        $id = $id[0];
        
        $sql = "SELECT * FROM items_$post[eventID] WHERE ID = $id";
        $item_detail = $db -> query($sql);
        $item_detail = $item_detail -> fetch(PDO::FETCH_ASSOC);
        
        echo "$qty $item_detail[name]<br>";
    }

    echo "<br>Totale: <b>$order[total]&euro;</b>";
};

function deleteOrder($db, $post){
    $order = $db -> query("SELECT * FROM orders_$post[eventID] WHERE ID = $post[orderID]");
    $order = $order -> fetch(PDO::FETCH_ASSOC);
    $items = preg_split("/;/", $order['order_content'], -1, PREG_SPLIT_NO_EMPTY);
    
    foreach($items as $item){
        
        $id = preg_split("/:/", $item, -1, PREG_SPLIT_NO_EMPTY);
        $qty = $id[1];
        $id = $id[0];
        
        if($order['staff'])
            $sql = "UPDATE items_$post[eventID] SET `staff_given` = `staff_given` - $qty WHERE `id` = '$id'";
        else
            $sql = "UPDATE items_$post[eventID] SET `sold` = `sold` - $qty WHERE `id` = '$id'";
        
        $db -> query($sql);
    }
    
    $db -> query("DELETE FROM orders_$post[eventID] WHERE ID = $post[orderID]");
};

function newEvent($db, $post){
    $db -> query("INSERT INTO events (`ID`, `name`) VALUES (NULL, 'Nuovo Evento')");
    $lastID = $db -> lastInsertId();
    $db -> query("CREATE TABLE categories_".$lastID." LIKE categories_0");
    $db -> query("INSERT categories_".$lastID." SELECT * FROM categories_0;");
    $db -> query("CREATE TABLE items_".$lastID." LIKE items_0");
    $db -> query("INSERT items_".$lastID." SELECT * FROM items_0;");
    $db -> query("CREATE TABLE orders_".$lastID." LIKE orders_0");
 };   
    
function editMenu($db, $post){
    $queries = preg_split('/§/', $post['sql'], -1, PREG_SPLIT_NO_EMPTY);
    foreach ($queries as $query)
        $events = $db -> query($query);
};
?>
