<?php
  // Authenticate
  #$referrer = "/review.php";
  require("authenticate_visitor.php");

  if ($_SERVER["REQUEST_METHOD"] != "GET") {
    echo "request method error";
    exit;
  }
  
  if (!isset($_GET['review_id']) || !isset($_GET['file_idx'])) {
    echo "error: get params";
    exit;
  }
  
  $review_id = $_GET['review_id'];
  $file_idx = $_GET['file_idx'];
  $git_repo = "/tmp/git_clone/$review_id";
  
  $cmd = "cd $git_repo && git show $file_idx";	
  $shell_output = shell_exec($cmd);
  
  echo $shell_output; exit;
  
  $output = htmlspecialchars($shell_output);
  
  if ($output == "") {
    $img = base64_encode($shell_output);
    echo "<img src='data:image/png;base64, $img' />";
  } else {
    echo $output;
  }
?>