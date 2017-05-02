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

  //If fields are left blank
  if ($name === "" || $pass === ""){
      echo "<div style = 'color:red'> *All fields required</div>";
      exit -1;
  }
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
  $r = @oci_execute($stmt);
  $err = array();

  //query failed
  if (!$r) {
      $m = oci_error($stmt);

      if($m['code'] == 1) {
        echo "<div style = 'color:red'>*User Name already exists</div>";
      }
      else{ 
          echo "<div style = 'color:red'>*Something went wrong with the query</div>";
      }
      oci_close($conn);

  } else{
	// after adding a new user, grant them a new session_cache_expire
	$session_key = session_id();
	$query = "INSERT into SESSIONS(user_id, session_key, session_address, session_useragent) values(:user_id, :session_key, :session_address, :session_useragent)";
	$stmt = oci_parse($conn, $query);
	oci_bind_by_name($stmt,':user_id', $user_id);
	oci_bind_by_name($stmt,':session_key', $session_key);
	oci_bind_by_name($stmt,':session_address', $_SERVER['REMOTE_ADDR']);
	oci_bind_by_name($stmt,':session_useragent', $_SERVER['HTTP_USER_AGENT']);
	oci_execute($stmt);
    oci_close($conn);
    //header('Location: clone.php');//takes you to this page after running the script
    echo "<script>top.window.location = './homepage.php'</script>";
  }
?>

  
