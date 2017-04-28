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
    echo "<a href='/review.php?id=".$row['ID']."'>".$row['ID']."</a>";
    echo "</td><td>";   
    echo $row['SUMMARY'];
    echo "</td><td>";    
    echo $row['DESCRIPTION'];
    echo "</td><td>";    
    echo $row['TIMESTAMP'];
    echo "</td><td>";    
    echo $row['OWNER'];
    echo "</td></tr>"; 
  }

  oci_close($conn);



?>

