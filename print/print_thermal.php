<?php
require_once('../config.php');

require __DIR__ . '/autoload.php';
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

if ($CONFIG_PRINT_TRANSPORT == 'net') {
    $connector = new NetworkPrintConnector($CONFIG_PRINTER_ADDRESS, $CONFIG_PRINTER_PORT);
}
else if ($CONFIG_PRINT_TRANSPORT == 'usb') {
    if(php_uname('s') == 'Linux')
        $connector = new CupsPrintConnector($CONFIG_PRINTER);
    else
        $connector = new WindowsPrintConnector($CONFIG_PRINTER);
}
else if ($CONFIG_PRINT_TRANSPORT == 'file') {
    $connector = new FilePrintConnector($CONFIG_PRINTER);
}
else {
    die('Invalid printer settings.');
}

$printer = new Printer($connector);
if (!$printer) {
    die('Cannot setup printer.');
}

// Event data
$events = $db -> query('SELECT * FROM events WHERE active > 0');
$count = $events->rowCount();
if($count){
    $row_events = $events -> fetch(PDO::FETCH_ASSOC);
    $event = $row_events['name'];
    $eventID = $row_events['ID'];
    $discount = $row_events['discount'];
    $day = $row_events['active'];
    $evday = $eventID.'_'.$day;
}
else{
    die('Cannot find active event.');
}

// Extract order data
$orderID = $_GET['ID'];
$order = $db -> query("SELECT * FROM orders_$evday WHERE ID = $orderID");
$count = $order->rowCount();
if($count){
    $order = $order -> fetch(PDO::FETCH_ASSOC);
}
else{
    die('Cannot extract order data.');
};

/* Initialize */
$printer -> initialize();

// Format print
$items = preg_split("/;/", $order['order_content'], -1, PREG_SPLIT_NO_EMPTY);

$cur_cat = 0;
$cur_pointer = -1;
$isfirst = true;
$seats = 0;
$seats_text = "";
$cur_cat_lines = $CONFIG_THERMAL_MIN_LINES;

foreach($items as $item){

    $idx = preg_split("/:/", $item, -1, PREG_SPLIT_NO_EMPTY);

    $id  = $idx[0];
    $qty = $idx[1];
    $cat = $idx[2];

    if($cat == 1){
        if($id == 1){
            $sql = "SELECT * FROM items_$evday WHERE ID = $id";
            $item_detail = $db -> query($sql);
            $item_detail = $item_detail -> fetch(PDO::FETCH_ASSOC);
            $seats = $qty;
            $seats_text = strtoupper("$item_detail[name]");
        }
        continue;
    }

    $sql = "SELECT * FROM items_$evday WHERE ID = $id";
    $item_detail = $db -> query($sql);
    $item_detail = $item_detail -> fetch(PDO::FETCH_ASSOC);

    // New category
    if($cat != $cur_cat){
        $cur_cat = $cat;

        if(!$isfirst){
            $printer -> setTextSize(1, 2);
            if($seats > 0)
                $printer -> text("$seats $seats_text\n");
            for(;$cur_cat_lines>0;$cur_cat_lines--)
                $printer -> text("\n");
            cat_footer($printer, $orderID, $order['timestamp']);
            $printer -> cut(Printer::CUT_PARTIAL, 1);
            $cur_cat_lines = $CONFIG_THERMAL_MIN_LINES;
        }
        else
            $isfirst = false;

        $cat_name = $db -> query("SELECT name FROM categories_$evday WHERE ID = $cat");
        $cat_name = $cat_name -> fetch(PDO::FETCH_ASSOC);
        cat_header($printer, $event, $order['customer']);
        cat_title($printer, "$cat_name[name]\n\n");
    }

    $printer -> setTextSize(1, 2);
    $printer -> text("$qty ".strtoupper("$item_detail[name]")."\n");
}
// last footer
if($seats > 0)
    $printer -> text("$seats $seats_text\n");
for(;$cur_cat_lines>0;$cur_cat_lines--)
                $printer -> text("\n");
