<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8">
    <title> Group 2 Code Review Project</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,50" rel="stylesheet">

	<!-- JQuery -->
	<script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
	
	<!-- Materialize -->
	<!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.1/css/materialize.min.css">
    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.1/js/materialize.min.js"></script>

    <!--Prettify for syntax highlighting -->
    <script src="https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js"></script>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.11.0/styles/default.min.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.11.0/highlight.min.js"></script>

	<!-- gitgraph -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/gitgraph.js/1.10.0/gitgraph.css">
    
    <!-- AJAX -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gitgraph.js/1.10.0/gitgraph.js"></script>

    <!-- custom css -->
	<link rel="stylesheet" href="select_commits.css">


</head>
  <body>
  
  <?php
	session_start();
	$review_id = $_SESSION['review_id'];

	// https://gist.github.com/varemenos/e95c2e098e657c7688fd
	$gitlog = <<< EOT
	git log --reverse --pretty=format:'{
		%n  "commit": "%H",
		%n  "abbreviated_commit": "%h",
		%n  "tree": "%T",
		%n  "abbreviated_tree": "%t",
		%n  "parent": "%P",
		%n  "abbreviated_parent": "%p",
		%n  "refs": "%D",
		%n  "encoding": "%e",
		%n  "subject": "%s",
		%n  "sanitized_subject_line": "%f",
		%n  "body": "%b",
		%n  "commit_notes": "%N",
		%n  "verification_flag": "%G?",
		%n  "signer": "%GS",
		%n  "signer_key": "%GK",
		%n  "author": {
			%n    "name": "%aN",
			%n    "email": "%aE",
			%n    "date": "%aD"%n  
		},
		%n  "commiter": {
			%n    "name": "%cN",
			%n    "email": "%cE",
			%n    "date": "%cD"%n  
		}%n},'
EOT;
	$format = <<< EOT
	| sed "$ s/,$//" | tr '\r\n' ' ' | awk 'BEGIN { print("[") } { print($0) } END { print("]") }'
