<?php

function connect($server, $port)
{
  $socket = stream_socket_client("tcp://" . gethostbyname($server) . ":" . $port, $errno, $errorMessage);

  if ($socket === false) {
    throw new UnexpectedValueException("Failed to connect: $errorMessage");
  }

  // handle greeting
  fgets($socket);

  return $socket;
}

function request($socket, $request)
{
  $id = mt_rand(1, 10000);
  $request["commID"] = $id;
  fwrite($socket, json_encode($request) . "\r\n");

  do {
    $resp = json_decode(fgets($socket));
  } while ($resp->commID != $id || $resp->status == 102);

  return $resp;
}
