<?php
  /*
   * HANDLE GET REQUEST VARS
   */
  $review_id = $_GET['review_id'];
  if (!isset($_GET['start_line']) || !isset($_GET['end_line'])) {
    $get_single_file = false;
  } else {
    $get_single_file = true;
    $diff_start_line = $_GET['start_line'];
    $diff_end_line = $_GET['end_line'];
  }
  
  /*
   * CONNECT TO DATABASE
   */
  $conn = oci_connect("guest", "guest", "xe")
	or die("<br>Couldn't connect");
  // Retrieve the filepath to the diff file
  $query = "WITH ordered_diffs AS (
              SELECT id, filename 
              FROM diffs 
              WHERE review_id = :review_id
              ORDER BY upload_time DESC)
            SELECT * 
            FROM ordered_diffs 
            WHERE rownum = 1";
  $stmt = oci_parse($conn, $query);
  oci_define_by_name($stmt, "ID", $diff_id);
  oci_define_by_name($stmt, "FILENAME", $diff_file_path);
  oci_bind_by_name($stmt, ':review_id', $review_id);
  oci_execute($stmt);
  oci_fetch($stmt);
  // Retreive comments for the current diff
  $query = "SELECT  u.user_name author, c.message, c.timestamp, c.line_number
            FROM comments c, users u 
            WHERE c.diff_id = :diff_id AND c.author = u.id
            ORDER BY c.timestamp ASC";
  $array = oci_parse($conn, $query);
  oci_bind_by_name($array, ':diff_id', $diff_id);
  oci_execute($array);
  oci_close($conn);
  
  // create useful data structure out of the sql output for list of comments
  function get_comments_as_map($array) {
    $comment_map = array();
    while($row=oci_fetch_array($array)){
      $line_data = array(
        "author" => $row['AUTHOR'],
        "message" => $row['MESSAGE'],
        "timestamp" => $row['TIMESTAMP']
      );
      if (!isset($comment_map[$row['LINE_NUMBER']])) {
        $comment_map[$row['LINE_NUMBER']] = array($line_data);
      } else {
        array_push($comment_map[$row['LINE_NUMBER']], $line_data);
      }
    }
    return $comment_map;
  }
  $comment_map = get_comments_as_map($array);
  
  /* 
   * HELPER FUNCTIONS
   */
   
  // Retrieve diff file contents
  function get_diffs_arr_by_file($diff_file_path) {
    $cmd = "cat $diff_file_path";	
    $shell_output = shell_exec($cmd);
    $shell_output = htmlspecialchars($shell_output);
    $regex = '~(?=((?<=\n)diff\s\-\-)git\sa/.*\sb/.*\n)~';
    return preg_split($regex, $shell_output);
  }
  
  // Retrieve diff contents for a single file within the diff
  function get_diff_contents($diff_file_path, $start, $end) {
    $cmd = "sed -n '$start , $end p' $diff_file_path";
    $shell_output = shell_exec($cmd);
    $shell_output = htmlspecialchars($shell_output);
    return $shell_output;
  }
   
  // get the filename from the inputted line from a diff
  // Args:
  //   line: assumed to be the first line of a diff
  //     eg. diff --git a/get_diff_for_review.php b/get_diff_for_review.php
  function get_file_name($line) {
    $regex = '~(?<=a/|b/)[^\s]*(?=\sb|$)~';
    preg_match_all($regex, $line, $matches);
    $from_file = $matches[0][0];
    $to_file = $matches[0][1];
    if ($to_file && $to_file != "/dev/null") {
      return $to_file;
    } else {
      return $from_file;
    }
  }
  
  function get_tooltip_message($diff_lines, $change) {
    $a_regex = '~^\+[^\+]~';
    $add_count = count(preg_grep($a_regex, $diff_lines));
    if ($add_count == 0) {
      $a_tooltip = "Binary file added";
    } else if ($add_count == 1) {
      $a_tooltip = "1 addition";
    } else {
      $a_tooltip = "$add_count additions";
    }
    if ($change == "a")
      return $a_tooltip;
    
    $d_regex = '~^-[^\-]~';
    $del_count = count(preg_grep($d_regex, $diff_lines));
    if ($del_count == 0) {
      $d_tooltip = "Binary file deleted";
    } else if ($del_count == 1) {
      $d_tooltip = "1 deletion";
    } else {
      $d_tooltip = "$del_count deletions";
    }
    if ($change == "d")
      return $d_tooltip;
    
    // else its "m"
    return $a_tooltip." & ".$d_tooltip;
  }
  
  // determine where the diff is for an addition, modification, or deletion
  function get_file_status($diff_lines, &$pre_file, &$post_file) {
    $regex = '~^index\s[a-f0-9]{7}\.\.[a-f0-9]{7}~';
    foreach ($diff_lines as $line) {
      if (preg_match($regex, $line)) {
        $regex = '~[a-f0-9]{7}~';
        preg_match_all($regex, $line, $matches);
        $pre_file = $matches[0][0];
        $post_file = $matches[0][1];
        $nil = "0000000";
        if ($pre_file == $nil || $post_file == $nil) {
          if ($pre_file == $nil) {
            // added file;
            $msg = get_tooltip_message($diff_lines, "a");
            return "<a class=\"tooltipped\" data-position=\"left\" data-delay=\"50\" data-tooltip=\"$msg\">\
            <i class=\"material-icons md-24 md-green400 icon-valign\">add_circle</i></a>";
          } else {
            // deleted file
            $msg = get_tooltip_message($diff_lines, "d");
            return "<a class=\"tooltipped\" data-position=\"left\" data-delay=\"50\" data-tooltip=\"$msg\">\
            <i class=\"material-icons md-24 md-red300 icon-valign\">remove_circle</i></a>";
          }
        } else {
          // modified file
          $msg = get_tooltip_message($diff_lines, "m");
          return "<a class=\"tooltipped\" data-position=\"left\" data-delay=\"50\" data-tooltip=\"$msg\">\
          <i class=\"material-icons md-24 md-amber400 icon-valign\">add_circle</i></a>";
        }
      }
    }
    return "";
  }
  
  function get_dropdown($pre_file, $post_file, $diff_counter) {
    $nil = "0000000";
    $li1 = $pre_file == $nil ? "" : '<li><a href="get_file_version.php?review_id='.$_GET['review_id'].'&file_idx='.$pre_file.'">Before</a></li>';
    $li2 = $post_file == $nil ? "" : '<li><a href="get_file_version.php?review_id='.$_GET['review_id'].'&file_idx='.$post_file.'">After</a></li>';
    
    $output = "var dropdown_div = $('<div class=\"valign-wrapper\" style=\"margin: 0 0 0 auto;\"></div>');
              var dropdown_html = '<a class=\"dropdown-button btn\" href=\"#\" data-activates=\"dropdown-$diff_counter\">View</a><ul id=\"dropdown-$diff_counter\" class=\"dropdown-content\">$li1$li2</ul>';
              var dropdown = $(dropdown_html);
              var toggle_btn = '<i class=\"material-icons toggle-btn\">keyboard_arrow_up</i>';
              dropdown_div.append(dropdown).append(toggle_btn);";
     return $output;
  }
  
  // use the first few lines of the diff to create an html header
  function create_header_div($diff_lines, &$idx, $diff_counter) {
    $file_name = get_file_name($diff_lines[0]);
    $status = get_file_status($diff_lines, $pre_file, $post_file);
    $init_dropdown = get_dropdown($pre_file, $post_file, $diff_counter);
    $header_div = "var header_div = $('<div class=\"file_header_div valign-wrapper\"></div>');
                  var file_name = $('$status<h6>$file_name</h6>');
                  $init_dropdown;
                  header_div.append(file_name).append(dropdown_div);";
    return $header_div;
  }
   
  // create comment divs for the line if there are any
  function get_comments_for_line($line_number) {
    global $comment_map;
    $output = "";
    if (!isset($comment_map[$line_number])) {
      return $output;
    }
    foreach ($comment_map[$line_number] as $comment) {
      $html_comment = "<p>".$comment['author'].": ".$comment['message']."  (".$comment['timestamp'].")</p>";
      $output .= $html_comment;
    }
    return $output;
  }
   
  // loop through array of lines in a diff and build up a string of formatted <p>s
  function diff_lines_to_paragraphs($diff_lines, $start_line_number) {
    $curr_line = $start_line_number;
    $diffs_as_p = "";
    foreach ($diff_lines as $line) {
      if (!empty($line)){
        if ($line[0] === '+'){
          $diffs_as_p .= "<p style='margin: 0;background-color:#dbffdb;'>$line</p>";
        } elseif ($line[0] === '-'){
          $diffs_as_p .= "<p style='margin: 0;background-color:#f1c0c0;'>$line</p>";
        } else {
          $diffs_as_p .= "<p style='margin: 0;'>$line</p>";
        }
      } else {
        $diffs_as_p .= "<p style='margin: 0;'>$line</p>";
      }
      $diffs_as_p .= get_comments_for_line($curr_line++);
    }
    return $diffs_as_p;
  }
 
  // Given a diff file, format it nicely using html, then return the string
  function diff_to_html_string($file_diff, $max_diff_size, &$start_line_idx, &$end_line_idx, $diff_counter) {
    $diff_str = "var file_div = $(\"<div class='file_div z-depth-1'></div>\");
                    var code_div = $(\"<div class='file_code_div'></div>\");";
      
    // divide file diff into array of lines
    $diff_lines = explode("\n", $file_diff);
    array_pop($diff_lines); // last entry is empty from explode()ing on a trailing \n
    // keep track of global line numbers in the diff file
    $start_line_idx = $end_line_idx;
    $end_line_idx += count($diff_lines);
    
    // handle git diff header
    $header_div = create_header_div($diff_lines, $idx, $diff_counter);
    $diff_str .= $header_div."file_div.append(header_div);";
    
    // If the diff is too big, just print a button instead of the diff
    if (strlen($file_diff) > $max_diff_size) {
      $diff_str .= "var a_container = $('<div class=\"left-align\"></div>');
                    var load_diff_btn = $(\"<a class='waves-effect waves-light btn load_diff'>Load diff</a>\");
                    a_container.append(load_diff_btn);";
      $js_obj = "{'start_line': $start_line_idx, 'end_line': $end_line_idx-1}";
      $diff_str .= "load_diff_btn.data($js_obj);
                    code_div.append(a_container);";
    
    } else {    
      // loop through lines in the current diff
      $diffs_as_p = diff_lines_to_paragraphs($diff_lines, $start_line_idx);
      $diff_str .= "var diffs_as_p = $(\"$diffs_as_p\");
                    for (var i=0; i<diffs_as_p.length; ++i) {
                      $(diffs_as_p[i]).data('line_number', $start_line_idx + i);
                    }
                    code_div.append(diffs_as_p);";
    }
    
    $diff_str .= "file_div.append(code_div);
                    outer_div.append(file_div);";
                    
    return $diff_str;
  }
  
  // given an array of diffs, return html formatting all of them
  function diffs_arr_to_html_string($diffs_by_file) {
    // loop through each diff file and format their lines
    $php_output = "var outer_div = $('#diff_div');";
    $max_diff_size = 1000;  // bytes
    $start_line_idx = 1;
    $end_line_idx = 1;
    $diff_counter = 0;
    
    foreach ($diffs_by_file as $file_diff) {
      $php_output .= diff_to_html_string($file_diff, $max_diff_size, $start_line_idx, $end_line_idx, $diff_counter);
      ++$diff_counter;
    }
           
    // return output
    return "<script>$php_output</script>";
  }
  
  /*
   * FORMAT DIFF AS HTML, THEN ECHO
   */
  if ($get_single_file) {
    $diff_contents = get_diff_contents($diff_file_path, $diff_start_line, $diff_end_line);
    // divide file diff into array of lines
    $diff_lines = explode("\n", $diff_contents);
    array_pop($diff_lines); // last entry is empty from explode()ing on a trailing \n
    
    echo diff_lines_to_paragraphs($diff_lines, $diff_start_line);
    
  } else {
    $diffs_by_file = get_diffs_arr_by_file($diff_file_path);
    echo diffs_arr_to_html_string($diffs_by_file);
  }
?>