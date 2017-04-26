<!DOCTYPE html>

<?php require("authenticate_visitor.php"); ?>

<html>
  <?php
  $title = "Clone";
  include("head.php"); 
  ?>
  
  <body>
  
  <?php include("navbar.php"); ?>
		
    <?php
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
          $repoErr = "* Repository is required";
        } else {
          $repo = cleanInput($_POST["repo"]);
          if (!isValidRepo($repo)) {
              $repoErr = "* Not a valid repository";
          }
        }
        }

        if (isset($_POST['form_repo_pwd'])) {
            $cmd = join(" ", array($cloneExec, $_POST['repo_name'], $cloneDir, $dirName, $_SESSION['user_id'], $_POST['pwd']));
            $exitCode = shell_exec($cmd);
            $exitCode = trim($exitCode);
            #echo "<pre>$exitCode</pre>";
            
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
  
  	<div class="valign-wrapper">

    <!-- Repo input form -->
    <div class="valign container">
      <h3> Create a Review </h3>
      <form id="create_review_form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
          <label><b>Git Repository:</b></label>
		  <input type="text" name="repo" value="<?php echo $repo;?>" autofocus="autofocus">
          <span class="error" style="color: red;"><?php echo $repoErr;?></span>
          <br></br>
          <input type="submit" name="form_repo_name" value="Create" class="waves-effect waves-light btn"/>
      </form>
      
      <div id="loader" class="center-align" style="display: none">
      <div class="preloader-wrapper big active">
        <div class="spinner-layer">
          <div class="circle-clipper left">
            <div class="circle"></div>
          </div><div class="gap-patch">
            <div class="circle"></div>
          </div><div class="circle-clipper right">
            <div class="circle"></div>
          </div>
        </div>
      </div>
      </div>
      
    </div>

    <!-- authn modal -->
    <div id="clone_pwd_modal" class="modal">
		<div class="container">
        <span onclick="document.getElementById('clone_pwd_modal').style.display='none'" class="close" title="Close Modal">&times;</span>
        <!-- Modal Content -->
        <form method="post" class="modal-content animate" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <h5 class="center-align">Password Required</h5>
			
			<label><b>Git Repository</b></label>
            <input readonly value="<?php echo $repo; ?>" name="repo_name">
            <label><b>Password</b></label>
            <input type="password" name="pwd" required>
            <button type="submit" name="form_repo_pwd" class="waves-effect waves-light btn">Submit</button>
        </form>
		</div>
    </div>

    <script>
      // Before the form is submitted, show the loader
      $("#create_review_form").submit(function() {
        $("#loader").show();
        return true;
      })
    </script>
    
    <?php
        if (isset($_POST['form_repo_name'])) {
          if (isValidRepo($repo)) {
            $cmd = join(" ", array($cloneExec, $repo, $cloneDir, $dirName, $_SESSION['user_id']));
            $exitCode = shell_exec($cmd);
			$exitCode = trim($exitCode);
            #echo "<pre>~~~\n$exitCode\n~~~</pre>";
              
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
	
	html {
		width: 100%;
		height: 85%;
	}
	body {
		width: 100%;
		height: 100%;
	}
	div.valign-wrapper {
		width: 100%;
		height: 100%;
	}
	
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

	</div>
  </body>
  
</html>
