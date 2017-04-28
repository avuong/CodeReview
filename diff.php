<?php
	session_start();
	$review_id = $_SESSION['review_id'];
	
  // Generate diff
  $diff1 = $_POST['diff1'];
  $diff2 = $_POST['diff2'];
	
  #run script that will correctly order the two diffs for git diff based on
  #ancestor - descendent relationship
  $cmd = "./commit_order.sh $review_id $diff1 $diff2";
  $output = shell_exec($cmd);
  $output = htmlspecialchars($output);
  //$cmd = "cd /tmp/git_clone/$review_id && git diff $diff1 $diff2";	

  $diff_lines = explode("\n", $output);
    
  $output_diff = "";

  #9-11px is about 1 char
  #Convert array to an array of string lengths
  #Grab max string length and multiply that for the div width
  #fixes the modal div width problem
  $lengths = array_map('strlen', $diff_lines);
  $max_length = max($lengths)*9;
  
  # Get the last line currently displayed on the client side
  # and then start retrieving the diff from there
  if (isset($_POST['last_line'])) {
    $var = $_POST['last_line'];
    $start_line = intval($_POST['last_line']) + 1;
  } else {
    $var = 0;
    $start_line = 0;
  }
  
  # Calculate the ending line to return to the client
  $chunk_size = 100;
  $diff_len = count($diff_lines);
  if ( ($start_line + $chunk_size) >= $diff_len ) {
    $end_of_diff = true;
    $end_line = $diff_len;
  } else {
    $end_of_diff = false;
    $end_line = $start_line + $chunk_size;
  }
    
  #echo out each line 
  #green line for +'s and red line for -'s none for no changes
  for($i = $start_line; $i <= $end_line; $i++){
    if( !empty($diff_lines[$i])){  
      if ($diff_lines[$i][0] === '+'){
        $output_diff .= "<div style = 'background-color:#dbffdb;display:block;width:$max_length"."px;'>$diff_lines[$i]</div>";
      }elseif ($diff_lines[$i][0] === '-'){
        $output_diff .= "<div style = 'background-color:#f1c0c0;display:block;width:$max_length"."px;'>$diff_lines[$i]</div>";
      }else{
        $output_diff .= "<div>$diff_lines[$i]</div>";
      }
    }
  }
  echo json_encode(array("diff" => $output_diff, "end_of_diff" => $end_of_diff, "last_line" => $end_line, "start_line" => $start_line, "var" => $var));
  #echo "<div style = 'color:red'><pre>$output</pre></div>";
?>
