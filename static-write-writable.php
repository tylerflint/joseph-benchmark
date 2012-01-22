<?php
  $myFile = "/var/www/benchmark/testWrite.php";
  $fh = fopen($myFile, 'w') or die("can't open file");
  $stringData = "Test Succeeded";
  fwrite($fh, $stringData);
  fclose($fh);
?>
