<?php

  session_start();
  
  define("MAX_LENGTH", 6); //for salt length
  //generate hash and salt and return
  function generateHashWithSalt($password) {
    $response = array();
    $intermediateSalt = md5(uniqid(rand(), true));
    $salt = substr($intermediateSalt, 0, MAX_LENGTH);
    array_push($response, $salt);
    $hash = hash("sha256", $password . $salt);
    array_push($response, $hash);
    return $response;
  }

  $conn = oci_connect("guest", "guest")
    or die ("<br>Couldn't connect");

  //save nextval and pass on
  $id_query = "select user_seq.nextval from dual";
  $id_stmt = oci_parse($conn, $id_query);
  oci_define_by_name($id_stmt, 'NEXTVAL', $user_id);
  oci_execute($id_stmt);
  oci_fetch($id_stmt);
  $_SESSION['user_id'] = $user_id;
  
  $name = $_POST["username"];
  $pass = $_POST["password"];
  $response_arr = generateHashWithSalt($pass);
  $salt = $response_arr[0];
  $hash = $response_arr[1];
  //echo $salt;
  //echo 'SPAACE';
  //echo $hash; 
  
  $query = "INSERT into USERS(ID, USER_NAME, PASSWORD, SALT) values(:user_id, :name, :hash, :salt)";
  $stmt = oci_parse($conn, $query);
  oci_bind_by_name($stmt,':user_id',$user_id);
  oci_bind_by_name($stmt,':name', $name);
  oci_bind_by_name($stmt,':hash', $hash);
  oci_bind_by_name($stmt,':salt', $salt);
  $r = oci_execute($stmt);
  $err = array();
  //TODO: see if there are better errors messages
  if (!$r) {
    echo "Something went wrong with the query";
    oci_close($conn);
  } else{
    oci_close($conn);
    header('Location: clone.php');//takes you to this page after running the script
  }
?>

  
