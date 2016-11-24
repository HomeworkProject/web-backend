<?php

include "common.php";

$server = $_POST["server"];
$port = $_POST["port"];

$socket = connect($server, $port);

$loginRes = login($socket);

if ($loginRes === false) {
  $loginRes = array("status" => "invalid_credentials");
}

echo json_encode($loginRes);
