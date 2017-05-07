<?php

#get groups to display on view groups
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
    echo "Here are the groups you are currently in. If you would like to create a group
        click <a href=./groups.php> here. </a>";

  $query = "SELECT g.NAME, g.DETAIL
            FROM GROUPS g, users_groups_junction u
            WHERE u.group_id=g.id
            AND u.USER_ID=$user_id
            ORDER BY Lower(NAME)";
  $array = oci_parse($conn, $query);
  oci_execute($array);

  //check for any results 
  if (! $row=oci_fetch_array($array)){
      echo "<br> <br> You currently are not in any groups.";
  } else{
      echo '<br/><br/>';
      echo '<table class="table table-striped table-bordered table-hover highlight">'; 
      echo "<thead><tr><th>Group Name</th><th>Description</th></tr></thead>";
      echo "<tr id='modal_click' data-target='modal1'><td id='group_name'>"; 
      echo $row['NAME'];
      echo "</td><td>";   
      echo $row['DETAIL'];
      echo "</td></tr>";
      while($row=oci_fetch_array($array)){
        echo "<tr id='modal_click' data-target='modal1'><td id='group_name'>"; 
        echo $row['NAME'];
        echo "</td><td>";   
        echo $row['DETAIL'];
        echo "</td></tr>"; 
      }   
  }

  oci_close($conn);
?>
