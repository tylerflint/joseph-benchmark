<?php
if (isset($_SERVER['CACHE2_HOST'])) {
    $memcached = new Memcached();
    $memcached->addServer($_SERVER['CACHE2_HOST'], $_SERVER['CACHE2_PORT'] );
    $memcached->set("test_key", "test_value");
    $value = $memcached->get("test_key");
    if ($value == "test_value") {
        echo "Test Succeeeded";
    }
    else {
        echo "doesn't match";
    }
}
else {
    echo 'no memcache server';
}
?>
