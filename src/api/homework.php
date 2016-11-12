<?php


$server = $_POST["server"];
$port = 11900;
$group = $_POST["group"];
$user = $_POST["user"];
$password = $_POST["password"];

$date = array(2016, 11, 14);

$socket = stream_socket_client("tcp://" . gethostbyname($server) . ":" . $port, $errno, $errorMessage);

if ($socket === false) {
  throw new UnexpectedValueException("Failed to connect: $errorMessage");
}

function request($socket, $request) {
  $id = mt_rand(1, 10000);
  $request["commID"] = $id;
  fwrite($socket, json_encode($request) . "\r\n");

  do {
    $resp = json_decode(fgets($socket));
  } while ($resp->commID != $id || $resp->status == 102);

  return $resp;
}

// handle greeting
fgets($socket);

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
  die("Server didn't return 200 OK for getHW");
}

echo json_encode($getHwResp->payload);