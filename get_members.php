<?php

  #get all the users in a particular group
  require("authenticate_visitor.php");

  $conn = oci_connect("guest", "guest", "xe")
    or die("<br>Couldn't connect");

  $group_name = $_GET['name'];

  $query = "SELECT ID from groups where NAME='$group_name'";
  $stmt = oci_parse($conn, $query);
  // bind the user variable.
  oci_define_by_name($stmt, "ID", $group_id);
  oci_execute($stmt);
  oci_fetch($stmt);
  

  //get all the members in a group
  $query = "SELECT a.USER_NAME
            FROM users a, users_groups_junction b
            WHERE b.group_id=$group_id
            AND b.user_id=a.id
            ORDER BY USER_NAME"; //alphabetize

  $array = oci_parse($conn, $query);
  oci_execute($array);
  echo "Here are the members in this group: <br> <br>";
  $counter=1;
  while($row=oci_fetch_array($array)){
    echo $counter.".) ".$row['USER_NAME'].'<br>';
    $counter++;
  }