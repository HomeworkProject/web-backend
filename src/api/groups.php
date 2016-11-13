<?php

include "common.php";

$server = $_POST["server"];
$port = $_POST["port"];

$socket = connect($server, $port);

$listReq = array("command" => "list");
$listResp = request($socket, $listReq);

if ($listResp->status != 200) {
  die("Server didn't return 200 OK for list.");
}

$groups = array();

foreach ($listResp->payload as $key => $value) {
  $groups[count($groups)] = $key;
}

echo json_encode($groups);