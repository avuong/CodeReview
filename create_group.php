<?php 

  require("authenticate_visitor.php");

  $conn = oci_connect("guest", "guest", "xe")
    or die("<br>Couldn't connect");

  //save nextval and pass on
  $id_query = "select group_seq.nextval from dual";
  $id_stmt = oci_parse($conn, $id_query);
  oci_define_by_name($id_stmt, 'NEXTVAL', $group_id);
  oci_execute($id_stmt);
  oci_fetch($id_stmt);

  $_SESSION['user_id'] = $user_id;
  //get user name to add into group 
  //you should be in a group you made
  $fxn = "begin :r := shallowbugspack.get_user_name(:id); end;";
  $stmt = oci_parse($conn, $fxn);
  oci_bind_by_name($stmt, ':id', $user_id);
  oci_bind_by_name($stmt, ':r', $user_name);
  oci_execute($stmt);

  $group_name = $_POST['group-name'];
  $description = $_POST['description'];
  $users_string = $_POST['members'];
  $user_array = explode(',', $users_string);
  $user_array = array_map('trim', $user_array);
  //default permissions for now
  $permissions = 0;

  echo "Group Name: ".$group_name;
  echo "<br/>";
  echo "Desc: ".$description;
  echo "<br/>";
  echo "Reviewer Users: ".$users_string;
  echo "<br/>";

  $user_ids = array();
  //get a list of user_id's from user names given
  foreach($user_array as $user){

    $query = "SELECT ID from USERS where USER_NAME='$user'";
    $array = oci_parse($conn, $query);
    oci_execute($array);
    $row=oci_fetch_array($array);
    //Throw an error if user doesnt exist
    if ($row['ID'] === NULL){
      echo "$user doesnt exist in the database";
    } else{
      array_push($user_ids, $row['ID']);
    }
  }



  //create group in group table first
  $query = "INSERT into GROUPS(ID, NAME, DETAIL) values (:group_id, :name, :description)";
  $stmt = oci_parse($conn, $query);
  oci_bind_by_name($stmt, ':group_id', $group_id);
  oci_bind_by_name($stmt, ':name', $group_name);
  oci_bind_by_name($stmt, ':description', $description);
  $r = oci_execute($stmt);
  $err = array();
  if (!$r) {
    echo "<div style = 'color:red'>*Group name already exists</div>";
    oci_close($conn);
  } else {
    //after creating the group add users to the users_groups junction table
    //first add creator to group
      $query = "INSERT into USERS_GROUPS_JUNCTION(USER_ID, GROUP_ID, PERMISSIONS) 
                values (:user_id, :group_id, :permissions)";
      $stmt = oci_parse($conn, $query);
      oci_bind_by_name($stmt,':user_id', $user_id);
      oci_bind_by_name($stmt,':group_id', $group_id);
      oci_bind_by_name($stmt,':permissions', $permissions);
      oci_execute($stmt);
      oci_close($conn);
    //now add the rest of the members
    foreach($user_ids as $id){
      $query = "INSERT into USERS_GROUPS_JUNCTION(USER_ID, GROUP_ID, PERMISSIONS) 
                values (:user_id, :group_id, :permissions)";
      $stmt = oci_parse($conn, $query);
      oci_bind_by_name($stmt,':user_id', $id);
      oci_bind_by_name($stmt,':group_id', $group_id);
      oci_bind_by_name($stmt,':permissions', $permissions);
      oci_execute($stmt);
      oci_close($conn);
    }
  }


?>