<?php
	session_start();
	$review_id = $_SESSION['review_id'];
	
	$diff1 = $_POST['diff1'];
	$diff2 = $_POST['diff2'];
	
	$cmd = "cd /tmp/git_clone/$review_id && git diff $diff1 $diff2";	
	$output = shell_exec($cmd);
	$output = htmlspecialchars($output);

    $diff_lines = explode("\n", $output);

    #9-11px is about 1 char
    #Convert array to an array of string lengths
    #Grab max string length and multiply that for the div width
    #fixes the modal div width problem
    $lengths = array_map('strlen', $diff_lines);
    $max_length = max($lengths)*9;
    
    #echo out each line 
    #green line for +'s and red line for -'s none for no changes
    for($i = 0; $i <= count($diff_lines); $i++){
      if( !empty($diff_lines[$i])){  
        if ($diff_lines[$i][0] === '+'){
          echo "<div style = 'background-color:#dbffdb;display:block;width:$max_length"."px;'>$diff_lines[$i]</div>";
        }elseif ($diff_lines[$i][0] === '-'){
          echo "<div style = 'background-color:#f1c0c0;display:block;width:$max_length"."px;'>$diff_lines[$i]</div>";
        }else{
          echo "<div>$diff_lines[$i]</div>";
        }
      }
    }
    #echo "<div style = 'color:red'><pre>$output</pre></div>";
?>
