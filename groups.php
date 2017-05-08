<!DOCTYPE html>

<?php require("authenticate_visitor.php"); ?>

<html>

  <head>
      <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
      <link href="styles/create_review.css" rel="stylesheet">
    <?php
      $title = "Clone";
        $include = '
          <link href="styles/autocomplete.css" rel="stylesheet">
          <script src="scripts/jquery.autocomplete.min.js"></script>
        ';
      include("head.php"); 
    ?>
    
  </head>

  <body>
  
    <?php include("navbar.php"); ?>

    <div class="create-form-container z-depth-2">
    <form id="submit_group_form" action="create_group.php" method="POST" class="col s12">
        <div class="row">
          <div class="input-field col s12">
          <i class="material-icons prefix">group_work</i>
            <textarea id="group-name" name="group-name" class="materialize-textarea" maxlength="30" data-length="30" required></textarea>
            <label for="group-name">Group Name</label>
          </div>   
        </div>

        <div class="row">
          <div class="input-field col s12">
          <i class="material-icons prefix">description</i>
            <textarea id="description" name="description" class="materialize-textarea" maxlength="200" data-length="200" required></textarea>
            <label for="description">Description</label>
          </div>
        </div>

        <div class="row">
          <div class="input-field col s12">
          <i class="material-icons prefix">group_add</i>
          
          <div id="add_user_chip" class="chips"></div>
          <label for="add_user_chip">Enter Group Members</label>
          <!--
            <textarea id="members" name="members" class="materialize-textarea" ></textarea>
            <label for="members">Add Group Members (comma delimited)</label>
          -->
          </div>
        </div>

        <div style='color:red;' id=no_members></div>
     <button name="submit_group" type="submit" value="Submit" class="waves-effect waves-light btn">SUBMIT</button>

     </form>
     </div>
     
     <script>
     $(function() {
       /* Instantiate Material Chips */
       $('#add_user_chip').material_chip({
         placeholder: "add user"
       });
       // keep track of chip data
      var $user_chip = $('#add_user_chip');
      var user_chip_data = $user_chip.material_chip('data').slice();
      /*
       * Add autocomplete to Reviewer input fields
       * Autocomplete info can be found here:
       *   - https://www.devbridge.com/sourcery/components/jquery-autocomplete/
       */
      function addUserReviewerListener() {
        $('#add_user_chip > input').devbridgeAutocomplete({
            serviceUrl: "read_users.php",
            minChars: 1,
            autoSelectFirst: true,
            onSelect: function (suggestion) {
              user_chip_data = $user_chip.material_chip('data').slice();
              var chip = {
                id: suggestion.data,
                tag: suggestion.value
              };
              for (var i=0; i<user_chip_data.length; --i) { 
                if (user_chip_data[i].id === chip.id) {
                  $('#add_user_chip > input').val("");
                  return;
                }
              }
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
      
      // Begin listening
      addUserReviewerListener();
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
      $user_chip.on('chip.delete', function(e, chip){
        user_chip_data = $user_chip.material_chip('data').slice();
      });
      
      /*
       * Store the Material Chip data in hidden fields before
       * submitting the POST
       */
      $("#submit_group_form").submit(function(){
        // Create arrays of the user/group ids
        review_user_ids = [];
        for (var i=0; i<user_chip_data.length; ++i) {
          review_user_ids.push(user_chip_data[i].id);
        }

        //need at least one additional member for group creation
        if (review_user_ids.length == 0){
            $('#no_members').html("*Please enter at least one member")
            return false;
        }

        // create hidden elements to pass arrays via POST
        $('<input />').attr('type', 'hidden')
            .attr('name', 'members')
            .attr('value', JSON.stringify(review_user_ids))
            .appendTo('#submit_group_form');              
        return true;
      })
     });
     </script>


  </html>

