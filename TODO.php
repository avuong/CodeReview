<?php 
$title = "TODO";
include("head.php");
include("navbar.php"); 
?>
<div class="container">
  <p><i>
    * This is a temporary page for debugging/developing
  </i></p>
  <p>
    Since the presentation is coming up and we might have random things that 
    we run into and want to implement, I figure this is an easy way to keep 
    track of those.
  </p>

  <h5>TODO:</h5>
  <ul class="collection">
    <li class="collection-item">
      Fix the Materialize buttons so that when you click on the margins it still registers as a click
    </li>
    <li class="collection-item">
      Apparently the order you choose commits in matters. We should always put the earlier commit before
      the later one. Update: Fixed 
    </li>
    <li class="collection-item">
      <a href="https://github.com/pdrumm/SpaceGame/commit/d9c9586726bb3378703a670545e9892c0dd14034">This</a>
      should not be possible...
    </li>
    <li class="collection-item">
      -Basic functionality of groups is done -> when they create a group we will write that to the database and add all the correct users into to users_groups_junction table...need to make form look nicer and do some error checking. Ex.) multiple groups with same name can be made...need unique constraint? , etc...
      <br>-Chip for users
      <br>-display table of groups to users on different page than form
      <br>-display which users are in the group (perhaps clickable link on group name which goes to another page or opens modal with users list)
    </li>
    <li class="collection-item">
      Add code styling to get_file_version.php
    </li>
    <li class="collection-item">
      <b>DEMO:</b> Remove TODO from the navbar
    </li>
    <li class="collection-item">
      <b>DEMO or at least before final submit:</b> Just manually simulate different user activity to make sure everything is running smoothly
    </li>
  </ul>
</div>
