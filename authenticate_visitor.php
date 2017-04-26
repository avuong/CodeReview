<?php

  session_start();
  $session_key = session_id();

  $conn = oci_connect("guest", "guest", "xe")
	or die("<br>Couldn't connect");

  $query = "SELECT id, user_id FROM sessions WHERE session_key = :session_key  AND session_address = :session_address AND session_useragent = :session_useragent AND session_expires > sysdate";
  $stmt = oci_parse($conn, $query);
  oci_define_by_name($stmt, "ID", $session_id);
  oci_define_by_name($stmt, "USER_ID", $user_id);
  oci_bind_by_name($stmt, ':session_key', $session_key);
  oci_bind_by_name($stmt, ':session_address', $_SERVER['REMOTE_ADDR']);
  oci_bind_by_name($stmt,':session_useragent', $_SERVER['HTTP_USER_AGENT']);
  oci_execute($stmt);
  oci_fetch($stmt);
  
  // if the user does not have a valid session...
  if(empty($session_id)) {
    // if the user is not logged in and they are not already trying to login, then redirect them to login
    if ($_SERVER['REQUEST_URI'] !== "/index.php") {
      header('Location: index.php');
      exit;
    }
  // else the user has a valid session; update their session expiration time
  } else {
    $query = "UPDATE sessions SET session_expires = (sysdate + 1/24) WHERE id = :session_id";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':session_id', $session_id);
    oci_execute($stmt);
  
    oci_close($conn);
  
    if ($_SERVER['REQUEST_URI'] === "/index.php" || $_SERVER['REQUEST_URI'] === "/" ) {
      header("Location: homepage.php");
      exit;
    }
  }
  
?>