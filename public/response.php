<?php

use LightPAY\Framework\Request;

require dirname(__DIR__) . "/vendor/autoload.php";

$request = new Request(
    "__API_KEY__",
    "__CONSUMER_KEY__",
    "__SECRET_KEY__"
);

error_log(json_encode($_POST));

// test if you've received the data
$file = 'log.txt';
$current = file_get_contents($file);
$current .= "[" . date("Y-m-d H:i:s") . "]:\n";
foreach ($_POST as $k => $v) {
    $current .= "$k => $v\n";
}
$current .= "=================================================================================\n";
file_put_contents($file, $current);

// Proceed to save data into your DB
