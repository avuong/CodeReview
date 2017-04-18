<?php
	session_start();
	$review_id = $_SESSION['review_id'];
	
	$diff1 = $_POST['diff1'];
	$diff2 = $_POST['diff2'];
	
	$cmd = "cd /tmp/git_clone/$review_id && git diff $diff1 $diff2";	
	$output = shell_exec($cmd);
	$output = htmlspecialchars($output);

    $diff_lines = explode("\n", $output);

    #echo out each line 
    #green line for +'s and red line for -'s none for no changes
    for($i = 0; $i <= count($diff_lines); $i++){
      if( !empty($diff_lines[$i])){  
        if ($diff_lines[$i][0] === '+'){
          echo "<div style = 'color:green'>$diff_lines[$i]</div>";
        }elseif ($diff_lines[$i][0] === '-'){
          echo "<div style = 'color:red'>$diff_lines[$i]</div>";
        }else{
          echo "$diff_lines[$i]";
        }
      }
    }

    #echo "<div style = 'color:red'><pre>$output</pre></div>";
?>
