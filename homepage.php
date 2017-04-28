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

  </head>

  <body>

     <?php include("navbar.php"); ?>

     <div id="sidebar">
     <ul id="slide-out" style="position: relative; padding-top: 3em;" class="side-nav fixed">
      <li><a onclick='get_reviews();'>Incoming Reviews</a></li>
      <li><a href="#!">Outgoing Reviews</a></li>
    </ul>
    </div> 

   <script>
     function get_reviews(){
       console.log("hello");
         var request = $.ajax({
          url: "./get_reviews.php",
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

   <div id=resultDiv style="position: relative; left:50px"></div>
 </body>
