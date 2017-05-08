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
  echo "Here are the reviews others have requested you to look at.";
  $tooltip_msg = "Green reviews are ready to be shipped<br/>Yellow reviews are in progress";
  echo "<div id=\"tooltip_container\" class=\"right-align\"><i class=\"material-icons tooltipped\" data-position=\"left\" data-delay=\"50\" data-tooltip=\"$tooltip_msg\">help</i></div>";
  echo "<script>$('.tooltipped').tooltip({delay: 50, html: true});</script>";
  echo "<br> <br>";
  
#  echo "Green reviews are ready to be shipped while yellow reviews are in progress <br>";

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

      //do a query and see if all the users of a certain review have said ship it
      //if so set a bool to 1 and print out the rows with gold
      $rev_id = $row['ID']; //grab the review id we are looking at
      $query = "SELECT APPROVED from user_reviewer_junction where review_id='$rev_id'";
      $array2 = oci_parse($conn, $query);
      oci_execute($array2);
      $all_approved = 1;
      while($row2=oci_fetch_array($array2)){
        //echo $row2['APPROVED'];
        if($row2['APPROVED'] == 0){
            $all_approved = 0; //if we encounter a 0 for approved set all approved to 0
            break;
        }

      }
      if ($all_approved == 1){
        //load in the first fetch then continue on to the others 
        echo '<table class="table table-striped table-bordered table-hover highlight">'; 
        echo "<thead><tr><th>ID</th><th>Summary</th><th>Date</th></tr></thead>";
        echo "<tr bgcolor=#B4EEB4><td>"; 
        echo $row['ID'];
        echo "</td><td>";   
        echo $row['SUMMARY'];
        echo "</td><td>";    
        echo $row['TIMESTAMP'];
        echo "</td><td>";    
        /*echo $row['OWNER'];
        echo "</td></tr>"; */
      } else {
          //load in the first fetch then continue on to the others 
          echo '<table class="table table-striped bordered table-hover highlight">'; 
          echo "<thead><tr><th>ID</th><th>Summary</th><th>Date</th></tr></thead>";
          echo "<tr bgcolor=#FFFFE0><td>"; 
          //echo "<div style=color:#EEE8AA>".$row['ID']."</div>";
          echo $row['ID'];
          echo "</td><td>";   
          echo $row['SUMMARY'];
          echo "</td><td>";    
          echo $row['TIMESTAMP'];
          echo "</td><td>";    
      }

      while($row=oci_fetch_array($array)){

        //do a query and see if all the users of a certain review have said ship it
        //if so set a bool to 1 and print out the rows with gold
        $rev_id = $row['ID']; //grab the review id we are looking at
        $query = "SELECT APPROVED from user_reviewer_junction where review_id='$rev_id'";
        $array2 = oci_parse($conn, $query);
        oci_execute($array2);
        $all_approved = 1;
        while($row2=oci_fetch_array($array2)){
          //echo $row2['APPROVED'];
          if($row2['APPROVED'] == 0){
              $all_approved = 0; //if we encounter a 0 for approved set all approved to 0
              break;
          }

        }
        
        if ($all_approved == 1){
          //load in the first fetch then continue on to the others 
          echo "<tr bgcolor=#B4EEB4><td>"; 
          echo $row['ID'];
          echo "</td><td>";   
          echo $row['SUMMARY'];
          echo "</td><td>";    
          echo $row['TIMESTAMP'];
          echo "</td><td>";    
          /*echo $row['OWNER'];
          echo "</td></tr>"; */
        } else {
            //load in the first fetch then continue on to the others 
            echo "<tr bgcolor=#FFFFE0><td>"; 
            //echo "<div style=color:#EEE8AA>".$row['ID']."</div>";
            echo $row['ID'];
            echo "</td><td>";   
            echo $row['SUMMARY'];
            echo "</td><td>";    
            echo $row['TIMESTAMP'];
            echo "</td><td>";    
        }  
      }
   }

  oci_close($conn);



?>

