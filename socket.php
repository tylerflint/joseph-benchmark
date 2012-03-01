<?php
error_reporting(E_ALL);

echo "<h2>TCP/IP Connection</h2>\n";

$service_port = 11211;

$address = gethostbyname('tunnel.pagodabox.com');

/* Create a TCP/IP socket. */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
} else {
    echo "OK.\n";
}

echo "Attempting to connect to '$address' on port '$service_port'...";
$result = socket_connect($socket, $address, $service_port);
if ($result === false) {
    echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
} else {
    echo "OK.\n";
}

$in = "set greeting 1 0 11\r\n";
$in .= "Hello world\r\n";
$out = '';

echo "Sending request...";
socket_write($socket, $in, strlen($in));
echo "OK.\n";

echo "Reading response:\n\n";
while ($out = socket_read($socket, 10000, PHP_NORMAL_READ)) {
    echo $out;
}

echo "Closing socket...";
socket_close($socket);
echo "OK.\n\n";

?>
