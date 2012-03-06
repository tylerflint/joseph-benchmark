<?php
$con = mysql_connect($_SERVER['DB2_HOST'],$_SERVER['DB2_USER'],$_SERVER['DB2_PASS']);
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }
  
mysql_select_db($_SERVER['DB2_NAME'], $con);

$result1 = mysql_query("SELECT value FROM core_config_data WHERE path = 'web/cookie/cookie_lifetime' AND scope = 'default' AND scope_id = 0");

$result2 = mysql_query("SELECT value FROM core_config_data WHERE path='atb/stores/store_ip'");

echo 'Test Succeeded';

mysql_close($con);
?>