cat_footer($printer, $orderID, $order['timestamp']);

if($CONFIG_PRINT_INVOICE){

    $printer -> cut(Printer::CUT_PARTIAL, 1);

    $invoice_total = 0;

    cat_header($printer, $event, $order['customer'], true);
    foreach($items as $item){

        $idx = preg_split("/:/", $item, -1, PREG_SPLIT_NO_EMPTY);

        $id  = $idx[0];
        $qty = $idx[1];
        $cat = $idx[2];

        $sql = "SELECT * FROM items_$evday WHERE ID = $id";
        $item_detail = $db -> query($sql);
        $item_detail = $item_detail -> fetch(PDO::FETCH_ASSOC);

        $printer -> setTextSize(1, 1);
        $printer -> setJustification(Printer::JUSTIFY_LEFT);
        $printer -> text("$qty $item_detail[name] ");
        spacing($printer, (41 - strlen("$qty $item_detail[name] ".$qty*$item_detail['price']." E")) );
        $printer -> text($qty*$item_detail['price']." E");
        $printer -> text("\n");
        $invoice_total += $qty*$item_detail['price'];
    }
    $printer -> text("\n");

    $printer -> setJustification(Printer::JUSTIFY_RIGHT);
    $printer -> selectPrintMode(Printer::MODE_EMPHASIZED);

    if ($order['staff']	== 1) {
        $invoice_total *= (1 - $discount / 100);
        $printer -> text("TOTALE (Servizio): $invoice_total E  \n");
    } else {
        $printer -> text("TOTALE: $invoice_total E  \n");
    }

    cat_footer($printer, $orderID, $order['timestamp']);
}

$printer -> cut(Printer::CUT_FULL, 1);
$printer -> close();

function cat_title(Printer $printer, $text)
{
    $printer -> setJustification(Printer::JUSTIFY_CENTER);
    $printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
    $printer -> setTextSize(2, 2);
    $printer -> text($text);
    $printer -> setJustification(Printer::JUSTIFY_LEFT);
    $printer -> selectPrintMode();
}
function cat_header(Printer $printer, $evname, $customer, $invoice = false){
    $printer -> setJustification(Printer::JUSTIFY_CENTER);

    // Fix for printers appending fiscal data on top
    // $printer -> setTextSize(1, 1);
    // $printer -> text(" ");

    if(strlen($evname) > 18)
        $printer -> setTextSize(1, 2);
    else
        $printer -> setTextSize(2, 2);

    $printer -> text("$evname\n");
    $printer -> setTextSize(4, 1);
    $printer -> text("----------\n");
    if(!$invoice){
        $printer -> setJustification(Printer::JUSTIFY_LEFT);
        $printer -> setTextSize(1, 2);
        $printer -> text("Cliente: ");
        $customerName = strtoupper(mb_strimwidth($customer, 0, 14, ''));
        $printer -> text($customerName);
        spacing($printer, (16 - strlen($customerName)) );
        $printer -> text(" Tavolo: ________ ");
        $printer -> text("\n");
    }
    else {
        $printer -> setTextSize(1, 2);
        $printer -> text("***COPIA PER IL CLIENTE***\n");

        $printer -> setTextSize(1, 1);
        $customerName = strtoupper(mb_strimwidth($customer, 0, 14, ''));
        $printer -> text($customerName);
        $printer -> text("\n");
    }
}
function cat_footer(Printer $printer, $orderID, $timestamp){
    $printer -> setTextSize(4, 1);
    $printer -> setJustification(Printer::JUSTIFY_CENTER);
    $printer -> text("----------\n");
    $printer -> setTextSize(1, 2);
    $printer -> text("#$orderID - $timestamp\n");

    // Fix for printers appending fiscal data at bottom
    // $printer -> setTextSize(1, 1);
    // $printer -> text(" ");
}
function spacing(Printer $printer, $spaces){
    $spacing = "";
    $spacingnr = $spaces;
    while($spacingnr>0){
    $spacing .= " ";
        $spacingnr--;
    }
    $printer -> text($spacing);
}
?>
