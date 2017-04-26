<?php

  session_start();
  $session_key = session_id();

  $conn = oci_connect("guest", "guest", "xe")
	or die("<br>Couldn't connect");

  $query = "SELECT * from reviews";
  $array = oci_parse($conn, $query);
  oci_execute($array);
  
  echo '<table class="table table-striped table-bordered table-hover">'; 
  echo "<tr><th>ID</th><th>Summary</th><th>Description</th><th>Timestamp</th><th>Owner</th></tr>";
  while($row=oci_fetch_array($array)){
    //echo $row[0]." ".$row[1];
    echo "<tr><td>"; 
    echo $row['ID'];
    echo "</td><td>";   
    echo $row['Summary'];
    echo "</td><td>";    
    echo $row['Description'];
    echo "</td><td>";    
    echo $row['Timestamp'];
    echo "</td><td>";    
    echo $row['Owner'];
    echo "</td></tr>"; 
  }

  oci_close($conn);

         
//TEST 
//INSERT INTO Reviews (ID, SUMMARY, DESCRIPTION, TIMESTAMP, OWNER)
//VALUES ('1', 'Code Review', 'Integrating git hooks', TO_DATE('2003/07/09', 'yyyy/mm/dd'), 'avuong');

?>

