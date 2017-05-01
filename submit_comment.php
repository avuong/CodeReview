<?php
  // Authenticate
  require("authenticate_visitor.php");

  // Collect POSTed vars
  $author = $_POST['author'];
  $diff_id = $_POST['diff_id'];
  $message = $_POST['message'];
  $line_number = $_POST['line_number'];
  
  // Connect to DB and insert comment
  $conn = oci_connect("guest", "guest")
  or die ("<br>Couldn't connect");
  
  // Insert the new Review record
  $query = "INSERT into COMMENTS(author, message, timestamp, diff_id, line_number) values(:author, :message, sysdate, :diff_id, :line_number)";
	$stmt = oci_parse($conn, $query);
	oci_bind_by_name($stmt,':author', $author);
	oci_bind_by_name($stmt,':message', $message);
	oci_bind_by_name($stmt,':diff_id', $diff_id);
	oci_bind_by_name($stmt,':line_number', $line_number);
	oci_execute($stmt);
  oci_close($conn);
  
  // contains `function get_refreshed_comments($diff_id, $line_number)`
  require("get_refreshed_comments.php");
  echo get_refreshed_comments($diff_id, $line_number);
  
?>