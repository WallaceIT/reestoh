$(window).load(function() {
    $("#report_next_day").button()
                        .click(function(){
                            $("#report_next_day_popup").dialog({
                                modal: true,
                                draggable: false,
                            });
                        });

    $("#report_next_day_confirm").button()
                         .click(function(){
                             $.ajax({
                                 type: "POST",
                                 url: "functions.php",
                                 data: {
                                     func: "newDay",
                                     eventID: eventID,
                                     evdayn: last_evdayn,
                                     nextdate: $("#report_next_day_date").val()
                                 },
                                 dataType: "text",
                                 success: function(response){
                                     alert(response);
                                     location.reload();
                                 }
                             });
                         });
});
