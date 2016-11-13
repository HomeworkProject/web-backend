<?php

include "common.php";

$server = $_POST["server"];
$port = $_POST["port"];
$group = $_POST["group"];
$user = $_POST["user"];
$password = $_POST["password"];

$dateY = $_POST["dateY"];
$dateM = $_POST["dateM"];
$dateD = $_POST["dateD"];

$date = array($dateY, $dateM, $dateD);

$socket = connect($server, $port);

// check protocol version --

// login
$loginReq = array("command" => "login", "parameters" => array($group, $user, $password));
$loginResp = request($socket, $loginReq);

if ($loginResp->status != 200) {
  die("Server didn't return 200 OK for login.");
}

// get homework
$getHwReq = array("command" => "gethw", "date" => $date);
$getHwResp = request($socket, $getHwReq);

if ($getHwResp->status != 200) {
  die("Server didn't return 200 OK for getHW. Status: " . $getHwResp->payload);
}

echo json_encode($getHwResp->payload);