<?php
	session_start();
	$review_id = $_SESSION['review_id'];
	
	$diff1 = $_POST['diff1'];
	$diff2 = $_POST['diff2'];
	
	$cmd = "cd /tmp/git_clone/$review_id && git diff $diff1 $diff2";	
	$output = shell_exec($cmd);
	$output = htmlspecialchars($output);
	
    echo "<pre>$output</pre>";
?>