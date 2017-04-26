<?php

  $conn = oci_connect("guest", "guest", "xe")
	or die("<br>Couldn't connect");
  
  $query = "SELECT id, name FROM groups WHERE REGEXP_LIKE(name, :query)";
  $stmt = oci_parse($conn, $query);
  oci_bind_by_name($stmt, ':query', $_GET['query']);
  oci_execute($stmt);
  
  $suggestions = array();
  while (($row = oci_fetch_array($stmt, OCI_ASSOC)) != false) {
    $s = array("value" => $row['NAME'], "data" => $row['ID']);
    array_push($suggestions, $s);
  }
  $result = array('suggestions' => $suggestions);

  echo json_encode($result);
  
  oci_free_statement($stmt);
  oci_close($conn);
  
?>