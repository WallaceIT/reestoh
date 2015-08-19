<?php
    require('db.php');
    
    $events = $db -> query('SELECT * FROM events ORDER BY ID DESC LIMIT 0,1');
    $count = $events->rowCount();
    if($count){
        $row_events = $events -> fetch(PDO::FETCH_ASSOC);
        $event = $row_events['name'];
        $eventID = $row_events['ID'];
    }
    else{
        header("Location: manage.php");
    }

    $CATEGORIES_HTML = "";
    $ORDER_HTML = "";

    $cats = $db -> query("SELECT * FROM categories_$eventID");
    while ($row_cats = $cats -> fetch(PDO::FETCH_ASSOC)) {
        $ID = $row_cats['ID'];
        $items = $db -> query("SELECT * FROM items_$eventID WHERE category = $ID");
        $count = $items -> rowCount();
        if($count){ 
            $CATEGORIES_HTML .="<div class='category'>$row_cats[name]<br>";

            while ($row_items = $items -> fetch(PDO::FETCH_ASSOC)) {
                $itemID = $row_items['ID'];
                $CATEGORIES_HTML .="<div id='sold_item_$itemID' item='$itemID' class='sold_item ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only'><span class='ui-button-text'>$row_items[sold]</span></div>
                                    <div id='item_$itemID' item='$itemID' class='item ui-accordion-header ui-state-default' price='$row_items[price]'>
                                        $row_items[name] ($row_items[price]&euro;)
                                        <input type='text' size='3' style='float:right' readonly value='0' id='qty_item_$itemID' item='$itemID' cat='$row_items[category]' class='qty_item'>
                                    </div>
                                    <div id='minus_item_$itemID' item='$itemID' class='minus_item ui-corner-right'>-</div>".PHP_EOL;
               
                $ORDER_HTML .= "<div id='order_item_$itemID' class='order_item hidden ui-state-default'>
								<button item='$itemID' class='minus_item button_minus_item'>-</button>
                                <input type='text' size='2' class='order_item_qty' value='0' readonly>
                                <button item='$itemID' class='item button_plus_item' price='$row_items[price]'>+</button>
                                    $row_items[name]
                                </div>".PHP_EOL;
            }
            $CATEGORIES_HTML .="</div>";
        }
    };
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title><?php echo $event; ?> - Reestoh 2014</title>
    <link rel="stylesheet" href="style.css"/>
    <link rel="stylesheet" href="js/jquery-ui.css"/>
    <script src="js/jquery.min.js" type="text/javascript"></script>
    <script src="js/jquery-ui.min.js" type="text/javascript"></script>
    
</head>
<body>
    <div id="toolbar">
        <a href="index.php" id="index" title="Nuovo Ordine"></a>
        <a href="report.php" id="report" title="Statistiche"></a>
        <a href="order_list.php" id="order_list" title="Lista Ordini"></a>
        <a href="manage.php" id="manage" title="Modifica Menù"></a>
    </div>
    <div id="event_name">
        <?php echo $event; ?>
    </div>
    <div id="categories_container">
        <!-- CAT -->
        <?php echo $CATEGORIES_HTML;?>
    </div>
    <div id="controls_container">
        Riepilogo
        <div id="order_container">
             <!-- ORD -->
            <?php echo $ORDER_HTML;?>
        </div>
        <div id="total_container">
            Totale: <input type="text" size="5" id="total" value="0.00" readonly>&euro;
        </div>
        <div id="confirm_container">
            <form id="confirm_form">
                Servizio <input type="checkbox" id="staff" value="1">
                <br><br>
                Nome Cliente <input type="text" id="customer" required> <input type="submit" id="order_confirm" value="Conferma">
            </form>
        </div>
    </div>

<!------------ JQUERY -------------->
<script type="text/javascript">
    
    $("#total").val(0);
    $("#customer").val("");
    
    $('#index').button({icons: {primary: 'ui-icon-document'}});
    $('#report').button({icons: {primary: 'ui-icon-calculator'}});
    $('#order_list').button({icons: {primary: 'ui-icon-note'}});
    $('#manage').button({icons: {primary: 'ui-icon-key'}});
    
    $("#order_confirm").button();
    
    $(".button_plus_item").button();
    
    $(".item").button().click( function() {
        
			$("#total").val(parseFloat($("#total").val()) + parseFloat($(this).attr("price")));
			qty = "#qty_item_"+$(this).attr("item");
			$(qty).val(parseInt($(qty).val()) + 1);
			$(qty).css("color","red");
        
            $("#order_item_"+$(this).attr("item")+" input").val(parseInt($(qty).val()));
            $("#order_item_"+$(this).attr("item")).show();
    });
    
    $(".minus_item").button().click(function() {
			qty = "#qty_item_"+$(this).attr("item");

			if (parseInt($(qty).val()) != 0){
				$("#total").val(parseFloat($("#total").val()) - parseFloat($("#item_"+$(this).attr("item")).attr("price")));
				$(qty).val(parseInt($(qty).val()) - 1);
                
                $("#order_item_"+$(this).attr("item")+" input").val(parseInt($(qty).val()));

				if (parseInt($(qty).val()) == 0) {
					$(qty).css("color","black");
                    $("#order_item_"+$(this).attr("item")).hide();
				}
			}
    });
    
    $("#confirm_form").submit(function(event){
        event.preventDefault();
        var order = "";
        
        $(".qty_item").each(function(){
            if (parseInt($(this).val()) != 0) {
                order += $(this).attr("item")+":"+$(this).val()+":"+$(this).attr("cat")+";";
            }
        });
        
        var staff = 0;
        if($("#staff").is(":checked"))
            staff = 1;
        
        $.ajax({
		      type: "POST",
		      url: "functions.php",
		      data: {
                  func: 'processOrder',
                  eventID: <?php echo $eventID;?>,
                  customer: $("#customer").val(),
                  order: order,
                  total: $("#total").val(),
                  staff: staff
              },
		      dataType: "text",
		      success: function(response){
                  //alert(response);
                  myWindow = window.open('print.php?ID='+response);
				  location.reload();
		      }
        });
    });
</script>

</body>
</html>