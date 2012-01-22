<?php
  $myFile = "/var/www/benchmark/testWrite.php";
  $fh = fopen($myFile, 'w') or die("can't open file");
  $stringData = "My stuff that is written\n";
  fwrite($fh, $stringData);
  fclose($fh);
?>
