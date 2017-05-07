<?php

  require("authenticate_visitor.php");

  $conn = oci_connect("guest", "guest", "xe")
    or die("<br>Couldn't connect");

  $review_id = $_POST['name']; 
  echo $user_id;
  echo $review_id;

  $query = "UPDATE USER_REVIEWER_JUNCTION
            SET APPROVED=1
            WHERE REVIEW_ID=:review_id
            AND USER_ID=:user_id";
  $stmt = oci_parse($conn, $query);
  oci_bind_by_name($stmt, ':review_id', $review_id);
  oci_bind_by_name($stmt, ':user_id', $user_id);
  oci_execute($stmt);
  oci_close($conn);
?>