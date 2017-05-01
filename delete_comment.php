<?php
  // Authenticate
  require("authenticate_visitor.php");

  // Collect POSTed vars
  $comment_id = $_POST['comment_id'];
  $line_number = $_POST['line_number'];
  $diff_id = $_POST['diff_id'];
  
  // Connect to DB and insert comment
  $conn = oci_connect("guest", "guest")
  or die ("<br>Couldn't connect");  
  // Delete the comment
  $query = "DELETE FROM comments WHERE id = :id and author = :author";
	$stmt = oci_parse($conn, $query);
	oci_bind_by_name($stmt,':id', $comment_id);
  oci_bind_by_name($stmt,':author', $user_id);
	oci_execute($stmt);
  oci_close($conn);
  
  // contains `function get_refreshed_comments($diff_id, $line_number)`
  require("get_refreshed_comments.php");
  echo get_refreshed_comments($diff_id, $line_number);
  
?>