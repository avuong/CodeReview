<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8">
    <title> Group 2 Code Review Project</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,50" rel="stylesheet">

  </head>
  <body>
    <?php
      session_start();

	  $hostname = "52.34.131.50";
	  $port = "8172";
	  $onSuccessPhp = "select_commits.php";
	  
      $cloneExec = "/home/ec2-user/apache/htdocs/shell_scripts/clone.sh";
      $cloneDir = "/tmp/git_clone";
      $dirName = uniqid(null, true);

      $SUCCESS     = "0";
      $ERROR       = "1";
      $AUTHN       = "2";
	  $PERM_DENIED = "3";

      $repoErr = "";
      $repo = "";

      if ($_SERVER["REQUEST_METHOD"] == "POST") {

        if (isset($_POST['form_repo_name'])) {
        if (empty($_POST["repo"])) {
          $repoErr = "Repository is required";
        } else {
          $repo = cleanInput($_POST["repo"]);
          if (!isValidRepo($repo)) {
              $repoErr = "Not a valid repository";
          }
        }
        }

        if (isset($_POST['form_repo_pwd'])) {
            $cmd = join(" ", array($cloneExec, $_POST['repo_name'], $cloneDir, $dirName, $_POST['pwd']));
            $exitCode = shell_exec($cmd);
			$exitCode = trim($exitCode);
            echo "<pre>$exitCode</pre>";
            
            if ($exitCode == $PERM_DENIED) {
                echo "<script type='text/javascript'>",
                    "alert('Authentication failed.')",
                    "</script>";
            } else if ($exitCode == $SUCCESS) {
				if (substr($cloneDir, -1) != "/")
					$cloneDir .= "/";
				$_SESSION['review_id'] = $dirName;
                header("Location: http://".$hostname.":".$port."/".$onSuccessPhp);
				exit();
            } else {
				echo "<script type='text/javascript'>",
                    "alert('Error')",
                    "</script>";
			}
        }

      }

      function cleanInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        return $data;
      }

      function isValidRepo($repoStr) {
        $string = $repoStr;
        $urlRegex = "(http:\/\/|https:\/\/)?(www\.)?(.*@)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?";
        $ipaddrRegex = "(.*@)?(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]):(.*\/)?([^.]+?)(\.[^.]*)?";
        $pattern = "/^(".$urlRegex."|".$ipaddrRegex.")$/";
        $isValidRepo = (bool) preg_match($pattern, $string);
        $hasWhiteSpace = (bool) preg_match("/\s/", $string);
        return ($isValidRepo and !$hasWhiteSpace);
      }
 
    ?>
    <h1> Clone your repository </h1>
  
    <!-- Repo input form -->
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        Name: <input type="text" name="repo" value="<?php echo $repo;?>">
        <span class="error">* <?php echo $repoErr;?></span>
        <br></br>
        <input type="submit" name="form_repo_name" value="git clone"/>
    </form>

    <!-- authn modal -->
    <div id="clone_pwd_modal" class="modal">
        <span onclick="document.getElementById('clone_pwd_modal').style.display='none'" class="close" title="Close Modal">&times;</span>
        <!-- Modal Content -->
        <form method="post" class="modal-content animate" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <label><b>Password</b></label>
            <br></br>
            <input readonly value="<?php echo $repo; ?>" name="repo_name">
            <br></br>
            <input type="password" placeholder="Enter Password" name="pwd" required>
            <br></br>
            <button type="submit" name="form_repo_pwd">git clone</button>
        </form>
    </div>

    <?php
        if (isset($_POST['form_repo_name'])) {
          if (isValidRepo($repo)) {
            echo $repo;
            $cmd = join(" ", array($cloneExec, $repo, $cloneDir, $dirName));
            $exitCode = shell_exec($cmd);
			$exitCode = trim($exitCode);
            echo "<pre>~~~\n$exitCode\n~~~</pre>";
              
            if ($exitCode == $AUTHN) {
                echo "<script type='text/javascript'>",
                    "document.getElementById('clone_pwd_modal').style.display='block'",
                    "</script>";
            } else if ($exitCode == $SUCCESS) {
				if (substr($cloneDir, -1) != "/")
					$cloneDir .= "/";
				$_SESSION['review_id'] = $dirName;
                header("Location: http://".$hostname.":".$port."/".$onSuccessPhp);
				exit();
			} else {
				echo "<script type='text/javascript'>",
                    "alert('Error')",
                    "</script>";
			}
          }
        }
    ?>


    <style>
/* The Modal (background) */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgb(0,0,0); /* Fallback color */
    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
    padding-top: 60px;
}

/* Modal Content/Box */
.modal-content {
    background-color: #fefefe;
    margin: 5px auto; /* 15% from the top and centered */
    border: 1px solid #888;
    width: 80%; /* Could be more or less, depending on screen size */
}

/* The Close Button */
.close {
    /* Position it in the top right corner outside of the modal */
    position: absolute;
    right: 25px;
    top: 0; 
    color: #000;
    font-size: 35px;
    font-weight: bold;
}

/* Close button on hover */
.close:hover,
.close:focus {
    color: red;
    cursor: pointer;
}

/* Add Zoom Animation */
.animate {
    -webkit-animation: animatezoom 0.6s;
    animation: animatezoom 0.6s
}

@-webkit-keyframes animatezoom {
    from {-webkit-transform: scale(0)} 
    to {-webkit-transform: scale(1)}
}

@keyframes animatezoom {
    from {transform: scale(0)} 
    to {transform: scale(1)}
}
    </style>


  </body>
  
</html>