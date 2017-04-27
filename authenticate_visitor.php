<?php
  session_start();
  $session_key = session_id();

  /*
   * Search the database for a valid session key for the current user.
   * Redirect the user to login if they don't have a valid session.
   */
  $conn = oci_connect("guest", "guest", "xe")
	or die("<br>Couldn't connect");
  
  // Retrieve any valid sessions
  $query = "SELECT id, user_id FROM sessions WHERE session_key = :session_key  AND session_address = :session_address AND session_useragent = :session_useragent AND session_expires > sysdate";
  $stmt = oci_parse($conn, $query);
  oci_define_by_name($stmt, "ID", $session_id);
  oci_define_by_name($stmt, "USER_ID", $user_id);
  oci_bind_by_name($stmt, ':session_key', $session_key);
  oci_bind_by_name($stmt, ':session_address', $_SERVER['REMOTE_ADDR']);
  oci_bind_by_name($stmt,':session_useragent', $_SERVER['HTTP_USER_AGENT']);
  oci_execute($stmt);
  oci_fetch($stmt);
  
  // if the user does not have a valid session...
  if(empty($session_id)) {
    // if they are not already trying to login, then redirect them to login
    if ($_SERVER['REQUEST_URI'] !== "/index.php") {
      header('Location: index.php');
      exit;
    }
    
  // else the user has a valid session
  } else {
    // update the expiration time of their valid session
    $query = "UPDATE sessions SET session_expires = (sysdate + 1/24) WHERE id = :session_id";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':session_id', $session_id);
    oci_execute($stmt);
    oci_close($conn);

    // if the user was already logged in and tried to access the login page,
    // then redirect them to the homepage instead
    if ($_SERVER['REQUEST_URI'] === "/index.php" || $_SERVER['REQUEST_URI'] === "/" ) {
      $_SESSION['referrer'] = "/homepage.php";
      header("Location: /homepage.php");
      exit;
    }
    
    // If the requested page requires referral from a specific previous page,
    // then enforce that here
    if (isset($referrer)) {
      // if no referral in session -> deny
      if (!isset($_SESSION['referrer'])) {
        echo "referral error";
        exit;
      }
      // grab session referrer and unset session var
      $session_referrer = $_SESSION['referrer'];
      unset($_SESSION['referrer']);
      // if the session referrer is not the required referrer -> deny
      if ($session_referrer != $referrer) {
        echo "referral error";
        exit;
      }
    }
    // set session refferal for current page
    $_SESSION['referrer'] = $_SERVER['REQUEST_URI'];
  }
  
?>