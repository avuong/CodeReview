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
              SELECT filename 
              FROM diffs 
              WHERE review_id = :review_id
              ORDER BY upload_time DESC)
            SELECT * 
            FROM ordered_diffs 
            WHERE rownum = 1";
  $stmt = oci_parse($conn, $query);
  oci_define_by_name($stmt, "FILENAME", $diff_file_path);
  oci_bind_by_name($stmt, ':review_id', $review_id);
  oci_execute($stmt);
  oci_fetch($stmt);
  oci_close($conn);
  
  
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
   
  // loop through array of lines in a diff and build up a string of formatted <p>s
  function diff_lines_to_paragraphs($diff_lines) {
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
   }
   return $diffs_as_p;
  }
 
  // Given a diff file, format it nicely using html, then return the string
  function diff_to_html_string($file_diff, $max_diff_size, &$start_line_idx, &$end_line_idx) {
    $diff_str = "var file_div = $(\"<div class='file_div'></div>\");
                    var code_div = $(\"<div class='code_div'></div>\");";
      
    // divide file diff into array of lines
    $diff_lines = explode("\n", $file_diff);
    array_pop($diff_lines); // last entry is empty from explode()ing on a trailing \n
    // keep track of global line numbers in the diff file
    $start_line_idx = $end_line_idx;
    $end_line_idx += count($diff_lines);
    // retrieve and print filename
    $file_name = get_file_name($diff_lines[0]);
    $diff_str .= "var header = $('<h4>$file_name</h4>');
                    file_div.append(header);";
    
    // If the diff is too big, just print a button instead of the diff
    if (strlen($file_diff) > $max_diff_size) {
      $diff_str .= "var load_diff_btn = $(\"<a class='waves-effect waves-light btn load_diff'>Load diff</a>\");";
      $js_obj = "{'start_line': $start_line_idx, 'end_line': $end_line_idx-1}";
      $diff_str .= "load_diff_btn.data($js_obj);";
      $diff_str .= "code_div.append(load_diff_btn);";
    
    } else {    
      // loop through lines in the current diff
      $diffs_as_p = diff_lines_to_paragraphs($diff_lines);
      $diff_str .= "code_div.append(\"$diffs_as_p\");";
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
    
    foreach ($diffs_by_file as $file_diff) {
      $php_output .= diff_to_html_string($file_diff, $max_diff_size, $start_line_idx, $end_line_idx);
      
    }
    
    // Add a listener to the "load diff" buttons that calls this script to retrieve single diff
    $php_output .= '
                          $(".load_diff").on("click", function() {
                          
                            var self = this;
                            var request = $.ajax({
                              url: "./get_diff_for_review.php",
                              type: "GET",
                              data: {
                                review_id: "'.$_GET['review_id'].'",
                                start_line: $(this).data("start_line"),
                                end_line: $(this).data("end_line")
                              }
                            });
                            
                            request.success(function(data) {
                              $(self).parent().html(data);
                            });
                            
                            request.fail(function(jqXHR, textStatus) {
                              alert( "Request failed: " + textStatus );
                            });
                            
                            return false;
                          });
                          ';
                          
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
    
    echo diff_lines_to_paragraphs($diff_lines);
    
  } else {
    $diffs_by_file = get_diffs_arr_by_file($diff_file_path);
    echo diffs_arr_to_html_string($diffs_by_file);
  }
?>