<!DOCTYPE html>

<?php require("authenticate_visitor.php"); ?>

<html>

  <head>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>    <?php
      $title = "Clone";
      include("head.php"); 
    ?>
    
    <link href="styles/view_groups.css" rel="stylesheet">
  </head>

  <body onload="get_groups();">

    <?php include("navbar.php"); ?>
    
    <script>
     function get_groups(){
        var request = $.ajax({
          url: "./get_groups.php",
          type: "POST",
          success: function(data){
            $('#resultDiv').html(data);
          }
        });
        
        request.fail(function(jqXHR, textStatus) {
          alert( "Request failed: " + textStatus );
        });
        
        return false;    
     }

    function get_members(){
        var request = $.ajax({
          url: "./get_groups.php",
          type: "POST",
          success: function(data){
            $('#resultDiv').html(data);
          }
        });
        
        request.fail(function(jqXHR, textStatus) {
          alert( "Request failed: " + textStatus );
        });
        
        return false;    
     }
      //need this to trigger modal opening
      $(document).ready(function(){
        // the "href" attribute of .modal-trigger must specify the modal ID that wants to be triggered
        $('.modal').modal();
      });
          
     </script>
     <div class="group-members-container z-depth-2">
       <div id="resultDiv" style="position: relative;"></div>
     </div>
       <!-- Modal Structure -->
      <div id="modal1" class="modal modal-fixed-footer">
        <div class="modal-content">
          <h4>Group Members</h4>
          <div id="modal-body">
            <p></p>
          </div>
        </div>
        <div class="modal-footer">
          <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat ">OK</a>
        </div>
      </div>

     <script>
        //when user clicks on table row
        $('#resultDiv').on('click', 'table tr', function() {
            //get the Group name on the table row we clicked on to pass to PHP script
            var $row = $(this).closest("tr"),
            $tds = $row.find("td:nth-child(1)");

            $.each($tds, function() {
                //below this the group name
                var $group_name = $(this).text();
                console.log($group_name);


                //need to get group name out of function above and here
                //encode URI spaces are weird need to encode them
                var actual_url = './get_members.php?name=' + encodeURIComponent($group_name.trim());
                // get group members
                //TODO: pass in argument of $group_name
                $.get(actual_url, function(data) {
                    // use the result
                    $('#modal-body').html(data);
                });
            });

        });

     </script>

  </body>