EOT;
	$cmd = "cd /tmp/git_clone/$review_id && $gitlog $format";
	$commit_tree = shell_exec($cmd);
	#echo "<pre>$commit_tree</pre>";
  ?>
  
  <div id="mySidenav" class="sidenav container">
  <h5>Select two commits</h5>
  <form name="get_diff" action="" id="get_diff" method="POST" onsubmit="return validateForm()">
        <input placeholder="Commit #1" name="diff1" id="commit1" type="text" required readonly/>
        <input placeholder="Commit #2" name="diff2" id="commit2" type="text" required readonly/>
      <input name="diff_submit" id="diff_submit" type="button" value="Get Diff!" class="waves-effect waves-light btn" />
  </form>
  </div>
  <canvas id="gitGraph"></canvas>

  <script>
	// populate GitGraph
	var commitTree = <?php echo $commit_tree; ?>;
	console.log(commitTree);
	children = {};
	for (var i=0; i<commitTree.length; ++i) {
	  var commit = commitTree[i].commit;
	  var parent = commitTree[i].parent;
	  children[parent] = children[parent] || [];
	  children[parent].push(commit);
	}
	
	console.log(children);
	var gitgraph = new GitGraph({
		template: "metro",
		orientation: "vertical-reverse",
		mode: "extended"
	});
	gitgraph.template.commit.message.displayBranch = false;
	gitgraph.template.commit.dot.strokeColor = "#FFD600";
	gitgraph.canvas.addEventListener("commit:mouseover", function (event) {
		this.style.cursor = "pointer";
	});
	gitgraph.canvas.addEventListener("commit:mouseout", function (event) {
		this.style.cursor = "auto";
	});
	console.log(gitgraph);
	
	var master = gitgraph.branch("master");

	var visited = {};
	var map_head = {};
	map_head[""] = master;
	
	function clicked(obj) {
		console.log(obj);
		var c1 = document.getElementById("commit1");
		var c2 = document.getElementById("commit2");
		
		if (!obj.representedObject.selected) {

			if (c1.value && c2.value) {
				alert("There are already two commits selected.");
			} else if (c1.value) {
				c2.value = obj.representedObject.commit;
				obj.tag = "Commit #2";
				obj.representedObject.selected = true;
				obj.dotStrokeWidth = 20;
			} else {
				c1.value = obj.representedObject.commit;
				obj.tag = "Commit #1";
				obj.representedObject.selected = true;
				obj.dotStrokeWidth = 20;
			}
		} else {
			obj.representedObject.selected = false;
			obj.dotStrokeWidth = null;
			if (obj.tag === "Commit #1")
				c1.value = "";
			else
				c2.value = "";
			obj.tag = null;
		}
		gitgraph.render();
	}
	
	function isChildOf(child, parent) {
		var curr = child;
		while (curr.parentBranch) {
			if (curr.parentBranch === parent) 
				return true;
			curr = curr.parentBranch;
		}
		return false;
	}
	
	for (var i=0; i<commitTree.length; ++i) {
	  var parent = commitTree[i].parent;
	  var commit = commitTree[i].commit;
	  
	  var message = commitTree[i].subject;
	  var author = commitTree[i].author.email;
	  var sha1 = commitTree[i].abbreviated_commit;
	  var commitMessage = {
		  message: message, 
		  author: author, 
		  sha1: sha1,
		  showLabel: true,
		  representedObject: { // https://github.com/nicoespeon/gitgraph.js/blob/develop/src/gitgraph.js#L659
			  commit: commit,
			  selected: false
		  },
		  onClick: function(){
//			  clicked(this.representedObject);
			  clicked(this);
		  }
	  };
	  /*
	  console.log(commitTree[i]);
	  console.log(commit);
	  console.log(parent);
	  */
	  if (children[parent].length > 1 && !visited[parent]) {
		// First child found where a branch was created
		// - branch, then commit
		map_head[commit] = map_head[parent].branch(sha1);
		map_head[commit].commit(commitMessage);
		visited[parent] = true;
	  } else if (children[parent].length > 1) {
		// Second (or higher) child found where a branch was created
			// - just commit
		map_head[parent].commit(commitMessage);
		map_head[commit] = map_head[parent];
	  } else if (parent.split(" ").length > 1) {
		// Merge commit
		var [b1, b2] = parent.split(" ");
		if (isChildOf(map_head[b1], map_head[b2])) {
			map_head[b1].merge(map_head[b2], commitMessage);
			map_head[commit] = map_head[b2];
		} else {
			map_head[b2].merge(map_head[b1], commitMessage);
			map_head[commit] = map_head[b1];
		}
	  } else {
		// Standard commit
		map_head[parent].commit(commitMessage);
		map_head[commit] = map_head[parent];
	  }
	}

  
  function validateForm() {
	var d1 = document.forms["get_diff"]["diff1"].value;
	var d2 = document.forms["get_diff"]["diff2"].value;
	var patt = /\s/;
	valid1 = d1.length==40 && !patt.test(d1);
	valid2 = d2.length==40 && !patt.test(d2);
	if (!valid1) {
		alert("Diff #1 is not valid");
		return false;
	} else if (!valid2) {
		alert("Diff #2 is not valid");
		return false;
	} else {
		return true;
	}
  }

  </script>

    <script type="text/javascript">

     $("#diff_submit").on("click", function(){
        var postData = $("#get_diff").serializeArray();
        var request = $.ajax({
          url: "./diff.php",
          type: "POST",
          data: postData,
          success: function(data){
            $('#resultDiv').html(data);
            $('#modal1').modal('open');
            hljs.initHighlighting()
          }
        });
        
        request.fail(function(jqXHR, textStatus) {
          alert( "Request failed: " + textStatus );
        });
        
        return false;    
     });

      $(document).ready(function(){
    // the "href" attribute of .modal-trigger must specify the modal ID that wants to be triggered
    $('.modal').modal();
  });
   </script>

<script>hljs.initHighlightingOnLoad();</script>
  <!-- Modal Structure -->
  <div id="modal1" class="modal modal-fixed-footer">
    <div class="modal-content">
      <h4>Diff Review</h4>
         <pre>
           <code id=resultDiv style="text-align:right;">
           </code>
         </pre>
     </div>
    <div class="modal-footer">
      <a href="./create_review.php" class="modal-action modal-close waves-effect waves-green btn-flat ">Submit</a>
    </div>
  </div>


</body>
</html>
