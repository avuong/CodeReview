<!DOCTYPE html>

<?php 
  require("authenticate_visitor.php");
?>

<?php
  // Ensure that a review id was specified
  if (!isset($_GET['id'])) {
    echo "no review specified";
    exit;
  }
  $review_id = $_GET['id'];
  
  // Retrieve review data
  $conn = oci_connect("guest", "guest", "xe")
	or die("<br>Couldn't connect");
  $query = "SELECT r.summary, r.description, r.timestamp, u.user_name, r.owner
            FROM reviews r, users u 
            WHERE r.id = :review_id AND r.owner = u.id";
  $stmt = oci_parse($conn, $query);
  oci_define_by_name($stmt, "SUMMARY", $summary);
  oci_define_by_name($stmt, "DESCRIPTION", $description);
  oci_define_by_name($stmt, "TIMESTAMP", $timestamp);
  oci_define_by_name($stmt, "USER_NAME", $owner_name);
  oci_define_by_name($stmt, "OWNER", $owner_id);
  oci_bind_by_name($stmt, ':review_id', $review_id);
  oci_execute($stmt);
  oci_fetch($stmt);
  oci_close($conn);
?>

<html>
  <?php
    $title = "Review";
    $include = "
      <link rel='stylesheet' href='styles/review.css'>
      <link href=\"https://fonts.googleapis.com/icon?family=Material+Icons\" rel=\"stylesheet\">
    ";
    include("head.php"); 
	?>
  
  <body>
    <?php include("navbar.php"); ?>
    
    <div class="container">
      <div id="review_title_div" class="row">
        <h3 style="display: inline-block;">Review </h3>
        <h4 style="display: inline-block; font-style: italic">(<?php echo $review_id; ?>)</h4>
      </div>
      <div class="row">
        <div class="col s12">
          <ul class="tabs">
            <li class="tab col s6"><a class="active" href="#details_div">Details</a></li>
            <li class="tab col s6"><a href="#diff_div">Diff</a></li>
          </ul>
        </div>
        
        <div id="details_div" class="col s12">
          <div class="row">
            <h5>Summary</h5>
            <div class="col s6 section">
              <?php echo $summary;?>
            </div>
          </div>
          <div class="row">
            <div class="col s6">
              <h5>Owner</h5>
              <div class="col s12 section">
                <?php echo $owner_name;?>
              </div>
            </div>
            <div class="col s6">
              <h5>Last modified</h5>
              <div class="col s12 section">
                <?php echo $timestamp;?>
              </div>
            </div>
          </div>
          <div class="row">
            <h5>Description</h5>
            <div class="col s12 section">
              <?php echo $description;?>
            </div>
          </div>
        </div>
        
        <div id="diff_div" class="col s12"></div>
        
      </div>
    </div>
  
  <script>
  $(function() {
    function get_diff(){
      var request = $.ajax({
        url: "./get_diff_for_review.php",
        type: "GET",
        data: {
          review_id: "<?php echo $review_id;?>"
        }
      });
      
      request.success(function(data) {
        $("#diff_div").html(data);
      });
      
      request.fail(function(jqXHR, textStatus) {
        alert( "Request failed: " + textStatus );
      });
      
      return false;    
    }
    get_diff();
  });
  </script>
  
  </body>
</html>