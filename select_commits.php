<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8">
    <title> Group 2 Code Review Project</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,50" rel="stylesheet">

	<!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.1/css/materialize.min.css">

    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.1/js/materialize.min.js"></script>

  </head>
  <body>
  
  <div class="container">
  <form name="get_diff" action="./diff.php" method="POST" class="col s12" onsubmit="return validateForm()">
	<div class="row">
	  <div class="input-field col s6">
        <input placeholder="Diff #1" name="diff1" type="text" required />
	  </div>
	  <div class="input-field col s6">
        <input placeholder="Diff #2" name="diff2" type="text" required />
	  </div>
      <input name="diff_submit" type="submit" value="Get Diff!" class="waves-effect waves-light btn" />
	</div>
  </form>
  </div>
  
  <script>
  function validateForm() {
	var d1 = document.forms["get_diff"]["diff1"].value;
	var d2 = document.forms["get_diff"]["diff2"].value;
	var patt = /\s/;
	valid1 = d1.length==40 && !patt.test(d1);
	valid2 = d2.length==40 && !patt.test(d2);
	if (!valid1) {
		alert("Diff #1 is not valid");
		return false;
	} else if (!valid2) {
		alert("Diff #2 is not valid");
		return false;
	} else {
		return true;
	}
  }
  </script>

<?php
	session_start();
	$review_id = $_SESSION['review_id'];
#	unset($_SESSION['review_id']);

	require('login.php');

	// Prepare the statement
	$stid = oci_parse($conn, "SELECT * FROM commits WHERE review_id='".$review_id."'");
	echo "SELECT * FROM commits WHERE review_id='".$review_id."'";
	if (!$stid) {
		$e = oci_error($conn);
		trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
	}

	// Perform the logic of the query
	$r = oci_execute($stid);
	if (!$r) {
		$e = oci_error($stid);
		trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
	}

	// Fetch the results of the query
	print "<table border='1'>\n";
	while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
		print "<tr>\n";
		foreach ($row as $item) {
			print "    <td>" . ($item !== null ? htmlentities($item, ENT_QUOTES) : "&nbsp;") . "</td>\n";
		}
		print "</tr>\n";
	}
	print "</table>\n";

	oci_free_statement($stid);
	oci_close($conn);
	
?>

</body>
</html>