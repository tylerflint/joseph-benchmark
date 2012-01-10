<?php
$con = mysqli_connect($_SERVER['DB1_HOST'],$_SERVER['DB1_USER'],$_SERVER['DB1_PASS']);
if (!$con)
  {
  die('Could not connect: ' . mysqli_error());
  }
  
mysqli_select_db($con, $_SERVER['DB1_NAME']);

$result1 = mysqli_query($con, "SELECT value FROM core_config_data WHERE path = 'web/cookie/cookie_lifetime' AND scope = 'default' AND scope_id = 0");
$result2 = mysqli_query($con, "SELECT value FROM core_config_data WHERE path='atb/stores/store_ip'");

echo "Test Succeeded";
mysqli_close($con);
?>
