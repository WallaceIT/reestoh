<?php
//============================================================+
// File name   : example_048.php
// Begin       : 2009-03-20
// Last Update : 2013-05-14
//
// Description : Example 048 for TCPDF class
//               HTML tables and table headers
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

require_once('tcpdf/tcpdf.php');
require_once('db.php');

// Event data
$events = $db -> query('SELECT * FROM events ORDER BY ID DESC LIMIT 0,1');
$count = $events->rowCount();
if($count){
    $row_events = $events -> fetch(PDO::FETCH_ASSOC);
    $event = $row_events['name'];
    $eventID = $row_events['ID'];
}
else{
    header("Location: index.php");
}

// Extract order data
$order = $db -> query("SELECT * FROM orders_$eventID WHERE ID = $_GET[ID]");
$count = $order->rowCount();
if($count){
    $order = $order -> fetch(PDO::FETCH_ASSOC);
}
else{
    header("Location: index.php");
};

$items = preg_split("/;/", $order['order_content'], -1, PREG_SPLIT_NO_EMPTY);


// Init empty array
$CAT_HTML=array();

$cur_cat = 0;
$cur_pointer = -1;

$has_special = 0;

foreach($items as $item){

    $idx = preg_split("/:/", $item, -1, PREG_SPLIT_NO_EMPTY);
    $id  = $idx[0];
    $qty = $idx[1];
    $cat = $idx[2];

    $sql = "SELECT * FROM items_$eventID WHERE ID = $id";
    $item_detail = $db -> query($sql);
    $item_detail = $item_detail -> fetch(PDO::FETCH_ASSOC);
    
    // New category
    if($cat > $cur_cat){
        
        if($cat == 1)
            $has_special = 1;
        
        $cat_name = $db -> query("SELECT name FROM categories_$eventID WHERE ID = $cat");
        $cat_name = $cat_name -> fetch(PDO::FETCH_ASSOC);
        $CAT_HTML[][0] = $cat_name['name'];
        $cur_pointer++;
        $CAT_HTML[$cur_pointer][1] = "<tr><td width=\"20%\">$qty</td><td width=\"80%\">$item_detail[name]</td></tr>";
        $cur_cat = $cat;
    }
    else $CAT_HTML[$cur_pointer][1] .= "<tr><td>$qty</td><td>$item_detail[name]</td></tr>";
}

// Normal categories (starts from $has_special, equal to 1 only if special elements are present)
for($i=$has_special; $i<=$cur_pointer;$i++){  
    $CAT_HTML[$i][1] = '<br><div style="text-align:center">'.$CAT_HTML[$i][0].'</div><br><table border="1" cellpadding="1mm">'.$CAT_HTML[$i][1].($has_special?$CAT_HTML[0][1]:'').'</table>';
}

$CAT_HEADER_HTML = '<div style="text-align:center">'.$event."</div><hr>";
$CAT_FOOTER_HTML = "<hr><div style=\"text-align:center\">#$order[ID] - $order[customer] - $order[timestamp]</div>";



// PDF creation

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Reestoh');
$pdf->SetTitle("$event - ordine $order[ID]");

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(0,0,0);

// set auto page breaks
$pdf->SetAutoPageBreak(FALSE, 0);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------


$pagecount = $cur_pointer/4;
$cellcount = $cur_pointer;


for($ix=0; $ix<$pagecount;$ix++){
    // add a page
    $pdf->AddPage();

    $pdf->SetFont('helvetica', '', 8);

    // -----------------------------------------------------------------------------

    $tbl = '
    <table cellspacing="0" cellpadding="0">
        <tr>
            <td width="10mm" height="5mm"></td>
            <td width="90mm"></td>
            <td width="10mm"></td>
            <td width="90mm"></td>
            <td width="10mm"></td>
        </tr>
        <tr>
            <td height="7.5mm"></td>
            <td>'.($cellcount>($ix+0)?$CAT_HEADER_HTML:"").'</td>
            <td></td>
            <td>'.($cellcount>($ix+1)?$CAT_HEADER_HTML:"").'</td>
            <td></td>
        </tr>
        <tr>
            <td height="122.5mm"></td>
            <td>'.($cellcount>($ix+0)?$CAT_HTML[($ix+1)][1]:"").'</td>
            <td></td>
            <td>'.($cellcount>($ix+1)?$CAT_HTML[($ix+2)][1]:"").'</td>
            <td></td>
        </tr>
        <tr>
            <td height="7.5mm"></td>
            <td>'.($cellcount>($ix+0)?$CAT_FOOTER_HTML:"").'</td>
            <td></td>
            <td>'.($cellcount>($ix+1)?$CAT_FOOTER_HTML:"").'</td>
            <td></td>
        </tr>
        <tr>
            <td height="5mm"></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>

        <tr>
            <td width="10mm" height="5mm"></td>
            <td width="90mm"></td>
            <td width="10mm"></td>
            <td width="90mm"></td>
            <td width="10mm"></td>
        </tr>
        <tr>
            <td height="7.5mm"></td>
            <td>'.($cellcount>($ix+2)?$CAT_HEADER_HTML:"").'</td>
            <td></td>
            <td>'.($cellcount>($ix+3)?$CAT_HEADER_HTML:"").'</td>
            <td></td>
        </tr>
        <tr>
            <td height="122.5mm"></td>
            <td>'.($cellcount>($ix+2)?$CAT_HTML[($ix+3)][1]:"").'</td>
            <td></td>
            <td>'.($cellcount>($ix+3)?$CAT_HTML[($ix+4)][1]:"").'</td>
            <td></td>
        </tr>
        <tr>
            <td height="7.5mm"></td>
            <td>'.($cellcount>($ix+2)?$CAT_FOOTER_HTML:"").'</td>
            <td></td>
            <td>'.($cellcount>($ix+3)?$CAT_FOOTER_HTML:"").'</td>
            <td></td>
        </tr>
        <tr>
            <td height="5mm"></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>';

    $pdf->writeHTML($tbl, true, false, false, false, '');
}


// -----------------------------------------------------------------------------


$js = "print(true);";
$pdf->IncludeJS($js);


//Close and output PDF document
$pdf->Output('example_048.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
