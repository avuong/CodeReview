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

  </head>

  <body onload="get_incoming_reviews();" style="background-color:#BFEFFF">

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
   

  <div class="switch-toggle switch-candy">
    <input id="incoming" name="view" type="radio" checked>
    <label for="incoming" onclick="get_incoming_reviews();">Incoming Reviews</label>

    <input id="outgoing" name="view" type="radio">
    <label for="outgoing" onclick="get_outgoing_reviews();">Outgoing Reviews</label>

    <a></a>
  </div>


   <div id=resultDiv style="position: relative; left:50px"></div>
 </body>
