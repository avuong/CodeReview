<?php

  function get_refreshed_comments($diff_id, $line_number) {
    // Connect to DB
    $conn = oci_connect("guest", "guest")
    or die ("<br>Couldn't connect");
    
    // Retrieve comments for that line
    $query = "alter session set NLS_DATE_FORMAT = 'mon dd, yyyy HH:miam'";
    $stmt = oci_parse($conn, $query);
    oci_execute($stmt);
    $query = "SELECT  c.id, c.author author_id, u.user_name author, c.message, c.timestamp
              FROM comments c, users u 
              WHERE c.diff_id = :diff_id AND c.line_number = :line_number AND c.author = u.id
              ORDER BY c.timestamp ASC";
    $array = oci_parse($conn, $query);
    oci_bind_by_name($array, ':diff_id', $diff_id);
    oci_bind_by_name($array, ':line_number', $line_number);
    oci_execute($array);
   
    oci_close($conn);
    
    // contains `function get_new_comment($comment_id, $author, $message, $timestamp, $div_color)`
    require("get_new_comment.php");
    
    // Generate html from retrieved comments
    $output = "var comments = $('<div class=\"code-line-comment-container\"></div>');";
    $div_color = 0;
    while($row=oci_fetch_array($array)){
      $output .= get_new_comment($row['ID'], $row['AUTHOR_ID'], $row['AUTHOR'], $row['MESSAGE'], $row['TIMESTAMP'], $div_color);
      $output .= "comments.append(new_comment);";
      $div_color = 1 - $div_color;
    }
    return "<script> function get_refreshed_comments() { $output return comments; } </script>";
  }
?>