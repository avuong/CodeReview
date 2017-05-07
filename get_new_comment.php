<?php
  function get_new_comment($comment_id, $author_id, $author, $message, $timestamp, $div_color) {
    $message = addslashes($message);
    $message = str_replace("\n","\\n",$message);
    $div_color = $div_color==1 ? "comment-light" : "comment-dark";
    $output = "var new_comment = $('<div class=\"code-line-comment $div_color\"></div>');
                var header = $('<div class=\"comment-header\"></div>');
                var author = $('<h6 class=\"comment-author\"><a href=\"#\">$author</a> says:</h6>');
                var timestamp = $('<h6 class=\"comment-timestamp\">$timestamp</h6>');
                var message = $('<h6 class=\"comment-message\">$message</h6>');
                var action_container = $('<div class=\"comment-action-container\">');
                var a_reply = $('<h6 class=\"comment-action reply-comment\">Reply</h6>');
                header.append(author).append(timestamp);
                action_container.append(a_reply);";
    global $user_id;
    if ($author_id == $user_id) {
      $output .= "var a_edit = $('<h6 class=\"comment-action edit-comment\">Edit</h6>');
                  var a_delete = $('<h6 class=\"comment-action delete-comment\">Delete</h6>');
                  action_container.append(a_edit).append(a_delete);";
    }
    $output .= "new_comment.append(header).append(message).append(action_container);
                new_comment.data(\"comment_id\", $comment_id);";
    return $output;
  }
?>