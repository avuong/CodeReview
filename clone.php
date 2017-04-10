<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8">
    <title> Group 2 Code Review Project</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,50" rel="stylesheet">
    <script src="https://www.gstatic.com/firebasejs/live/3.1/firebase.js"></script>

  </head>
  <body>
    <?php
      $repoErr = "";
      $repo = "";

      if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (empty($_POST["repo"])) {
          $repoErr = "Repository is required";
        } else {
          $repo = cleanInput($_POST["repo"]);
          if (isValidRepo($repo)) {
              echo $repo;
              $cloneExec = "/home/ec2-user/pdrumm/test/nocheckout.sh";
              $cloneDir = "/tmp/git_clone";
              $dirName = uniqid(null, true);
              $cmd = join(" ", array($cloneExec, $repo, $cloneDir, $dirName));
              $output = shell_exec($cmd);
              echo "<pre>$output</pre>";
          } else {
              $repoErr = "Not a valid repository";
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
  
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        Name: <input type="text" name="repo" value="<?php echo $repo;?>">
        <span class="error">* <?php echo $repoErr;?></span>
        <br></br>
        <input type="submit" name="submit" value="git clone"/>
    </form>

  </body>
  
</html>
