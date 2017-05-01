<?php
  /* Helper Functions */
  
  // create useful data structure out of the sql output for list of comments
  function add_to_parent($new_comment, $new_comment_parent_id, &$comment_map) {
    // loop through all comments
    foreach ($comment_map as &$comment) {
      // if this comment is the new comment's parent then add it to the parent's reply list
      if($comment['comment_id']==$new_comment_parent_id) {
        if (isset($comment['replies'])) {
          array_push($comment['replies'], $new_comment);
        } else {
          $comment['replies'] = array($new_comment);
        }
        return true;
      }
      // if this comment has replies, then recurse
      if (isset($comment['replies'])) {
        $r = add_to_parent($new_comment, $new_comment_parent_id, $comment['replies']);
        if ($r) {
          return true;
        }
      }
    }
    return false;
  }
  function get_comments_as_map($array) {
    $comment_map = array();
    while($row=oci_fetch_array($array)){
      $line_data = array(
        "comment_id" => $row['ID'],
        "author_id" => $row['AUTHOR_ID'],
        "author" => $row['AUTHOR'],
        "message" => $row['MESSAGE'],
        "timestamp" => $row['TIMESTAMP']
      );
      // if there is no parent comment then add it to the base level comments
      if (!isset($row['PARENT_COMMENT'])) {
        array_push($comment_map, $line_data);
      // otherwise lets find its parent
      } else {
        add_to_parent($line_data, $row['PARENT_COMMENT'], $comment_map);
      }
    }
    return $comment_map;
  }
  
  function get_child_comments($child_comment_map, $div_color, $level=0) {
    $output = "var child_comments$level = $('<div class=\"code-line-reply-container test$level\"></div>');";
    foreach ($child_comment_map as $comment) {
      if (isset($comment['replies'])) {
        $output .= get_child_comments($comment['replies'], 1-$div_color, $level+1 );
      }
      $output .= get_new_comment($comment['comment_id'], $comment['author_id'], $comment['author'], $comment['message'], $comment['timestamp'], $div_color);
      if (isset($comment['replies'])) {
        $child_idx = $level+1;
        $output .= "new_comment.append(child_comments$child_idx);";
      }
      $output .= "child_comments$level.append(new_comment);";
      $div_color = 1 - $div_color;
    }
    return $output;
  }  
   
  
  /* get_refreshed_comments */

  function get_refreshed_comments($diff_id, $line_number) {
    // Connect to DB
    $conn = oci_connect("guest", "guest")
    or die ("<br>Couldn't connect");
    
    // Retrieve comments for that line
    $query = "alter session set NLS_DATE_FORMAT = 'mon dd, yyyy HH:miam'";
    $stmt = oci_parse($conn, $query);
    oci_execute($stmt);
    $query = "SELECT  c.id, c.author author_id, u.user_name author, c.message, c.timestamp, c.parent_comment
              FROM comments c, users u 
              WHERE c.diff_id = :diff_id AND c.line_number = :line_number AND c.author = u.id
              ORDER BY c.timestamp ASC";
    $array = oci_parse($conn, $query);
    oci_bind_by_name($array, ':diff_id', $diff_id);
    oci_bind_by_name($array, ':line_number', $line_number);
    oci_execute($array);
    oci_close($conn);
    
    // Parse sql output and create a data structure containing comments
    $comment_map = get_comments_as_map($array);
    
    // contains `function get_new_comment($comment_id, $author, $message, $timestamp, $div_color)`
    require("get_new_comment.php");
    
    // Generate html from retrieved comments
    
    // create the comments
    $output = "var comments = $('<div class=\"code-line-comment-container\"></div>');";
    $div_color = 0;
    foreach ($comment_map as $comment) {
      if (isset($comment['replies'])) {
        $output .= get_child_comments($comment['replies'], 1-$div_color);  // creates `child_comments`
      }
      $output .= get_new_comment($comment['comment_id'], $comment['author_id'], $comment['author'], $comment['message'], $comment['timestamp'], $div_color);  // creates `new_comment`
      if (isset($comment['replies'])) {
        $output .= "new_comment.append(child_comments0);";
      }
      $output .= "comments.append(new_comment);";
      $div_color = 1 - $div_color;
    }
    
    return "<script> function get_refreshed_comments() { $output return comments; } </script>";
  }
  
  
?>