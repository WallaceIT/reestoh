<?php
    require('config.php');
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
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
    <link rel="stylesheet" href="style.css"/>
    <link rel="stylesheet" href="js/jquery-ui.css"/>
    <script src="js/jquery.min.js" type="text/javascript"></script>
    <script src="js/jquery-ui.min.js" type="text/javascript"></script>
    <script src="js/admin.js" type="text/javascript"></script>
</head>
<body>
    <?php include('toolbar.htm'); ?>
    <div id="event_name_category">Reestoh Administration Panel</div>
    <div id="admin_messagebox"><?php if(isset($_GET['noactive'])) echo "ATTENZIONE: CREARE E/O ATTIVARE UN EVENTO PER UTILIZZARE L'APPLICAZIONE"; ?></div>
    
    <?php if(!$events) { ?>
    
    <div class="admin_opt_block">
        <b>Attenzione!</b><br>
        Nel database specificato in config.php non sono state trovate le tabelle di Reestoh.<br>
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
                <th>Svuota</th>
                <th>Elimina</th>
            </tr>
        <?php while($row_events = $events -> fetch(PDO::FETCH_ASSOC)){            
            echo "
            <tr ".($row_events['active']?"class='active_event'":"").">
                <td>$row_events[ID]</td>
                <td>$row_events[name]</td>
                <td>".($row_events['active']?"_ATTIVO_":"<button class=\"activate_event\" evID=\"$row_events[ID]\">Attiva</button>")."</td>
                <td><button class=\"view_report\" evID=\"$row_events[ID]\">Report</button></td>
                <td><button class=\"truncate_event\" evID=\"$row_events[ID]\">Svuota</button></td>
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
</body>
</html>