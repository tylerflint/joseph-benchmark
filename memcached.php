<?php
if (isset($_SERVER['CACHE1_HOST'])) {
    $memcached = new Memcached();
    $memcached->addServer($_SERVER['CACHE1_HOST'], $_SERVER['CACHE1_PORT'] );
    $memcached->set("test_key", "test_value");
    $value = $memcached->get("test_key");
    if ($value == "test_value") {
        echo "Test Succeeeded";
    }
    else {
header("Status: 404 Not Found");
    }
}
else {
header("Status: 500 Internal Server Error");
}
?>
