<!DOCTYPE html>

<?php require("authenticate_visitor.php"); ?>
	 
<html>
  <head>
    <meta charset="utf-8">
    <title> Home Page </title>

    <!-- JQuery -->
    <script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    
    <link rel="stylesheet" href="materialize-src/sass/materialize.css">
    <script src="materialize-src/js/bin/materialize.min.js"></script>

    <!-- AJAX -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gitgraph.js/1.10.0/gitgraph.js"></script>

    <!-- TOGGLE -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/css-toggle-switch/latest/toggle-switch.css" />

    <link href="styles/create_review.css" rel="stylesheet">
    <style>
      tbody tr {
        cursor: pointer;
      }
    </style>
  </head>

  <body onload="get_incoming_reviews();">

     <?php include("navbar.php"); ?>


   <script>
     function get_incoming_reviews(){
        var request = $.ajax({
          url: "./get_incoming_reviews.php",
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

     function get_outgoing_reviews(){
         var request = $.ajax({
          url: "./get_outgoing_reviews.php",
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

   </script>
   
   <!-- hidden input for refresh-->
  <input type="hidden" id="refresh" value="no">
  
  <div class="switch-toggle switch-candy">
    <input id="incoming" name="view" type="radio" checked>
    <label for="incoming" onclick="get_incoming_reviews();">Incoming Reviews</label>

    <input id="outgoing" name="view" type="radio">
    <label for="outgoing" onclick="get_outgoing_reviews();">Outgoing Reviews</label>

    <a></a>
  </div>

   <div class="create-form-container z-depth-2">
   <div id=resultDiv style="position: relative;"></div>
   </div>

   <script>
   //when user clicks on table row
        $('#resultDiv').on('click', 'table tr', function() {
            //get the Group name on the table row we clicked on to pass to PHP script
            var $row = $(this).closest("tr"),
            $tds = $row.find("td:nth-child(1)");

            console.log('hello');
            $.each($tds, function() {
                //below this the group name
                var $group_name = $(this).text();
                console.log($group_name);

                //need to get group name out of function above and here
                //encode URI spaces are weird need to encode them
                var actual_url = './review.php?id=' + encodeURIComponent($group_name.trim());
                location.href = actual_url;
   
            });

        });

        //refresh on back button
        $(document).ready(function(e) {
          var $input = $('#refresh');
          $input.val() == 'yes' ? location.reload(true) : $input.val('yes');
        });
   </script>
 </body>
