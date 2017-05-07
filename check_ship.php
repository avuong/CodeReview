<?php

  #check if the ship button should appear or not

  require("authenticate_visitor.php");

  $conn = oci_connect("guest", "guest", "xe")
    or die("<br>Couldn't connect");

  $review_id = $_GET['review_id'];
  //echo $review_id;

  $query = "SELECT APPROVED from USER_REVIEWER_JUNCTION where REVIEW_ID = '$review_id' and USER_ID = '$user_id'";
  $stmt = oci_parse($conn, $query);
  // bind the user variable.
  oci_define_by_name($stmt, "APPROVED", $approval);
  oci_execute($stmt);
  oci_fetch($stmt);

  echo $approval;

?>