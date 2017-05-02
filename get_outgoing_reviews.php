<?php

  require("authenticate_visitor.php");

  $conn = oci_connect("guest", "guest", "xe")
	or die("<br>Couldn't connect");

  //get user name to only fetch reviews you own
  $fxn = "begin :r := shallowbugspack.get_user_name(:id); end;";
  $stmt = oci_parse($conn, $fxn);
  oci_bind_by_name($stmt, ':id', $user_id);
  oci_bind_by_name($stmt, ':r', $user_name);
  oci_execute($stmt);

  echo "Hello ".$user_name."! ";
  echo "Here are the reviews you have submitted.";

  $query = "SELECT a.ID, a.SUMMARY, a.TIMESTAMP, b.USER_NAME as owner from 
            reviews a, users b
            where a.OWNER=$user_id
            and a.OWNER=b.id
            order by TIMESTAMP DESC";
  $array = oci_parse($conn, $query);
  oci_execute($array);
  
   //check for any results 
  if (! $row=oci_fetch_array($array)){
      echo "<br> <br> You currently don't have any outgoing reviews.";
  } else{
      //load in the first fetch then continue on to the others 
      echo '<table class="table table-striped table-bordered table-hover highlight">'; 
      echo "<thead><tr><th>ID</th><th>Summary</th><th>Date</th><th>Submitter</th></tr></thead>";
      echo "<tr><td>"; 
      echo $row['ID'];
      echo "</td><td>";   
      echo $row['SUMMARY'];
      echo "</td><td>";    
      echo $row['TIMESTAMP'];
      echo "</td><td>";    
      echo $row['OWNER'];
      echo "</td></tr>"; 

      while($row=oci_fetch_array($array)){
        //echo $row[0]." ".$row[1];
        echo "<tr><td>"; 
        echo $row['ID'];
        echo "</td><td>";   
        echo $row['SUMMARY'];
        echo "</td><td>";    
        //echo $row['DESCRIPTION'];
        //echo "</td><td>";    
        echo $row['TIMESTAMP'];
        echo "</td><td>";    
        echo $row['OWNER'];
        echo "</td></tr>"; 
      }
   }

  oci_close($conn);



?>

