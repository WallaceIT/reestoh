<?php
    require('db.php');

    if(isset($_GET['eventID']))
        $events = $db -> query("SELECT * FROM events WHERE ID = $_GET[eventID]");
    else
        $events = $db -> query("SELECT * FROM events ORDER BY ID DESC LIMIT 0,1");

    if(!$events)
        header("Location: admin.php");

    $count = $events->rowCount();
    if($count){
        $row_events = $events -> fetch(PDO::FETCH_ASSOC);
        $event = $row_events['name'];
        $eventID = $row_events['ID'];
    }
    else header("Location: admin.php?noactive");

    $cats = $db -> query("SELECT * FROM categories_$eventID");
    
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Report - <?php echo $event; ?></title>
    <link rel="stylesheet" href="style.css"/>
    <link rel="stylesheet" href="js/jquery-ui.css"/>
    <script src="js/jquery.min.js" type="text/javascript"></script>
    <script src="js/jquery-ui.min.js" type="text/javascript"></script>
    
</head>
<body>
    <?php if(!isset($_GET['eventID'])){ ?>
    <div id="toolbar">
        <a href="index.php" id="index" title="Nuovo Ordine"></a>
        <a href="report.php" id="report" title="Statistiche"></a>
        <a href="order_list.php" id="order_list" title="Lista Ordini"></a>
        <a href="manage.php" id="manage" title="Modifica Menù"></a>
    </div>
    <?php ;} ?>
    <div id="event_name">
        <?php echo $event; ?> - Report
    </div>
    <div id="report_container">
    <?php
        $total = 0;
        while ($row_cats = $cats -> fetch(PDO::FETCH_ASSOC)) {
            $catID = $row_cats['ID'];
            $items = $db -> query("SELECT * FROM items_$eventID WHERE category = $catID");
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

        echo "<div id='report_total'>Totale: $total&euro;</div>";
    ?>
    </div>

<script type="text/javascript">
    
    $('#index').button({icons: {primary: 'ui-icon-document'}});
    $('#report').button({icons: {primary: 'ui-icon-calculator'}});
    $('#order_list').button({icons: {primary: 'ui-icon-note'}});
    $('#manage').button({icons: {primary: 'ui-icon-key'}});
    
</script>
</body>
</html>
