<?php

  $conn = oci_connect("guest", "guest")
  or die ("<br>Couldn't connect");

  $input_name = 'test_user_82'; //$_POST["username"];
  $input_pass = 'password'; //$_POST["password"];
  
  $query = "select salt, password from users where user_name='$input_name'";
  $stmt = oci_parse($conn, $query);
  //store table results into variable
  oci_define_by_name($stmt, 'PASSWORD', $pass);
  oci_define_by_name($stmt, 'SALT', $salt);
  oci_execute($stmt);
  oci_fetch($stmt);

  //hash provided password and compare with DB
  //
  $hash = hash("sha256", $input_pass . $salt);
  
  //compare input pass and hashed pass
  if ($pass === $hash){
      print "LOGIN SUCCESSFUL";
  } else{
      print "LOGIN FAILED. Please check username/password combination";
  }

  //possible set session variables and pass onto next page?
?>
