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

// Converts a date in the format the server uses to a timestamp
function array_date_to_timestamp($arr)
{
  return mktime($arr[3], $arr[4], 0, $arr[1], $arr[2], $arr[0]);
}

function login($socket)
{
  if (!isset($_COOKIE["session"])) {
    if (isset($_POST["group"]) && isset($_POST["user"]) && isset($_POST["password"])) {
      $res = loginCredentials($socket, $_POST["group"], $_POST["user"], $_POST["password"]);
      if ($res["status"] == "logged_in") {
        setcookie("session", $res["session"]->token,
          array_date_to_timestamp($res["session"]->expires),
          "/homework/", null, false, true); // TODO: IMPORTANT: Change secure to true
      }
      return $res;
    } else {
      return false;
    }
  } else {
    $token = $_COOKIE["session"];
    $res = loginToken($socket, array("token" => $token));
    if ($res["status"] == "token_expired") {
      setcookie("session", "", time() - 3600, "/homework/", null, false, true); // TODO: see above
      unset($_COOKIE["session"]);

      return login($socket);
    } else {
      return $res;
    }
  }
}

/*
 * Attempts to login using the token in the supplied session object.
 */
function loginToken($socket, $session)
{
  $loginReq = array("command" => "login", "session" => $session);
  $loginResp = request($socket, $loginReq);

  $result = array();

  switch ($loginResp->status) {
    case 200:
      $result["status"] = "logged_in";
      $result["session"] = $loginResp->session;
      return $result;
    case 4011:
      $result["status"] = "token_expired";
      $result["session"] = null;
      return $result;
    default:
      $result["status"] = "unknown_error";
      $result["session"] = null;
      return $result;
  }
}

/*
 * Attempts to login using the supplied credentials.
 */
function loginCredentials($socket, $group, $user, $password)
{
  $loginReq = array("command" => "login", "parameters" => array($group, $user, $password));
  $loginResp = request($socket, $loginReq);

  $result = array();

  switch ($loginResp->status) {
    case 200:
      $result["status"] = "logged_in";
      $result["session"] = $loginResp->session;
      return $result;
    case 401:
      $result["status"] = "invalid_credentials";
      $result["session"] = null;
      return $result;
    default:
      $result["status"] = "unknown_error";
      $result["session"] = null;
      return $result;
  }
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

function sessionExpired($session)
{
  $expires = array_date_to_timestamp($session->expires);
  return (time() - $expires) > 0;
}