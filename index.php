<!DOCTYPE html>

<?php require("authenticate_visitor.php"); ?>

<html>
  <?php
    $title = "Login";
    include("head.php"); 
  ?>
  
  <body style="background-color:#BFEFFF">
    
  <div class="valign-wrapper">
    <div class="valign container">
      <h3>Shallow Bugs</h3>
      <blockquote style="display: inline-block; margin: 0 0 20px 2.2em;">
        <h6>Given enough eyeballs, all bugs are shallow</h6>
        <h6>&#8211 Linus Torvalds</h6>
      </blockquote>
      <br>
      <form id="SignIn" action="./authenticate_signin.php" target="myIframe" method="POST">
        <label>User Name</label>
        <input name="username" type="text" size="25" />

        <label>Password:</label>
        <input name="password" type="password" size="25" />

        <input name="mySubmit" type="submit" value="Log In!" class="waves-effect waves-light btn" />
      </form>
      
      <iframe name="myIframe" frameborder="0" border="0" cellspacing="0" style="border-style: none;width: 100%; height: 30px;" scrolling="no"></iframe>

      <p>Don't have an account? </p>
      <a href="./signup.html"> Sign up here </a>
    </div>
  </div>

  </body>

  <style>
  html {
    width: 100%;
    height: 85%;
  }
  body {
    width: 100%;
    height: 100%;
  }
  div.valign-wrapper {
    width: 100%;
    height: 100%;
  }
  /* hanging opening quote */
  blockquote:before {
    display: block;
    height: 0;
    content: "“";
    margin-left: -1.1em; font: italic 400%/1 Cochin,Georgia,"Times New Roman", serif;
    color: #78909c;
  }
  </style>
  
</html>
