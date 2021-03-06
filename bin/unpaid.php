<?php

  require_once dirname(__FILE__) . "/../include/config.php";

  date_default_timezone_set($local_timezone);
  
  $db = mysql_connect($db_host, $db_user, $db_password);
  if (!$db) die('Error connecting to db');

  mysql_select_db($db_db);

  $now = date("Y-m-d\TH:i:s");

  $sum_mon = 0;
  $sum_vtc = 0;

  $output = '';
  $tbody = '<tbody>';
  $thead = '';
  $tfoot = '';
  
  $result_vtc = mysql_query("select distinct(user), sum(vtcvalue) from stats_shares where vtcpaid = 0 group by user order by user;");
  $result_mon = mysql_query("select distinct(auxuser), sum(monvalue) from stats_shares where monpaid = 0 group by auxuser order by user;");
  $rows_vtc = mysql_num_rows($result_vtc);
  $rows_mon = mysql_num_rows($result_mon);
  if ( $rows_vtc == 0 && $rows_mon == 0 ) {
     $output = "Nothing to display";
  } else {
    $output .= "<h3>All times, good times</h3><p>This is table of not yet paid submitted shares.";
    $thead .= "<table class='table table-bordered table-striped' id='unpaid'>";
    $thead .= "<thead><tr><th>VTC</th><th class='numbers'>VTC value</th><th>MON</th><th class='numbers'>MON value</th></tr></thead>";
    $thead .= "<p>This table is updated every 5 minutes. Last update " . $now;
    while ( True ): {
      $row_vtc = mysql_fetch_array($result_vtc);
      $row_mon = mysql_fetch_array($result_mon);
      if ( $row_vtc === False && $row_mon === False ) break;
      $sum_vtc += $row_vtc[1];
      $sum_mon += $row_mon[1];
      $tbody .= "<tr><td>" . $row_vtc[0] . "</td><td class='numbers'>" . sprintf("%.08f", $row_vtc[1]) . "</td><td>" . $row_mon[0] . "</td><td class='numbers'>" . sprintf("%.08f", $row_mon[1]) . "</td></tr>";
    } endwhile;
    $tfoot .= "<tfoot><tr><td></td><td class='numbers'>" . sprintf("%.08f", $sum_vtc) . "</td><td></td><td class='numbers'>" . sprintf("%.08f", $sum_mon) . "</td></tfoot>";
  };
  $tbody .= '</tbody></table>';
  $output .= $thead . $tfoot . $tbody;

  mysql_close($db);

  $file = dirname(__FILE__) . "/../include/gen-unpaid.html";
  file_put_contents($file, $output);

?>
