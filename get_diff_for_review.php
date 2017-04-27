<?php
  $review_id = $_GET['review_id'];
  
  /*
   * Retrieve path to diff file
   */
  $conn = oci_connect("guest", "guest", "xe")
	or die("<br>Couldn't connect");
  
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
   * Retrieve diff file contents
   */
  $cmd = "cat $diff_file_path";	
  $shell_output = shell_exec($cmd);
  $regex = '~(?=((?<=\n)diff\s\-\-)git\sa/.*\sb/.*\n)~';
  $diffs_by_file = preg_split($regex, $shell_output);
  
  $php_output = "";
  foreach ($diffs_by_file as $file_diff) {
    $diff_lines = explode("\n", $file_diff);
    echo "<h4>$diff_lines[0]</h4>";
    foreach ($diff_lines as $line) {
      echo "<p style='margin: 0;'>$line</p>";
    }
  }  
?>