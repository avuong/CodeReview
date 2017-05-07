<?php
  // Authenticate
  require("authenticate_visitor.php");
  
  // Collect vars for...
  // - diff
  $diff1 = $_POST['diff1'];
  $diff2 = $_POST['diff2'];
  // - review
  $review_id = $_SESSION['review_id'];
  $owner_id = intval($user_id);
  // unset session vars
  unset($_SESSION['review_id']);
  
  // open connection
  $conn = oci_connect("guest", "guest")
  or die ("<br>Couldn't connect");

  // get the number of diffs for this review so that we can create a unique diff filename
  $query = "SELECT count(*) diff_num FROM diffs WHERE review_id = :review_id";
  $stmt = oci_parse($conn, $query);
  oci_define_by_name($stmt, "DIFF_NUM", $diff_num);
  oci_bind_by_name($stmt, ':review_id', $review_id);
  oci_execute($stmt);
  oci_fetch($stmt);

  // write the diff to file
  $repo = "/tmp/git_clone/$review_id";
  $diff_file = "/tmp/git_diff/$review_id$diff_num";
  $cmd = "cd $repo && git diff $diff1 $diff2 > $diff_file";	
	$output = shell_exec($cmd);
  
  // Insert diff record
  $query = "INSERT into DIFFS(filename, review_id, upload_time) values(:filename, :review_id, sysdate)";
	$stmt = oci_parse($conn, $query);
	oci_bind_by_name($stmt,':filename', $diff_file);
	oci_bind_by_name($stmt,':review_id', $review_id);
	oci_execute($stmt);  
  
  // Close connection
  oci_close($conn);
  
  // Redirect to created review
  $_SESSION['review_updated'] = true;
  header("Location: review.php?id=".$review_id);
  exit();
?>