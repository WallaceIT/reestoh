<?php
require_once('../config.php');

if(!isset($_SERVER['HTTP_REFERER'])){
        header('HTTP/1.0 403 Forbidden');
        die('You are not allowed to directly access this file.');     
}

// Event data
$events = $db -> query('SELECT * FROM events WHERE active = TRUE');
$count = $events->rowCount();
if($count){
    $row_events = $events -> fetch(PDO::FETCH_ASSOC);
    $event = $row_events['name'];
    $eventID = $row_events['ID'];
}
else{
    header("Location: ../index.php");
}

// Extract order data
$order = $db -> query("SELECT * FROM orders_$eventID WHERE ID = $_GET[ID]");
$count = $order->rowCount();
if($count){
    $order = $order -> fetch(PDO::FETCH_ASSOC);
}
else{
    header("Location: ../index.php");
};

$items = preg_split("/;/", $order['order_content'], -1, PREG_SPLIT_NO_EMPTY);


// Init empty array
$CAT_HTML=array();

$cur_cat = 0;
$cur_pointer = -1;

$has_special = 0;

$receipt = "";
$receipt_total = 0;

foreach($items as $item){

    $idx = preg_split("/:/", $item, -1, PREG_SPLIT_NO_EMPTY);

    $id  = $idx[0];
    $qty = $idx[1];
    $cat = $idx[2];

    $sql = "SELECT * FROM items_$eventID WHERE ID = $id";
    $item_detail = $db -> query($sql);
    $item_detail = $item_detail -> fetch(PDO::FETCH_ASSOC);
    
    // New category
    if($cat != $cur_cat){
        $cur_pointer++;
        
        if($cat == 1) $has_special = 1;
        
        $cat_name = $db -> query("SELECT name FROM categories_$eventID WHERE ID = $cat");
        $cat_name = $cat_name -> fetch(PDO::FETCH_ASSOC);
        $CAT_HTML[][0] = "$cat_name[name]";
        $CAT_HTML[$cur_pointer][1] = "";
        $cur_cat = $cat;
    }
    
    $CAT_HTML[$cur_pointer][1] .= "<tr>
                                       <td style=\"width:10%;text-align:center\">$qty</td>
                                       <td style=\"width:90%;\">$item_detail[name]</td>
                                   </tr>".PHP_EOL;
                                        
    // Receipt
    $receipt .= "<tr>
                    <td width=\"7%\" style=\"text-align:center\">$qty</td>
                    <td width=\"78%\">$item_detail[name]</td>
                    <td width=\"15%\" style=\"text-align:right\">".$qty*$item_detail['price']."&euro;</td>
                </tr>".PHP_EOL;
    $receipt_total += $qty*$item_detail['price'];
}

// Normal categories (starts from $has_special, equal to 1 only if special elements are present)
for($i=$has_special; $i<=$cur_pointer;$i++){  
    $CAT_HTML[$i][1] = "<div>
                            <b>TAVOLO: __________
                            <br>
                            CLIENTE: ".mb_strimwidth($order['customer'], 0, 14, '')."
                        </div>
                        <div style=\"text-align:center\">".$CAT_HTML[$i][0]."</div>
                        <br>
                        <table style=\"width:100%;border-collapse:collapse;\" border=\"1\">".$CAT_HTML[$i][1].($has_special?$CAT_HTML[0][1]:'')."</table>".PHP_EOL;
}

// Receipt
$RECEIPT_HTML = "<div style=\"text-align:center\">*COPIA PER IL CLIENTE*</div>
                 <br>
                 <table style=\"width:100%;border-collapse:collapse;\" border=\"1\" cellpadding=\"1mm\">
                    $receipt
                    <tr>
                        <td width=\"7%\"></td>
                        <td width=\"78%\" style=\"text-align:right\">TOTALE:</td>
                        <td width=\"15%\" style=\"text-align:right\">$receipt_total&euro;</td>
                    </tr>
                 </table>".PHP_EOL;

// Header and footer
$HEADER_HTML = "<div style=\"text-align:center;\"><span style=\"color:white;\">.</span><br>$event</div><hr>".PHP_EOL;
$FOOTER_HTML = "<hr><div style=\"text-align:center\">#$order[ID] - $order[timestamp]</div>".PHP_EOL;

// ---------------------------------------------------------


$pagecount = ($cur_pointer+1);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title><?php echo $event; ?></title>
    <link rel="stylesheet" href="thermal.css"/>
    <script type="text/javascript">
        print(true);
    </script>
</head>
<body>
<?php
for($ix=$has_special; $ix<$pagecount;$ix++){
    echo $HEADER_HTML;
    echo $CAT_HTML[$ix][1];
    echo $FOOTER_HTML;
    echo '<p style="page-break-after: always;"></p>';
}

if($CONFIG_PRINT_RECEIPT){
    echo $HEADER_HTML;
    echo $RECEIPT_HTML;
    echo $FOOTER_HTML;
}

?>
    
</body>
