<?php
  require("authenticate_visitor.php");
	
  $conn = oci_connect("guest", "guest", "xe")
    or die("<br>Couldn't connect");
	  
  $query = "DELETE FROM sessions WHERE session_key = :session_key  AND session_address = :session_address AND session_useragent = :session_useragent";
  $stmt = oci_parse($conn, $query);
  oci_bind_by_name($stmt, ':session_key', $session_key);
  oci_bind_by_name($stmt, ':session_address', $_SERVER['REMOTE_ADDR']);
  oci_bind_by_name($stmt,':session_useragent', $_SERVER['HTTP_USER_AGENT']);
  oci_execute($stmt);
  #echo $stmt;
  #exit;
 
  oci_close($conn);
  
  header("Location: index.php");
  exit;
?>