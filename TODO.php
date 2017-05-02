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
      Apparently the order you choose commits in matters. We should always put the earlier commit before
      the later one. Update: Fixed 
    </li>
    <li class="collection-item">
      <a href="https://github.com/pdrumm/SpaceGame/commit/d9c9586726bb3378703a670545e9892c0dd14034">This</a>
      should not be possible...
    </li>
    <li class="collection-item">
      <br>-Chip for users
      <br>-Add styling
      <br>-Assuming group name only appears once?
      <br> Group Integration with creating review
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
    <li class="collection-item">
      Add functionality to simply upload a diff/patch instead of using our git clone tool
    </li>
    <li class="collection-item">
      when creating a new review, autopopulate the review summary with the commit message
    </li>
    <li class="collection-item">
      <b>DEMO:</b> list the reviewers in the Review's Detail page
    </li>
    <li class="collection-item">
      add line #s to the review diff page
    </li>
    <li class="collection-item">
      add ability to expand/hide comments in the review diffs page
    </li>
  </ul>
</div>
