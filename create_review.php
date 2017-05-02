<!DOCTYPE html>

<?php 
  #$referrer = "/select_commits.php";
  require("authenticate_visitor.php");
?>

<?php
  // Ensure a POST request method
  if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo "request method error";
    exit;
  } else {
    $_SESSION['diff1'] = $_POST['diff1'];
    $_SESSION['diff2'] = $_POST['diff2'];
  }
  
  // get the user's username
  $conn = oci_connect("guest", "guest", "xe")
	or die("<br>Couldn't connect");

  $fxn = "begin :r := shallowbugspack.get_user_name(:id); end;";
  $stmt = oci_parse($conn, $fxn);
  oci_bind_by_name($stmt, ':id', $user_id);
  oci_bind_by_name($stmt, ':r', $user_name);
  oci_execute($stmt);
  oci_close($conn);
?>

<html>
  <?php
	$title = "Clone";
  $include = '
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="styles/autocomplete.css" rel="stylesheet">
    <script src="scripts/jquery.autocomplete.min.js"></script>
    <link href="styles/create_review.css" rel="stylesheet">
  ';
	include("head.php"); 
	?>
  
  <body>
  
	<?php include("navbar.php"); ?>

    <div class="create-form-container z-depth-2">
    <div class="row">
      <form id="submit_review_form" action="submit_review.php" method="POST" class="col s12">
        
        <h4>Form Details</h4>
        
        <div class="row">
          <div class="input-field col s3">
            <input readonly value="<?php echo $user_name; ?>" id="owner"  name="owner" type="text" class="validate">
            <label for="owner">Owner</label>
          </div>
        </div>
        
        <div class="row">
          <div class="input-field col s12">
            <input id="summary" name="summary" type="text" class="validate" maxlength="100" data-length="100" required>
            <label for="summary">Summary</label>
          </div>
        </div>
          
        <div class="row">
          <div class="input-field col s12">
            <textarea id="description" name="description" class="materialize-textarea" maxlength="200" data-length="200"></textarea>
            <label for="description">Description</label>
          </div>
        </div>
        
        <div class="row">
          <div class="col s1">
            Reviewers:	
          </div>
          <div class="col s5">
            <div class="row">
              <div class="input-field col s12">
                <i class="material-icons prefix">person_add</i>
                <div id="add_user_chip" class="chips"></div>
                <label for="add_user_chip">User</label>
              </div>
            </div>
          </div>
          <div class="col s5">
            <div class="row">
              <div class="input-field col s12">
                <i class="material-icons prefix">group_add</i>
                <div id="add_group_chip" class="chips"></div>
                <label for="add_group_reviewer">Group</label>
              </div>
            </div>
          </div>
        </div>
        
        <button name="submit_review" type="submit" class="waves-effect waves-light btn" >Submit</button>
        
      </form>
    </div>
    </div>

    <script>      
      $(function() {
        /* Instantiate Material Chips */
        $('#add_user_chip').material_chip({
          placeholder: "add user"
        });
        $('#add_group_chip').material_chip({
          placeholder: "add group"
        });
        // keep track of chip data
        var $user_chip = $('#add_user_chip');
        var user_chip_data = $user_chip.material_chip('data').slice();
        var $group_chip = $('#add_group_chip');
        var group_chip_data = $group_chip.material_chip('data').slice();    
        
        /*
         * Add autocomplete to Reviewer input fields
         * Autocomplete info can be found here:
         *   - https://www.devbridge.com/sourcery/components/jquery-autocomplete/
         */
        function addUserReviewerListener() {
          $('#add_user_chip > input').devbridgeAutocomplete({
              serviceUrl: "read_users.php",
              minChars: 2,
              autoSelectFirst: true,
              onSelect: function (suggestion) {
                user_chip_data = $user_chip.material_chip('data').slice();
                var chip = {
                  id: suggestion.data,
                  tag: suggestion.value
                };
                user_chip_data.push(chip);
                $user_chip.material_chip({
                  data: user_chip_data.slice(),
                  secondaryPlaceholder: "add another user"
                });
                addUserReviewerListener();
              }
          });
          $('#add_user_chip > input').focus();
        }
        
        function addGroupReviewerListener() {
          $('#add_group_chip > input').devbridgeAutocomplete({
              serviceUrl: "read_groups.php",
              minChars: 2,
              autoSelectFirst: true,
              onSelect: function (suggestion) {
                group_chip_data = $group_chip.material_chip('data').slice();
                var chip = {
                  id: suggestion.data,
                  tag: suggestion.value
                };
                group_chip_data.push(chip);
                $group_chip.material_chip({
                  data: group_chip_data.slice()
                });
                addGroupReviewerListener();
              }
          });
          $('#add_group_chip > input').focus();
        }

        // Begin listening
        addUserReviewerListener();
        addGroupReviewerListener();
        $(document.activeElement).blur();
        
        /*
         * Add callbacks to the Material chips to prevent invalid
         * users and groups
         */
        $user_chip.on('chip.add', function(e, chip){
          $user_chip.material_chip({
            data: user_chip_data.slice()
          });
          addUserReviewerListener();
        });
        $group_chip.on('chip.add', function(e, chip){
          $group_chip.material_chip({
            data: group_chip_data.slice()
          });
          addGroupReviewerListener();
        });
        $user_chip.on('chip.delete', function(e, chip){
          user_chip_data = $user_chip.material_chip('data').slice();
        });
        $group_chip.on('chip.delete', function(e, chip){
          group_chip_data = $group_chip.material_chip('data').slice();
        });
      
        /*
         * Store the Material Chip data in hidden fields before
         * submitting the POST
         */
        $("#submit_review_form").submit(function(){
          // Create arrays of the user/group ids
          review_user_ids = [];
          for (var i=0; i<user_chip_data.length; ++i) {
            review_user_ids.push(user_chip_data[i].id);
          }
          review_group_ids = [];
          for (var i=0; i<group_chip_data.length; ++i) {
            review_group_ids.push(group_chip_data[i].id);
          }
          // create hidden elements to pass arrays via POST
          $('<input />').attr('type', 'hidden')
              .attr('name', 'reviewer_users')
              .attr('value', JSON.stringify(review_user_ids))
              .appendTo('#submit_review_form');              
          $('<input />').attr('type', 'hidden')
              .attr('name', 'reviewer_groups')
              .attr('value', JSON.stringify(review_group_ids))
              .appendTo('#submit_review_form');
          return true;
        })
      });
    </script>
    
  </body>

</html>
