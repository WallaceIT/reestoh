<?php
require_once('../config.php');

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

$invoice = "";
$invoice_total = 0;

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
        
        if($cat == 1)
            $has_special = 1;
        
        $cat_name = $db -> query("SELECT name FROM categories_$eventID WHERE ID = $cat");
        $cat_name = $cat_name -> fetch(PDO::FETCH_ASSOC);
        $CAT_HTML[][0] = "<b class=\"font_items\">$cat_name[name]</b>";
        $CAT_HTML[$cur_pointer][1] = "<tr>
                                        <td class=\"font_items\" style=\"width:10%;text-align:center;\">$qty</td>
                                        <td class=\"font_items\" style=\"width:90%;\">$item_detail[name]</td>
                                      </tr>".PHP_EOL;
        $cur_cat = $cat;
    }
    else $CAT_HTML[$cur_pointer][1] .= "<tr>
                                            <td class=\"font_items\" style=\"text-align:center\">$qty</td>
                                            <td class=\"font_items\">$item_detail[name]</td>
                                        </tr>".PHP_EOL;

    if($CONFIG_PRINT_INVOICE){                                  
        // Receipt
        $invoice .= "<tr>
                        <td width=\"7%\" style=\"text-align:center\">$qty</td>
                        <td width=\"78%\">$item_detail[name]</td>
                        <td width=\"15%\" style=\"text-align:right;\">".$qty*$item_detail['price']."&euro;</td>
                    </tr>".PHP_EOL;
        $invoice_total += $qty*$item_detail['price'];
    }
}

// Normal categories (starts from $has_special, equal to 1 only if special elements are present)
for($i=$has_special; $i<=$cur_pointer;$i++){  
    $CAT_HTML[$i][1] = "<br>
                        <div><b>TAVOLO:</b> _____ <b> CLIENTE:</b> $order[customer]</div>
                        <div style=\"text-align:center\">".$CAT_HTML[$i][0]."</div>
                        <br>
                        <table style=\"width:100%;border-collapse:collapse;\" border=\"1\">".$CAT_HTML[$i][1].($has_special?$CAT_HTML[0][1]:'')."</table>".PHP_EOL;
}

if($CONFIG_PRINT_INVOICE){
    // Receipt
    $cur_pointer++;
    $CAT_HTML[][0] = "*COPIA PER IL CLIENTE*";
    $CAT_HTML[$cur_pointer][1] = "<br>
                                <div style=\"text-align:center\">*COPIA PER IL CLIENTE*</div>
                                <br>
                                <table style=\"width:100%;border-collapse:collapse;\" border=\"1\" cellpadding=\"1mm\">
                                    $invoice
                                    <tr>
                                        <td width=\"7%\"></td>
                                        <td width=\"78%\" style=\"text-align:right\">TOTALE:</td>
                                        <td width=\"15%\" style=\"text-align:right\">$invoice_total&euro;</td>
                                    </tr>
                                </table>".PHP_EOL;
}

// Header and footer
$CAT_HEADER_HTML = "<div style=\"text-align:center\"><b>$event</b></div><hr>".PHP_EOL;
$CAT_FOOTER_HTML = "<hr><div style=\"text-align:center\">#$order[ID] - $order[timestamp]</div>".PHP_EOL;

// ---------------------------------------------------------


$pagecount = ($cur_pointer+1-$has_special)/2;
$cellcount = ($cur_pointer+1-$has_special);

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title><?php echo $event; ?></title>
    <link rel="stylesheet" href="print.css"/>
    <script type="text/javascript">
        print(true);
    </script>
</head>
<body>
<?php
for($ix=0; $ix<$pagecount;$ix++){
    echo '
    <div class="page_a5">
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr style="height:7.5mm">
            <td style="width:90mm">'.($cellcount>(2*$ix+0)?$CAT_HEADER_HTML:"").'</div></td>
            <td style="width:10mm"></td>
            <td style="width:90mm">'.($cellcount>(2*$ix+1)?$CAT_HEADER_HTML:"").'</td>
        </tr>
        <tr class="aligner">
            <td><div class="aligner">'.($cellcount>(2*$ix+0)?$CAT_HTML[(2*$ix+0+$has_special)][1]:"").'</div></td>
            <td></td>
            <td><div class="aligner">'.($cellcount>(2*$ix+1)?$CAT_HTML[(2*$ix+1+$has_special)][1]:"").'</div></td>
        </tr>
        <tr style="height=:7.5mm">
            <td>'.($cellcount>(2*$ix+0)?$CAT_FOOTER_HTML:"").'</td>
            <td></td>
            <td>'.($cellcount>(2*$ix+1)?$CAT_FOOTER_HTML:"").'</td>
        </tr>
     </table>
     </div>
     '.PHP_EOL;
     
     if($ix%2)
        echo '<p style="page-break-after: always;"></p>';
     elseif($ix+1 < $pagecount)
        echo '<div style="height:1mm; overflow-y:hide;"></div>';
}
?>
    
</body>
