<?php
    require('db.php');
    if(!isset($_GET['default'])){
        $events = $db -> query('SELECT * FROM events ORDER BY ID DESC LIMIT 0,1');
        $count = $events->rowCount();
        if($count){
            $row_events = $events -> fetch(PDO::FETCH_ASSOC);
            $event = $row_events['name'];
            $eventID = $row_events['ID'];
        }
        else{
            $db -> query("INSERT INTO events (`ID`, `name`) VALUES (NULL, 'Nuovo Evento')");
            $eventID = $db -> lastInsertId();
            $event = 'Nuovo Evento';
            $db -> query("CREATE TABLE categories_".$eventID." LIKE categories_0");
            $db -> query("INSERT categories_".$eventID." SELECT * FROM categories_0;");
            $db -> query("CREATE TABLE items_".$eventID." LIKE items_0");
            $db -> query("INSERT items_".$eventID." SELECT * FROM items_0;");
			$db -> query("CREATE TABLE orders_".$eventID." LIKE orders_0");
        }
    }
    else {
        $event = 'Default';
        $eventID = 0;
    }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Management - <?php echo $event; ?> - Reestoh 2014</title>
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
    <button id="mng_newevent">Nuovo Evento</button>
    <div id="event_name">
        <input type="text" id="mng_event_name" value="<?php echo $event; ?>">
    </div>
    
    <div id="mng_cat_container">        
        <?php
            $cats = $db -> query("SELECT * FROM categories_$eventID ORDER BY ID asc");
            while ($row_cats = $cats -> fetch(PDO::FETCH_ASSOC)) {
                $ID = $row_cats['ID']; ?>
                <div class="group">
                    <h3 id="cat_<?php echo $ID;?>" cat="<?php echo $ID;?>" name="<?php echo $row_cats['name'];?>">
                        <?php echo $row_cats['name'];?>
                        <?php if($ID>1) {?><button style="float:right" class="remove_cat" cat="<?php echo $ID;?>"></button><?php ;} ?>
                    </h3>
                    <div id="cat_<?php echo $ID;?>_items">
                        <?php
                            $items = $db -> query("SELECT * FROM items_$eventID WHERE category = $ID");
                            while ($row_items = $items -> fetch(PDO::FETCH_ASSOC)) {
                            $item_ID = $row_items['ID'];?>
                                <div class="mng_item ui-accordion-header ui-state-default ui-accordion-icons" cat="<?php echo $row_items['category'];?>" id="mng_item_<?php echo $item_ID;?>" item="<?php echo $item_ID;?>">
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
    
    <button id="mng_add_cat">Aggiungi Categoria</button>
    <div id="mng_modal_add_cat" title="Aggiungi categoria:" class="hidden">
        <br>
        <input type="text" id="modal_add_cat_name" required>
        <br><br>
        <button style="float:right" id="mng_add_cat_ok">Ok</button>
    </div>
    
    <input tye="submit" id="mng_save" value="Salva">

<!------------ JQUERY -------------->
<script type="text/javascript">
    
    var cat_lastid = <?php echo $ID;?>;
    var item_lastid = <?php echo $item_ID;?>;
    var SQL = '';
    
    $('#index').button({icons: {primary: 'ui-icon-document'}});
    $('#report').button({icons: {primary: 'ui-icon-calculator'}});
    $('#order_list').button({icons: {primary: 'ui-icon-note'}});
    $('#manage').button({icons: {primary: 'ui-icon-key'}});
    
    $("#mng_event_name").change(function(){
        SQL += "UPDATE events SET `name` = '"+$(this).val()+"' WHERE ID = <?php echo $eventID; ?>§";
        $("#mng_save").addClass("ui-state-error");
    });
    
    $("#mng_newevent").button()
                      .click(function(){
                          if (confirm('Creare un nuovo evento?')) {
                                $.ajax({
					               type: "POST",
					               url: "functions.php",
					               data: {func: 'newEvent'},
					               dataType: "text",
					               success: function(){
						              document.location.reload(true);
					               }
				                });
                            }
                      });
    
    $("#mng_cat_container").accordion({ active: "false", collapsible: "true", header: "> div > h3"})
                           .sortable({
                                axis: "y",
                                handle: "h3",
                                stop: function( event, ui ) {
                                    ui.item.children("h3").triggerHandler( "focusout" );
                                    // Refresh accordion to handle new order
                                    $( this ).accordion("refresh");
                                }
                            });
    
    // add category
    $("#mng_add_cat").button({ icons: { primary: "ui-icon-plusthick" }})
                     .click(function(){
                        $( "#mng_modal_add_cat" ).dialog({
                            resizable: false,
                            modal: true
                        });
                     });
    
    
    $("#mng_add_cat_ok").button({ icons: { primary: "ui-icon-check" }})
                        .click(function(){
                            if($("#modal_add_cat_name").val()){
                                cat_lastid += 1;
                                $("#mng_cat_container").append(

                                    "<div class='group'><h3 id='cat_"+cat_lastid+"' cat='"+cat_lastid+"' name='"+$('#modal_add_cat_name').val()+"' class='newcat'>"+$('#modal_add_cat_name').val()+"<button style='float:right' class='remove_cat' cat='"+cat_lastid+"'></button></h3><div id='cat_"+cat_lastid+"_items'><br><button class='mng_add_item' cat='"+cat_lastid+"'>+</button></div></div>"
                                ).accordion( "refresh" );
                                
                                $(".remove_cat").button({icons:{primary: "ui-icon-closethick"},text:"false"});

                                $(".mng_add_item").button({ icons: { primary: "ui-icon-plusthick" },
                                                            text: false})

                                $("#mng_save").addClass("ui-state-error");
                                $( "#mng_modal_add_cat" ).dialog( "close" );
                        }
                        });
    
    
    $("#modal_add_cat_ok").button({ icons: { primary: "ui-icon-check" },
                                    text: false});
    
    // remove category
    $(".remove_cat").button({icons:{primary: "ui-icon-closethick"},text:"false"});
    $(document).on('click',".remove_cat",function(event){
        event.preventDefault();
        var id = $(this).attr("cat");
        
        if (confirm('Eliminare compleamente la categoria e tutti i suoi prodotti?')) {
            
            // add SQL DELETE statement only if it's a pre-existent category
            if(!$(this).parent().parent().hasClass("newcat")){
                SQL += "DELETE FROM categories_<?php echo $eventID; ?> WHERE ID = "+id+"§";
                SQL += "DELETE FROM items_<?php echo $eventID; ?> WHERE category = "+id+"§";
            }
            
            $("#cat_"+id).remove();
            $("#cat_"+id+"_items").remove();
            $("#mng_save").addClass("ui-state-error");
        }
    });
    
    // add item
    $(".mng_add_item").button({ icons: { primary: "ui-icon-plusthick" },
                                text: false})
    $(document).on("click", ".mng_add_item", function(){
        item_lastid++;
        $('<div class="mng_item mng_item_new ui-accordion-header ui-state-default ui-accordion-icons" cat="'+$(this).attr('cat')+'" id="mng_item_'+item_lastid+'" item="'+item_lastid+'"> <input type="text" size="30" id="mng_item_name_'+item_lastid+'" placeholder="Name..." required> &euro; <input type="number" id="mng_item_price_'+item_lastid+'" min="0" step="0.5" placeholder="0" required><span style="float:right"><a class="ui-icon ui-icon-closethick remove_item" href="" item="'+item_lastid+'">X</a></span></div>').insertBefore($(this));
        $("#mng_cat_container").accordion("refresh");
        $("#mng_save").addClass("ui-state-error");
    });
    
    // edit item
    $(".mng_item input").change(function(){
        $(this).parent().addClass("mng_item_edited");
        $("#mng_save").addClass("ui-state-error");
    });
    
    // remove item
    $(document).on("click",".remove_item",function(event){
        event.preventDefault();
        var id = $(this).attr("item");
        
        // add SQL DELETE statement only if it's a pre-existent category
        if(!$(this).parent().parent().hasClass("mng_item_new")){
            SQL += "DELETE FROM items_<?php echo $eventID; ?> WHERE ID = "+id+"§";
        };
            
        $("#mng_item_"+id).remove();
        $("#mng_save").addClass("ui-state-error");
    });
    
    $("#mng_save").button({ icons: { secondary: "ui-icon-disk" } })
                  .click(function(){
                      // new categories
                      $(".newcat").each(function(){
                          SQL += "INSERT INTO `categories_<?php echo $eventID; ?>` (`id`, `name`) VALUES ('"+$(this).attr('cat')+"','"+$(this).attr('name')+"')§";
                          $(this).removeClass("newcat");
                      });
                      // new items
                      $(".mng_item_new").each(function(){
                          if($('#mng_item_name_'+$(this).attr('item')).val()){
                              SQL += "INSERT INTO `items_<?php echo $eventID; ?>` (`id`, `name`, `price`, `category`) VALUES ('"+$(this).attr('item')+"','"+$('#mng_item_name_'+$(this).attr('item')).val()+"','"+$('#mng_item_price_'+$(this).attr('item')).val()+"','"+$(this).attr('cat')+"')§";
                              $(this).removeClass("mng_item_new");
                          }
                      });
                      // edited items
                      $(".mng_item_edited").each(function(){
                          if($('#mng_item_name_'+$(this).attr('item')).val()){
                              SQL += "UPDATE items_<?php echo $eventID; ?> SET `name` = '"+$('#mng_item_name_'+$(this).attr('item')).val()+"', `price` = '"+$('#mng_item_price_'+$(this).attr('item')).val()+"' WHERE ID = "+$(this).attr('item')+"§";
                              $(this).removeClass("mng_item_edited");
                          }
                              
                      });
                      
                      $.ajax({
					       type: "POST",
					       url: "functions.php",
					       data: {
                               func: 'editMenu',
                               sql: SQL},
					       dataType: "text"
				      });
                      SQL='';
                      $("#mng_save").removeClass("ui-state-error");
                  });
</script>

</body>
</html>