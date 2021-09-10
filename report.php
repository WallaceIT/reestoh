<?php
    require('config.php');

    if(isset($_GET['eventID']))
        $events = $db -> query("SELECT * FROM events WHERE ID = $_GET[eventID]");
    else
        $events = $db -> query("SELECT * FROM events WHERE active > 0");

    if(!$events)
        header("Location: admin.php");

    $count = $events->rowCount();
    if($count){
        $row_events = $events -> fetch(PDO::FETCH_ASSOC);
        $event = $row_events['name'];
        $eventID = $row_events['ID'];
        $discount = $row_events['discount'];
    }
    else header("Location: admin.php?noactive");

    if(isset($_GET['evdayn']) && $_GET['evdayn'] != '')
        $evdayn = $_GET['evdayn'];
    elseif($row_events['active'] > 0)
        $evdayn = $row_events['active'];
    else
        $evdayn = 1;

    $evday = $eventID.'_'.$evdayn;

    $cats = $db -> query("SELECT * FROM categories_$evday");

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Report - <?php echo $event; ?></title>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
    <link rel="stylesheet" href="style.css"/>
    <link rel="stylesheet" href="js/jquery-ui.css"/>
    <script src="js/jquery.min.js" type="text/javascript"></script>
    <script src="js/jquery-ui.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        var eventID = <?php echo $eventID; ?>;
        var evdayn = '<?php echo $evdayn; ?>';
        var last_evdayn = '<?php echo $row_events['active']; ?>';

        $(window).load(function() {
            $("#report_export_csv").button()
                                   .click(function(){
                                       location.href = "export_csv.php?eventID=<?php echo $eventID;?>&evdayn="+$("#report_select_evday").val();
                                   });
            $("#report_select_evday").change(function() {
                location.href = "report.php?eventID=<?php echo $eventID;?>&evdayn="+$("#report_select_evday").val();
            });
        });
    </script>
    <script src="js/report.js" type="text/javascript"></script>
</head>
<body>
    <?php include('toolbar.htm'); ?>
    <div id="event_name">
        <?php echo $event; ?> - Report -
        <select id="report_select_evday">
            <option value="" selected>Ultimo giorno</option>
            <?php
                $days = preg_split("/;/", $row_events['days'], -1, PREG_SPLIT_NO_EMPTY);
                foreach ($days as $day) {
                    $dt = preg_split("/:/", $day, -1, PREG_SPLIT_NO_EMPTY);
                    $evday_next = $eventID.'_'.$dt[0];
                    $sel = '';
                    if ($evday_next == $evday)
                        $sel = 'selected';
                    echo "<option value='$dt[0]' $sel>$dt[1]</option>";
                }
            ?>
        </select>
    </div>
    <div id="report_container">
    <?php
        $total = 0;
        while ($row_cats = $cats -> fetch(PDO::FETCH_ASSOC)) {
            $catID = $row_cats['ID'];
            $items = $db -> query("SELECT * FROM items_$evday WHERE category = $catID");
            $count = $items -> rowCount();
            if($count){
                $sold_cat_total = 0;
                $cash_cat_total = 0;
                $staff_given_cat_total = 0;
                echo "<div class='report_catname'>$row_cats[name]</div>";
                echo "<table class='report_cat_table'>
                          <tr class='ui-accordion-header ui-state-default'>
                            <th width='70%'>Prodotto</th>
                            <th width='10%'>Totale</th>
                            <th width='10%'>Venduti</th>
                            <th width='10%'>Servizio</th>
                          </tr>";

                while ($row_items = $items -> fetch(PDO::FETCH_ASSOC)) {
                    $cash = $row_items['sold']*$row_items['price'];

                    $cashStaff = ($row_items['staff_given']*$row_items['price']) * (1 - $discount / 100);
                    $cash += $cashStaff;

                    $sold_cat_total += $row_items['sold'];
                    $staff_given_cat_total += $row_items['staff_given'];
                    $cash_cat_total += $cash;
                    echo "<tr>
                            <td>$row_items[name]</td>
                            <td>$cash&euro;</td>
                            <td>$row_items[sold]</td>
                            <td>$row_items[staff_given]</td>
                          <tr>";
                }
                echo "<tr>
                        <td><b>Totale Categoria</b></td>
                        <td><b>$cash_cat_total&euro;</b></td>
                        <td>$sold_cat_total</td>
                        <td>$staff_given_cat_total</td>
                      </tr>";
                echo "</table>";
                $total += $cash_cat_total;
            }
        }
        ?>

        <div id='report_total'>
                Totale: <?php echo $total; ?>&euro; <button id='report_export_csv'>Esporta CSV</button>
        </div>
    <div align="center">
    <?php
        if($row_events['active'] > 0) {
            echo "<button id='report_next_day'>Nuova giornata</button>";
        }
    ?>
    </div>
    <p>&nbsp;</p>
    </div>
    <div id="report_next_day_popup" class="hidden">
        <form id="report_next_day_form">
            Data nuova giornata
            <br>
            (GG/MM/AAAA)
            <br><br>
            <input type="text" length="10" id="report_next_day_date" value="<?php echo date("d/m/Y");?>" required>
            <br><br>
            <input type="submit" id="report_next_day_confirm" value="Nuova giornata">
        </form>
    </div>
</body>
</html>
