<?php
// Use default title if one was not set
if (!isset($title)) {
	$title = "Shallow Bugs";
}
?>

<head>
  <meta charset="utf-8">
  <title><?php echo $title; ?></title>
  <link href="https://fonts.googleapis.com/css?family=Roboto:300,50" rel="stylesheet">

  <!-- JQuery -->
  <script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
  <!-- Materialize -->
  <link rel="stylesheet" href="materialize-src/sass/materialize.css">
  <script src="materialize-src/js/bin/materialize.min.js"></script>
	
  <?php
  if (isset($include)) {
    echo $include;
  }
  ?>
</head>
