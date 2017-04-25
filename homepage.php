<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8">
    <title> Home Page </title>

    <link rel="stylesheet" href="materialize-src/sass/materialize.css">
    <script src="materialize-src/js/bin/materialize.min.js"></script>

  </head>

  <body>

     <?php require("authenticate_visitor.php"); ?>
     <?php include("navbar.php"); ?>

     <div id="sidebar">
     <ul id="slide-out" style="position: relative; border: none;top: 10em;" class="side-nav fixed">
      <li><a href="#!">Incoming Reviews</a></li>
      <li><a href="#!">Outgoing Reviews</a></li>
    </ul>
    </div> 
 </body>
