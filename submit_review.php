<?php
  // Authenticate
  #$referrer = "/create_review.php";
  require("authenticate_visitor.php");
  
  // Collect vars for...
  // - diff
  $diff1 = $_SESSION['diff1'];
  $diff2 = $_SESSION['diff2'];
  // - review
  $review_id = $_SESSION['review_id'];
  $owner_id = intval($user_id);
  $summary = $_POST['summary'];
  $description = $_POST['description'];
  $reviewer_users = json_decode($_POST['reviewer_users']);
  $reviewer_groups = json_decode($_POST['reviewer_groups']);
  // unset session vars
  unset($_SESSION['diff1']);
  unset($_SESSION['diff2']);
  unset($_SESSION['review_id']);
  
  echo "Review ID: ".$review_id;
  echo "<br/>";
  echo "Diff 1: ".$diff1;
  echo "<br/>";
  echo "Diff 2: ".$diff2;
  echo "<br/>";
  echo "Summary: ".$summary;
  echo "<br/>";
  echo "Owner: ".$owner_id;
  echo "<br/>";
  echo "Description: ".$description;
  echo "<br/>";
  echo "Reviewer Users: ";
  print_r(array_values($reviewer_users));
  echo "<br/>";
  echo "Reviewer Groups: ";
  print_r(array_values($reviewer_groups));
  echo "<br/>";
  
  // write the diff to file
  $repo = "/tmp/git_clone/$review_id";
  $diff_file = "/tmp/git_diff/$review_id";
  $cmd = "cd $repo && git diff $diff1 $diff2 > $diff_file";	
	$output = shell_exec($cmd);
  
  /*
   * Insert review data into database
   */
  $conn = oci_connect("guest", "guest")
  or die ("<br>Couldn't connect");
  
  // Insert the new Review record
  $query = "INSERT into REVIEWS(id, summary, description, timestamp, owner) values(:id, :summary, :description, sysdate, :owner)";
	$stmt = oci_parse($conn, $query);
	oci_bind_by_name($stmt,':id', $review_id);
	oci_bind_by_name($stmt,':summary', $summary);
	oci_bind_by_name($stmt,':description', $description);
	oci_bind_by_name($stmt,':owner', $owner_id);
	oci_execute($stmt);
  
  // Insert diff record
  $query = "INSERT into DIFFS(filename, review_id, upload_time) values(:filename, :review_id, sysdate)";
	$stmt = oci_parse($conn, $query);
	oci_bind_by_name($stmt,':filename', $diff_file);
	oci_bind_by_name($stmt,':review_id', $review_id);
	oci_execute($stmt);  
  
  // Insert all reviewers
  //  - users
  if (!empty($reviewer_users)) {
    $query = "INSERT ALL";
    foreach ($reviewer_users as $k => $v) {
      $query .= " INTO user_reviewer_junction (review_id, user_id, approved) VALUES ('".$review_id."', ".$v.", 0)";
    }
    $query .= " SELECT * FROM dual";
    $stmt = oci_parse($conn, $query);
    oci_execute($stmt);
  }
  //  - groups
  if (!empty($reviewer_groups)) {
    foreach ($reviewer_groups as $group_id) {
      // get all users in the group
      $query = "SELECT user_id FROM users_groups_junction WHERE group_id = :group_id";
      $array = oci_parse($conn, $query);
      oci_bind_by_name($array, ':group_id', $group_id);
      oci_execute($array);
      
      // insert those users into the reviewers table
//      $query = "INSERT ALL /*+ ignore_row_on_dupkey_index(USER_REVIEWER_JUNCTION, USER_REVIEWER_PK) */";
//      while($row=oci_fetch_array($array)){
//        if ($owner_id != $row['USER_ID']) {
//          $query .= " INTO user_reviewer_junction (review_id, user_id, approved) VALUES ('".$review_id."', ".$row['USER_ID'].", 0)";
//        }
//      }
//      $query .= " SELECT * FROM dual";
//      $stmt = oci_parse($conn, $query);
//      oci_execute($stmt);
      
      // insert those users into the reviewers table
      #$query = "INSERT ALL /*+ ignore_row_on_dupkey_index(USER_REVIEWER_JUNCTION, USER_REVIEWER_PK) */";
      while($row=oci_fetch_array($array)){
        if ($owner_id != $row['USER_ID']) {
          $query = "INSERT /*+ ignore_row_on_dupkey_index(USER_REVIEWER_JUNCTION, USER_REVIEWER_PK) */ INTO user_reviewer_junction (review_id, user_id, approved) VALUES ('".$review_id."', ".$row['USER_ID'].", 0)";
          $stmt = oci_parse($conn, $query);
          oci_execute($stmt);
        }
      }

    }
  }
  
  // Close connection
  oci_close($conn);
  
  // Redirect to created review
  $_SESSION['review_created'] = true;
  header("Location: review.php?id=".$review_id);
  exit();
?>