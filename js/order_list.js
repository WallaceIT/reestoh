$(window).load(function() {
    $('.delete_order').button({icons: {primary: 'ui-icon-closethick'}});
    $('.print_order').button({icons: {primary: 'ui-icon-print'}});

    $("#olist_orders_container").accordion({
        collapsible: true,
        active: false,
        clearStyle: true,
        autoHeight: false,
        header: '.olist_order_header',
        heightStyle: 'content',
        icons: false,
        beforeActivate: function (event,ui) {
            if (ui.newPanel.html() == '') {
                $.ajax({
                    type: "POST",
                    url: "functions.php",
                    data: {
                        func: "getOrderDetails",
                        orderID: $(ui.newHeader[0]).attr("order"),
                        eventID: eventID,
                        evday: evday
                    },
                    dataType: "text",
                    success: function(response){
                        ui.newPanel.html(response);
                    }
                });
            }
        }
    });

    $(".delete_order").click(function(){
        var orderID = $(this).attr("order");
        if (confirm("Eliminare l'ordine numero "+orderID+"?")) {

            $.ajax({
                type: "POST",
                url: "functions.php",
                data: {
                    func: "deleteOrder",
                    orderID: orderID,
                    eventID: eventID,
                    evday: evday
                },
                dataType: "text",
                success: function(response){
                    $("#order_"+orderID).remove();
                    $("#details_order_"+orderID).remove();
                }
            });
        }
    });

    $(".print_order").click(function(){
        var orderID = $(this).attr("order");
        $("#frame").attr("src", "print/print_"+printMethod+".php?ID="+orderID);
    });
});
