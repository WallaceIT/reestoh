$(window).load(function() {
    $("#total").val(0);
    $("#customer").val("");

    $("#order_confirm").button();
    $(".button_plus_item").button();

    var discount = 100;
    $("#realtotal").val(0);
    $.ajax({
        type: "POST",
        url: "functions.php",
        data: {
            func: 'getDiscount',
            eventID: eventID
        },
        dataType: "text",
        success: function(response){
            discount = parseFloat(response);
        }
    });
    w = $("#categories_container").width();
    if(w/2 > 450)
        $(".item_container").width(w/2-15);
    else
        $(".item_container").width(w-20);

    $(".item").button().click( function() {

        $("#realtotal").val((parseFloat($("#realtotal").val()) + parseFloat($(this).attr("price"))).toFixed(2));
        $("#total").val($("#realtotal").val());
        if($("#staff").is(":checked")){
            var new_val = parseFloat($("#realtotal").val()) * (1 - (discount / 100));
            $("#total").val(parseFloat(new_val).toFixed(2));
        }

        qty = "#qty_item_"+$(this).attr("item");
        $(qty).val(parseInt($(qty).val()) + 1);
        $(qty).css("color","red");

        $("#order_item_"+$(this).attr("item")+" input").val(parseInt($(qty).val()));
        $("#order_item_"+$(this).attr("item")).show();
    });

    $(".minus_item").button().click(function() {
        qty = "#qty_item_"+$(this).attr("item");

        if (parseInt($(qty).val()) != 0){

            $("#realtotal").val(parseFloat($("#realtotal").val()) - parseFloat($("#item_"+$(this).attr("item")).attr("price")).toFixed(2));

            $("#total").val(parseFloat($("#realtotal").val()).toFixed(2));
            if($("#staff").is(":checked")){
                var new_val = parseFloat($("#realtotal").val()) * (1 - (discount / 100));
                $("#total").val(parseFloat(new_val).toFixed(2));
            }

            $(qty).val(parseInt($(qty).val()) - 1);

            $("#order_item_"+$(this).attr("item")+" input").val(parseInt($(qty).val()));

            if (parseInt($(qty).val()) == 0) {
                $(qty).css("color","black");
                    $("#order_item_"+$(this).attr("item")).hide();
            }
        }
    });

    $("#staff").click( function() {
        if($("#staff").is(":checked")){
            var new_val = parseFloat($("#realtotal").val()) * (1 - (discount / 100));
            $("#total").val(parseFloat(new_val).toFixed(2));
        } else {
            $("#total").val(parseFloat($("#realtotal").val()).toFixed(2));
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
        if($("#staff").is(":checked")){
            staff = 1;
            $("#dialog_total").html($("#total").val());
        }
        else{
            $("#dialog_total").html($("#total").val());
        }

        $("#printing_dialog").dialog({
            modal: true,
            dialogClass: 'no-close',
            closeOnEscape: false,
            draggable: false,
        });

        $.ajax({
            type: "POST",
            url: "functions.php",
            data: {
                func: 'processOrder',
                eventID: eventID,
                evday: evday,
                customer: $("#customer").val(),
                order: order,
                total: $("#total").val(),
                staff: staff
            },
            dataType: "text",
            success: function(response){
                $("#frame").attr("src", "print/print_"+printMethod+".php?ID="+response);
                $("#printing_dialog_close").button().show().click(function(){
                    $("#frame").attr("src", "blank.htm");
                    setTimeout(function(){location.reload();}, 1000);
                });
            },
            error: function(){
                alert("Si è verificato un errore. Ritenta.");
                $("#printing_dialog").dialog('close');
            }
        });
    });
});
