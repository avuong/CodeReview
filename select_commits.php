<?php
	session_start();
	$review_id = $_SESSION['review_id'];
	unset($_SESSION['review_id']);

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