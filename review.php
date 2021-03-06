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
  $query = "alter session set NLS_DATE_FORMAT = 'mon dd, yyyy HH:miam'";
  $stmt = oci_parse($conn, $query);
  oci_execute($stmt);
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
  
  // Retrieve reviewers for this review
  $query = "SELECT  u.user_name, ur.approved
            FROM user_reviewer_junction ur, users u
            WHERE ur.review_id = :review_id AND ur.user_id = u.id";
  $array = oci_parse($conn, $query);
  oci_bind_by_name($array, ':review_id', $review_id);
  oci_execute($array);
  oci_close($conn);
  
  $reviewers = array();
  while($row=oci_fetch_array($array)){
    //if user approved echo his/her name in green
    if ($row['APPROVED']){
        array_push($reviewers, "<div style='color: green;'>".$row['USER_NAME']."</div>");
    } else {
        array_push($reviewers, $row['USER_NAME']);
    }
  }
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
    <?php
      // if the review was just created, alert the user that it was created
      if (isset($_SESSION['review_created']) && $_SESSION['review_created']==true) {
        echo "<script>Materialize.toast('Review created!', 4000);</script>";
        unset($_SESSION['review_created']);
        
      // if a diff was just added to the review, alert the user of that
      } else if (isset($_SESSION['review_updated']) && $_SESSION['review_updated']==true) {
        echo "<script>Materialize.toast('Review updated!', 4000);</script>";
        unset($_SESSION['review_updated']);
      }
    ?>
    
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
        
        <div id="details_div">
          <div id="details_container" class="z-depth-2 col s12">
            <div class="row">
            
              <div class="col s6">
                <div class="row detail-section">
                  <h5 class="block-header">Summary:</h5>
                  <div class="col s6">
                    <p><?php echo $summary;?></p>
                  </div>
                </div>
                <div class="row detail-section">
                  <h5 class="block-header">Description:</h5>
                  <div class="col s12">
                    <p><?php echo $description;?></p>
                  </div>
                </div>

                <div class="row detail-section">
                    <button id="shipit" style="display: none;" type="button" class="waves-effect waves-light btn">Ship It!</button>
                    <button id="unship" style="display: none;" type="button" class="waves-effect waves-light btn">Unship It!</button>
                </div>

                <?php
                if ($user_id == $owner_id) {
                  $_SESSION['review_id'] = $review_id;
                  echo "<div class='row detail-section'>";
                  echo "<a href='select_commits.php' id='update_diff' type='button' class='waves-effect waves-light btn'>Update Diff</a>";
                  echo "</div>";
                }
                ?>

              </div>
              
              <div class="col s6">
                <div class="row detail-section">
                  <h5 class="inline-header">Last modified:</h5>
                  <p><?php echo $timestamp;?></p>
                </div>
                <div class="row detail-section">
                  <h5 class="inline-header">Owner:</h5>
                  <p><?php echo $owner_name;?></p>
                </div>
                <div id="reviewers-container" class="row detail-section">
                  <h5>Reviewers:</h5>
                  <div id="reviewers_tooltip_container" class="right-align"><i id="reviewers_tooltip" class="material-icons tooltipped" data-position="top" data-delay="50" data-tooltip="Reviewers who have approved are designated in green">info_outline</i></div>
                  <div class="col s12">
                    <ul id='reviewers_list' class="collection"></ul>
                  </div>
                </div>
              </div>
            
            </div>
          </div>
        </div>
        
        <div id="diff_div" class="col s12"></div>
        
        <!--Comment Scroller-->
        <div id="comment_scroller">
          <div id="comment_scroller_shadow_box" class="z-depth-2">
            <div class="scroll-btn-container">
              <i id="prev_comment" class="waves-effect waves-teal material-icons md-30 md-bluegrey700 scroll-btn">arrow_upward</i>
            </div>
            <div class="scroll-btn-container">
              <i id="next_comment" class="waves-effect waves-teal material-icons md-30 md-bluegrey700 scroll-btn">arrow_downward</i>
            </div>
          </div>
        </div>

        
      </div>
    </div>
  
  <script>
  var curr_diff_id = <?php echo $diff_id;?>;
  // Comment Scrolling
  $(function() {
    // show comment scroll arrows when user moves to the Diff page
    $(".tab").on('click', function() {
      var href = $(this).children('a').attr('href');
      if (href=="#diff_div") {
        $("#comment_scroller").show();
      } else {
        $("#comment_scroller").hide();
      }
    });
    // listen to when user clicks comment scroller
    function animate_comment(comment) {
      var animation_time = 1.2;
      var animation_rounds = 2;
      comment.css({
        "transform-origin": "50% 50%",
        "transform": "scale(1)",
        "animation": "pulse "+ (animation_time/2) +"s infinite linear alternate",
        "-webkit-animation": "pulse "+ (animation_time/2) +"s infinite linear alternate"
      });
      setTimeout(function() {
        comment.css({
          "transform-origin": "auto",
          "transform": "auto",
          "animation": "auto",
          "-webkit-animation": "auto"
        });
      }, animation_time * 1000 * animation_rounds);
    }
    function scroll_to(comment) {
      /*
      $('html, body').animate(
        { scrollTop: comment.offset().top - ($(window).height()/3) }, 
        1000, 
        function() { animate_comment(comment); }
      );
      */
      $('html, body').animate({
        scrollTop: comment.offset().top - ($(window).height()/3)
      }, 1000); 
      animate_comment(comment);
    }
    function scroll_to_prev_comment(comments) {
      var scrollTop = $(window).scrollTop();
      var scrollBottom = $(window).scrollTop() + $(window).height();
      for (var i=comments.length-1; i>=0; --i) {
        var offsetTop;
        var isHidden = false;
        if (! $(comments[i]).is(":visible")) {
          offsetTop = $(comments[i]).closest('.file_div').offset().top;
          isHidden = true;
        } else {
          offsetTop = $(comments[i]).offset().top;
        }
        var prevCommentYThreshold = scrollTop;
        if ($(comments[i]).hasClass("has-comments")) {
          prevCommentYThreshold = scrollBottom;
        }
        if (offsetTop < prevCommentYThreshold) {
          if (isHidden) {
            $(comments[i]).closest('.file_code_div').show();
          }
          scroll_to($(comments[i]));
          return;
        }
      }
      Materialize.toast('No prior comments above.', 2000);
    }
    function scroll_to_next_comment(comments) {
      var scrollBottom = $(window).scrollTop() + $(window).height();
      var scrollTop = $(window).scrollTop();
      // loop through all comments
      for (var i=0; i<comments.length; ++i) {
        var offsetTop;
        var isHidden = false;
        // if the comment is hidden, then we consider the top of the comment to be
        // the top of its parent file div
        if (! $(comments[i]).is(":visible")) {
          offsetTop = $(comments[i]).closest('.file_div').offset().top;
          isHidden = true;
        } else {
          offsetTop = $(comments[i]).offset().top;
        }
        // if the comment is in a diff that has not been downloaded, then we include
        // the current screen in the range to search for the next comment
        var nextCommentYThreshold = scrollBottom;
        if ($(comments[i]).hasClass("has-comments")) {
          nextCommentYThreshold = scrollTop;
        }
        if (offsetTop > nextCommentYThreshold) {
          if (isHidden) {
            $(comments[i]).closest('.file_code_div').show();
          }
          scroll_to($(comments[i]));
          return;
        }
      }
      Materialize.toast('No further comments below.', 2000);
    }
    $(".scroll-btn").on("click", function() {
      var comments = $(".code-line-comment-container, .has-comments");
      console.log(comments);
      if (comments.length == 0) {
        Materialize.toast('No comments on this page.', 3000);
      
      } else {
        var id = $(this).attr('id');
        if (id=="prev_comment") {
          scroll_to_prev_comment(comments);
        } else if (id=="next_comment") {
          scroll_to_next_comment(comments);
        }
      }
    });
  });
  
  // Populate the reviewers list
  function populate_reviewers_list() {
    var reviewers = <?php echo json_encode($reviewers); ?>;
    if (reviewers.length > 0) {
      var $ul = $('#reviewers_list');
      for (var i=0; i<reviewers.length; ++i) {
        $ul.append('<li class="collection-item">'+reviewers[i]+'</li>');
      }
      $("#reviewers-container").show();
    }
  }
  populate_reviewers_list();
            
  /*
   * Helper functions for comments
   */
   // use ajax to submit the comment
   function submit_comment(text, line, parent_id, new_comment_div) {
     
     var request = $.ajax({
        url: "submit_comment.php",
        type: "POST",
        data: {
          author: "<?php echo $user_id;?>",
          diff_id: curr_diff_id,
          message: text,
          line_number: line,
          parent_id: parent_id
        }
      });
      
      // after submitting a comment, we will remove the create comment box
      // and then refresh the comments for the line commented on
      request.success(function(data) {
        new_comment_div.hide(500, function() {
          $(this).remove();
        });
        var $code_line_container = new_comment_div.closest('.code-line-container');
        var $code_line_comments = $code_line_container.find('.code-line-comment-container');
        if ($code_line_comments.length == 0) {
          $old_comments = $('<div class=\"code-line-comment-container\"></div>');
          $code_line_container.append($old_comments);
        } else {
          var $old_comments = $code_line_comments;
        }
        $old_comments.html(data);
        $old_comments.replaceWith(get_refreshed_comments());
        add_code_line_listeners();
      });
      
      request.fail(function(jqXHR, textStatus) {
        alert( "Request failed: " + textStatus );
      });

      return false;   
   }
   // use ajax to delete a comment
   function delete_comment(comment_id, line_number, delete_btn) {
     
     var request = $.ajax({
        url: "delete_comment.php",
        type: "POST",
        data: {
          comment_id: comment_id,
          line_number: line_number,
          diff_id: <?php echo $diff_id;?>
        }
      });
      
      // after deleting a comment, we will 
      // refresh the comments for the line commented on
      request.success(function(data) {
        var $code_line_container = delete_btn.closest('.code-line-container');
        var $old_comments = $code_line_container.find('.code-line-comment-container');
        $old_comments.html(data);
        $old_comments.replaceWith(get_refreshed_comments());
        var new_comments = $code_line_container.find('.code-line-comment-container');
        if (new_comments.children().length == 0) {
          new_comments.remove();
        }
        add_code_line_listeners();
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
       submit_comment(comment_text, comment_line, null, comment_div);
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
   
   // create & return a "reply comment" box
   function get_create_reply_div(comment_id, line_number) {
     var $container = $('<div class="create_reply_container"></div>');
     var $form = $('<form class="create_reply_form"></form>');
     var $inputbox = $('<div class="input-field">\
            <textarea id="new_reply" name="reply_text" class="materialize-textarea"></textarea>\
            <label for="new_reply">Your comment</label>\
          </div>');
     var $btn_div = $('<div class="right-align"></div>');
     var $submit_btn = $('<input name="submit_reply" type="button" value="post" class="waves-effect waves-light btn" />');
     $submit_btn.data('comment_id', comment_id);
     $submit_btn.data('line_number', line_number);
     $submit_btn.on('click', function() {
       var parent_comment = $(this).data('comment_id');
       var comment_line = $(this).data('line_number');
       var comment_text = $(this).closest('.create_reply_form').find('textarea').val();
       var comment_div = $(this).closest('.create_reply_container');
       submit_comment(comment_text, comment_line, parent_comment, comment_div);
     });
     var $cancel_btn = $('<input name="cancel_reply" type="button" value="cancel" class="cancel-btn waves-effect waves-light btn" />');
     $cancel_btn.on('click', function() {
       var hide_div = $(this).closest('.create_reply_container').hide(500, function() {
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
      $(this).closest('.file_div').removeClass('has-comments');
      var self = this;
      var request = $.ajax({
        url: "./get_diff_for_review.php",
        type: "GET",
        data: {
          review_id: "<?php echo $review_id;?>",
          start_line: $(this).data("start_line"),
          end_line: $(this).data("end_line"),
          diff_id: $(this).data("diff_id")
        }
      });
      
      // `data` contains jquery that defines a function `get_new_code_div`
      // This can be called to retrieve the div to replace the old code div
      request.success(function(data) {
        var $old_file_div = $(self).closest('.file_code_div');
        $old_file_div.html(data);
        $old_file_div.replaceWith(get_new_code_div());
        add_code_line_listeners();
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
  
  function add_code_line_listeners() {
    $(".file_code_div p.code-line").off("click");
    $(".file_code_div p.code-line").on("click", function() {
      var self = $(this);
      // if there is already a create comment div, then just toggle it
      var create_comment_div = self.closest('.code-line-container').children('.create_comment_container');
      if (create_comment_div.length > 0) {
        create_comment_div.toggle(500);
        create_comment_div.find('textarea#new_comment').focus();
        
      // else create a new create comment div
      } else {
        var $comment = get_create_comment_div(self.closest('.code-line-container').data('line_number'));
        self.parent('pre').after($comment);
        $comment.show(500);
        $comment.find('textarea#new_comment').focus();
      }
    });
    
    $(".reply-comment").off("click");
    $(".reply-comment").on("click", function() {
      console.log($(this).closest('.code-line-comment').data('comment_id'));
      console.log($(this).closest('.code-line-container').data('line_number'));
      var $reply = get_create_reply_div(
        $(this).closest('.code-line-comment').data('comment_id'),
        $(this).closest('.code-line-container').data('line_number')
      );
      $(this).closest('.code-line-comment').after($reply);
      $reply.show(500);
    });
    $(".edit-comment").off("click");
    $(".edit-comment").on("click", function() {
      console.log($(this).closest('.code-line-comment').data());
    });
    $(".delete-comment").off("click");
    $(".delete-comment").on("click", function() {
      delete_comment(
        $(this).closest('.code-line-comment').data('comment_id'),
        $(this).closest('.code-line-container').data('line_number'),
        $(this)
      );
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
        $("#diff_div").html(data).promise().done(function() {
          add_load_more_listener();
          init_materialize_objs();
          add_toggle_listener();
          add_code_line_listeners();
        });
      });
      
      request.fail(function(jqXHR, textStatus) {
        alert( "Request failed: " + textStatus );
      });
      
      return false;    
    }
    get_diff();
  });

  //ship it button

    $(document).ready(function(){
      $("#shipit").click(function(){
        $.ajax({
          type: 'POST',
          data: ({name: "<?php echo $review_id;?>"}),
          url: './shipIt.php',
          success: function(data) {
            alert("You have approved this review!");
            window.location.reload();
          }
        });
      });

     $("#unship").click(function(){
        $.ajax({
          type: 'POST',
          data: ({name: "<?php echo $review_id;?>"}),
          url: './unshipit.php',
          success: function(data) {
            //alert(data);
            alert("You have unapproved this review.");
            window.location.reload();
          }
        });
      });

      //check to hide ship it button
      var review_id = "<?php echo $review_id; ?>";
      var actual_url = './check_ship.php?review_id=' + encodeURIComponent(review_id.trim());
      $.get(actual_url, function(data) {
        // show either button depending on thei ship status
        if (data == '0'){
            $('#shipit').show();
        }
        if (data == '1'){
            $('#unship').show();
        }

       });
    });

  </script>
  
  </body>
</html>