<?php
  require("authenticate_visitor.php");
  
  $summary = $_POST['summary'];
  $description = $_POST['description'];
  $reviewer_users = json_decode($_POST['reviewer_users']);
  $reviewer_groups = json_decode($_POST['reviewer_groups']);
  
  echo "Summary: ".$summary;
  echo "<br/>";
  echo "Description: ".$description;
  echo "<br/>";
  echo "Reviewer Users: ";
  print_r(array_values($reviewer_users));
  echo "<br/>";
  echo "Reviewer Groups: ";
  print_r(array_values($reviewer_groups));
?>