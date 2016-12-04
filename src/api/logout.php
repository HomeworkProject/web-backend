<?php

setcookie("session", "", time() - 3600, "/homework/", null, false, true); // TODO: See common.php

echo json_encode(array("status" => "logged_out"));