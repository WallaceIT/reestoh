<?php
    require('db.php');
    if(!isset($_GET['default'])){
        $events = $db -> query('SELECT * FROM events ORDER BY ID ASC');
    }
    else {
        $event = 'Default';
        $eventID = 0;
    }

    $newevent_options = "";
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Administration Panel</title>
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
    <div id="event_name_category">Reestoh Administration Panel</div>
    <div id="admin_messagebox"><?php if(isset($_GET['noactive'])) echo "ATTENZIONE: CREARE E/O ATTIVARE UN EVENTO PER UTILIZZARE L'APPLICAZIONE"; ?></div>
    
    <?php if(!$events) { ?>
    
    <div class="admin_opt_block">
        <b>Attenzione!</b><br>
        Nel database specificato in db.php non sono state trovate le tabelle di Reestoh.<br>
        Premi sul pulsante Popola per creare le tabelle ed inserire i valori di default.<br>
        <br>
        <button id="admin_db_populate">Popola</button>
    </div>
    
    <?php ;} else { ?>
    
    <div class="admin_opt_block">
        Eventi
        <br>
        <table id="admin_event_table">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Rendi attivo</th>
                <th>Mostra report</th>
                <th>Elimina</th>
            </tr>
        <?php while($row_events = $events -> fetch(PDO::FETCH_ASSOC)){
            
            if($row_events['active']==0)
                $active_addition = "";
            else
                $active_addition = "style='background-color: #ceefff'";
            
            echo "
            <tr $active_addition>
                <td>$row_events[ID]</td>
                <td>$row_events[name]</td>
                <td><button class=\"activate_event\" evID=\"$row_events[ID]\">Attiva</button></td>
                <td><button class=\"view_report\" evID=\"$row_events[ID]\">Report</button></td>
                <td><button class=\"delete_event\" evID=\"$row_events[ID]\">Elimina</button></td>
            </tr>";
        
            $newevent_options .= "<option value=\"$row_events[ID]\">$row_events[name]</option>".PHP_EOL;
        }
        ?>
        </table>
        <br>
        <button id="admin_newevent">Nuovo evento</button>
        
        <div id="admin_newevent_popup" class="hidden">
            <form id="admin_newevent_form">
                <input type="text" length="25" id="admin_newevent_name" placeholder="Nome Evento" required>
                <br><br>
                Copia dati da: 
                <select id="admin_newevent_copy">
                    <option value="0" selected>Default</option>
                    <?php echo $newevent_options; ?>
                </select>
                <br><br>
                <input type="submit" id="admin_newevent_confirm" value="Crea nuovo evento">
            </form>
        </div>
        
    </div> <?php ;} ?>
<!------------ JQUERY -------------->
<script type="text/javascript">
    
    $('#index').button({icons: {primary: 'ui-icon-document'}});
    $('#report').button({icons: {primary: 'ui-icon-calculator'}});
    $('#order_list').button({icons: {primary: 'ui-icon-note'}});
    $('#manage').button({icons: {primary: 'ui-icon-key'}});
    
    /*$("#admin_save_printer").click(function(){
                        var SQL = "UPDATE printer SET `name` = '"+$("#admin_set_printer").val()+"'";
                        $.ajax({
					       type: "POST",
					       url: "functions.php",
					       data: {
                               func: 'editMenu',
                               sql: SQL},
					       dataType: "text",
                            success: function(response){
						      alert(response);
					       }
				        });
                     });*/
    
    <?php if(!$events){ ?>
    
    $("#admin_db_populate").button().click(function(){
        $.post("setup.php", function(response){
            alert(response);
            document.location.reload(true);
        });
    });
    
    <?php ;} else { ?>
    
    $(".activate_event").click(function(){
                          if (confirm("Rendere attivo questo evento?")) {
                                $.ajax({
					               type: "POST",
					               url: "functions.php",
					               data: {func: 'activateEvent', eventID: $(this).attr("evID")},
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
                                    data: {func: 'newEvent', name: $("#admin_newevent_name").val(), copyID: $("#admin_newevent_copy").val()},
                                    dataType: "text",
                                    success: function(response){
                                       document.location.reload(true);
                                    }
                                 });
                             });
    
    <?php ;} ?>
</script>
</body>
</html>