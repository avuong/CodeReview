<!DOCTYPE html>

<?php require("authenticate_visitor.php"); ?>

<html>

  <head>
      <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <?php
      $title = "Clone";
      include("head.php"); 
    ?>
    
  </head>

  <body onload="get_groups();">

    <?php include("navbar.php"); ?>

    <p> Create groups <a href=./groups.php> here </a></p>
    
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

     </script>
     <div id=resultDiv style="position: relative; left:50px"></div>

  </body>