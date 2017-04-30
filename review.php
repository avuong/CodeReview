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
  
  // Retrieve diff id
  $query = "WITH ordered_diffs AS (
              SELECT id 
              FROM diffs 
              WHERE review_id = :review_id
              ORDER BY upload_time DESC)
            SELECT * 
            FROM ordered_diffs 
            WHERE rownum = 1";
  $stmt = oci_parse($conn, $query);
  oci_define_by_name($stmt, "ID", $diff_id);
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
  /*
   * Helper functions for comments
   */
   // use ajax to submit the comment
   function submit_comment(text, line, comment_div) {
     
     var request = $.ajax({
        url: "submit_comment.php",
        type: "POST",
        data: {
          author: "<?php echo $user_id;?>",
          diff_id: <?php echo $diff_id;?>,
          message: text,
          line_number: line
        }
      });
      
      request.success(function(data) {
        comment_div.hide(500, function() {
          $(this).remove();
        });
      });
      
      request.fail(function(jqXHR, textStatus) {
        alert( "Request failed: " + textStatus );
      });

      return false;   
   }
   
   // create & return a "write comment" box
   function get_create_comment_div(line_number) {
     var $container = $('<div class="create_comment_container"></div>');
     var $form = $('<form class="create_comment_form"></form>');
     var $inputbox = $('<div class="input-field">\
            <textarea id="new_comment" name="comment_text" class="materialize-textarea"></textarea>\
            <label for="new_comment">Your comment</label>\
          </div>');
     var $btn_div = $('<div class="right-align"></div>');
     var $submit_btn = $('<input name="submit_comment" type="button" value="post" class="waves-effect waves-light btn" />');
     $submit_btn.data('line_number', line_number);
     $submit_btn.on('click', function() {
       var comment_line = $(this).data('line_number');
       var comment_text = $(this).closest('.create_comment_form').find('textarea').val();
       var comment_div = $(this).closest('.create_comment_container');
       submit_comment(comment_text, comment_line, comment_div);
     });
     var $cancel_btn = $('<input name="cancel_comment" type="button" value="cancel" class="cancel-btn waves-effect waves-light btn" />');
     $cancel_btn.on('click', function() {
       var hide_div = $(this).closest('.create_comment_container').hide(500, function() {
         $(this).remove();
       });
     });
     $btn_div.append($cancel_btn).append($submit_btn);
     $form.append($inputbox).append($btn_div);
     $container.append($form);
     return $container;
   }
   
  /*
   * Define callbacks for after ajax returns the diff text
   */
  function add_load_more_listener() {
    $(".load_diff").on("click", function() {        
      var self = this;
      var request = $.ajax({
        url: "./get_diff_for_review.php",
        type: "GET",
        data: {
          review_id: "<?php echo $review_id;?>",
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
  }
  
  function init_materialize_objs() {
    // initialize dropdowns
    $(function() {
      $('.dropdown-button').dropdown({
        inDuration: 300,
        outDuration: 225,
        constrainWidth: true, // change width of dropdown to that of the activator
        hover: true, // Activate on hover
        gutter: 0, // Spacing from edge
        belowOrigin: false, // Displays dropdown below the button
        alignment: 'left', // Displays dropdown with edge aligned to the left of button
        stopPropagation: false // Stops event propagation
      });
      // initialize tooltips
      $('.tooltipped').tooltip({delay: 50});
      // initialize modals
      $('.modal').modal();
    })
  }
  
  function add_toggle_listener() {
    $('.toggle-btn').on('click', function() {
      var code_div = $(this).closest('.file_div').children('.file_code_div');
      if (code_div.is(":visible")) {
        code_div.hide(500);
        $(this).text('keyboard_arrow_down');
      } else {
        code_div.show(500);
        $(this).text('keyboard_arrow_up');
      }
    });
  }
  
  function add_code_line_listener() {
    $(".file_code_div > p").on("click", function() {
      var self = $(this);
      var $comment = get_create_comment_div(self.data('line_number'));
      self.after($comment);
      $comment.show(500);
    });
  }
   
   /*
    * Make an async request for the diff data in html format
    */
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
        add_load_more_listener();
        init_materialize_objs();
        add_toggle_listener();
        add_code_line_listener();
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