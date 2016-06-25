$(window).load(function() {
    var SQL = '';
    var cat_order_changed = 0;
    var item_order_changed = 0;
    
    $("#mng_event_name").change(function(){
        SQL += "UPDATE events SET `name` = '"+$(this).val()+"' WHERE ID = "+eventID;
        $("#mng_save").addClass("ui-state-error");
    });
    
    $("#mng_cat_container").accordion({
                                active: "false",
                                collapsible: "true",
                                icons: false,
                                header: "> div > h3",
                                heightStyle: "fill"
                            })
                           .sortable({
                                axis: "y",
                                handle: ".cat_handle",
                                stop: function( event, ui ) {
                                    ui.item.children("h3").triggerHandler( "focusout" );
                                    cat_order_changed = 1;
                                    $("#mng_save").addClass("ui-state-error");
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
                                    "<div class='group'><h3 id='cat_"+cat_lastid+"' cat='"+cat_lastid+"' name='"+$('#modal_add_cat_name').val()+"' class='newcat'><span class='handle ui-icon ui-icon-arrowthick-2-n-s'></span>"+$('#modal_add_cat_name').val()+"<button style='float:right' class='remove_cat' cat='"+cat_lastid+"'></button></h3><div id='cat_"+cat_lastid+"_items'><br><button class='mng_add_item' cat='"+cat_lastid+"'>+</button></div></div>"
                                ).accordion( "refresh" ).sortable( "refresh" );
                                
                                $(".remove_cat").button({icons:{primary: "ui-icon-closethick"},text:"false"});

                                $(".mng_add_item").button({ icons: { primary: "ui-icon-plusthick" },
                                                            text: false})

                                cat_order_changed = 1;
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
                SQL += "DELETE FROM categories_"+eventID+" WHERE ID = "+id+"§";
                SQL += "DELETE FROM items_"+eventID+" WHERE category = "+id+"§";
            }
            
            $("#cat_"+id).remove();
            $("#cat_"+id+"_items").remove();
            cat_order_changed = 1;
            $("#mng_save").addClass("ui-state-error");
        }
    });
    
    // add item
    $(".mng_add_item").button({ icons: { primary: "ui-icon-plusthick" },
                                text: false})
    $(document).on("click", ".mng_add_item", function(){
        item_lastid++;
        $('<div class="mng_item mng_item_new ui-accordion-header ui-state-default ui-accordion-icons" cat="'+$(this).attr('cat')+'" id="mng_item_'+item_lastid+'" item="'+item_lastid+'"> <span class="item-handle handle handle ui-icon ui-icon-arrowthick-2-n-s"></span> <input type="text" size="30" id="mng_item_name_'+item_lastid+'" placeholder="Name..." required> &euro; <input type="number" id="mng_item_price_'+item_lastid+'" min="0" step="0.5" placeholder="0" required><span style="float:right"><a class="ui-icon ui-icon-closethick remove_item" href="" item="'+item_lastid+'">X</a></span></div>').insertBefore($(this));
        $("#mng_cat_container").accordion("refresh");
        item_order_changed = 1;
        $("#mng_save").addClass("ui-state-error");
    });

    $(".sortable_cat").sortable({
                        axis: "y",
                        handle: ".item_handle",
                        stop: function( event, ui ) {
                            ui.item.children("h3").triggerHandler( "focusout" );
                            item_order_changed = 1;
                            $("#mng_save").addClass("ui-state-error");
                        }
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
            SQL += "DELETE FROM items_"+eventID+" WHERE ID = "+id+"§";
        };
            
        $("#mng_item_"+id).remove();
        $("#mng_save").addClass("ui-state-error");
    });
    
    $("#mng_save").button({ icons: { secondary: "ui-icon-disk" } })
                  .click(function(){
                      // new categories
                      $(".newcat").each(function(){
                          SQL += "INSERT INTO `categories_"+eventID+"` (`id`, `name`) VALUES ('"+$(this).attr('cat')+"','"+$(this).attr('name')+"')§";
                          $(this).removeClass("newcat");
                      });
                      if(cat_order_changed == 1){
                          $(".group").each(function(){
                              SQL += "UPDATE categories_"+eventID+" SET `displayorder` = '"+$(this).index()+"' WHERE ID = "+$(this).children().attr('cat')+"§";
                          });
                      }
                      // new items
                      $(".mng_item_new").each(function(){
                          if($('#mng_item_name_'+$(this).attr('item')).val()){
                              SQL += "INSERT INTO `items_"+eventID+"` (`id`, `name`, `price`, `category`) VALUES ('"+$(this).attr('item')+"','"+$('#mng_item_name_'+$(this).attr('item')).val()+"','"+$('#mng_item_price_'+$(this).attr('item')).val()+"','"+$(this).attr('cat')+"')§";
                              $(this).removeClass("mng_item_new");
                          }
                      });
                      // edited items
                      $(".mng_item_edited").each(function(){
                          if($('#mng_item_name_'+$(this).attr('item')).val()){
                              SQL += "UPDATE items_"+eventID+" SET `name` = '"+$('#mng_item_name_'+$(this).attr('item')).val()+"', `price` = '"+$('#mng_item_price_'+$(this).attr('item')).val()+"' WHERE ID = "+$(this).attr('item')+"§";
                              $(this).removeClass("mng_item_edited");
                          }
                              
                      });
                      // moved items
                      if(item_order_changed == 1){
                          $(".mng_item").each(function(){
                              SQL += "UPDATE items_"+eventID+" SET `displayorder` = '"+$(this).index()+"' WHERE ID = "+$(this).attr('item')+"§";
                          });
                      }
                      
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
});