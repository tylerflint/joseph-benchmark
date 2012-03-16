<?php
$redis->connect('tunnel.pagodabx.com', 6379);
$redis->set('key','value');
echo $redis->get('key');
?>
