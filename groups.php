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

  <body>
  
    <?php include("navbar.php"); ?>

    <?php 

      echo "Create groups here";

    ?>

    <form id="submit_group_form" action="create_group.php" method="POST" class="col s12">
        </div>
        <div class="row">
          <div class="input-field col s12">
          <i class="material-icons prefix">group_work</i>
            <textarea id="group-name" name="group-name" class="materialize-textarea" maxlength="30" data-length="30"></textarea>
            <label for="group-name">Group Name</label>
          </div>   
        </div>

        <div class="row">
          <div class="input-field col s12">
          <i class="material-icons prefix">description</i>
            <textarea id="description" name="description" class="materialize-textarea" maxlength="200" data-length="200"></textarea>
            <label for="description">Description</label>
          </div>
        </div>

        <div class="row">
          <div class="input-field col s12">
          <i class="material-icons prefix">group_add</i>
            <textarea id="members" name="members" class="materialize-textarea" maxlength="200" data-length="200"></textarea>
            <label for="members">Add Group Members (comma delimited)</label>
          </div>
        </div>
     <input name="submit_group" type="submit" value="Submit" class="waves-effect waves-light btn" />

  </html>

