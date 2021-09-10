$(window).load(function() {

    $("#admin_db_populate").button().click(function(){
        $.post("setup.php", function(response){
            alert(response);
            document.location.reload(true);
        });
    });

    $(".activate_event").click(function(){
                          if (confirm("Rendere attivo questo evento?")) {
                                $.ajax({
                                   type: "POST",
                                   url: "functions.php",
                                   data: { func: 'activateEvent',
                                           eventID: $(this).attr("evID"),
                                           evdayn: $(this).attr("evdayn")
                                         },
                                   dataType: "text",
                                   success: function(){
                                      document.location.reload(true);
                                   }
                                });
                            }
                      });

    $(".view_report").click(function(){
                        window.open("report.php?eventID="+$(this).attr("evID"));
                     });

    $(".truncate_event").click(function(){
                            if (confirm("ATTENZIONE: questo eliminerà tutti i dati relativi agli ordini dell'evento selezionato. Continuare?")) {
                                $.ajax({
                                   type: "POST",
                                   url: "functions.php",
                                   data: {func: 'truncateEvent', eventID: $(this).attr("evID")},
                                   dataType: "text",
                                   success: function(){
                                      document.location.reload(true);
                                   }
                                });
                            }
                        });

    $(".delete_event").click(function(){
                            if (confirm("ATTENZIONE: questo eliminerà l'evento selezionato e TUTTI i dati ad esso relativi. Continuare?")) {
                                $.ajax({
                                   type: "POST",
                                   url: "functions.php",
                                   data: {func: 'deleteEvent', eventID: $(this).attr("evID")},
                                   dataType: "text",
                                   success: function(){
                                      document.location.reload(true);
                                   }
                                });
                            }
                        });

    $("#admin_newevent").button()
                        .click(function(){
                            $("#admin_newevent_popup").dialog({
                                modal: true,
                                draggable: false,
                            });
                        });

    $("#admin_newevent_confirm").button();

    $("#admin_newevent_form").submit(function(event){
                                 event.preventDefault();
                                 $.ajax({
                                    type: "POST",
                                    url: "functions.php",
                                    data: { func: 'newEvent',
                                            name: $("#admin_newevent_name").val(),
                                            firstday: $("#admin_newevent_firstday").val(),
                                            copyID: $("#admin_newevent_copy").val()},
                                    dataType: "text",
                                    success: function(response){
                                       document.location.reload(true);
                                    }
                                 });
                             });
});
