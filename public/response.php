<?php

use LightPAY\Framework\Request;

require dirname(__DIR__) . "/vendor/autoload.php";

$request = new Request(
    "abcbcbc333f7b31ca751d88db3e760874d0cf7803cf5cd8b24d6a6d5051b4efab4eecab",
    "a8dc8a5665d3ac143e6fff431a8dc8a5665d3ac143e6fff431a8dc8a5665d3ac143e6fff431",
    "838a74e940e5cac62b90214829e42e9a0e6ea0338d91eb8a9ae29b788d4bb",
    "http://localhost:9001/response.php",
    "http://localhost:9001/response.php"
);
error_log("json_encode()");
error_log(json_encode($_POST));
$amount = 110;
$nb = new NumberFormatter("fr_FR", NumberFormatter::CURRENCY);
$amountView = $nb->format($amount / 100);
$res = $request->post("/v1/web/api/payments/init", [
    'amount' => $amount,
    'ref' => "5lzRlKO2Rp3AJtSsByUe6JyfnYOPJ84TA7LO9mXT0hknhVqQDATuxgY7baWD3SoF4Iw6r4IPEXpliUTZYnXqqKKL3rNh8PWgdKqA6b9YPLbDEsUvN9zqiF6BjjrztnWN7xqpDxk5nsAeMTJbAQYZPlmrZEVsjxnYzMixNzRmWjzkOlYdmch0AJBmleaFQVBL5xKT4iGKO06AI3cfwm6hmfMiWZk8OVUMDoqEByJXaHQXYa1KzZg0sAUTEF8tJ1F",
    "merchant_id" => "a8dc8a5665d3ac143e6fff431a8dc8a5665d3ac143e6fff431a8dc8a5665d3ac143e6fff431",
    "custom_fields" => $_POST['foo'] . "=" . $_POST['barfoo']
]);