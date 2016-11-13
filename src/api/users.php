<?php

include "common.php";

$server = $_POST["server"];
$port = $_POST["port"];
$group = $_POST["group"];

$socket = connect($server, $port);

// Currently a server bug prevents the group filter from working
//$listReq = array("command" => "list", "group" => $group);
$listReq = array("command" => "list");
$listResp = request($socket, $listReq);

if ($listResp->status != 200) {
  die("Server didn't return 200 OK for list.");
}

$groups = (array) $listResp->payload;

echo(json_encode($groups[$group]));
