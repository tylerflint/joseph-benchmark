<?php
$redis->connect('127.0.0.1', 6379);
$redis->set('key','value');
echo $redis->get('key');
?>
