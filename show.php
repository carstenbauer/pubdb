<html>
<head>

	<?php

	include_once('tools/db.php');
	include_once('tools/io.php');

	$db = new db();

	$db->connect();

	$publications = $db->getPublications();
	?>

	<script type="text/javascript" src="js/script.js"></script>

	<script type="text/javascript">
		function byProject(pub_obj){
			if (pub_obj.projects.toString().includes(this.toString()))
				return true;
			else
				return false;
		}

		function printPublication(pub) {
	    	pubp.innerHTML = pubp.innerHTML + PublicationToHTMLString(pub) + "<br><br>"; 
		}

		function DoFilter(project) {
			var pubs = <?php echo json_encode($publications); ?>;
			if (project != "all")
				pubs = pubs.filter(byProject, project);

			pubp.innerHTML = "";
			pubs.forEach(printPublication);
			MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
		}
	</script>

</head>
<body onload='DoFilter("all");'>

	<div id="container">	

		<div id="main">
			<br>
			<h2>Publications</h2>
			<br>

			<p id="pubp">
			</p>
		</div>

		<div id="filter">
			<br>
			<a href='javascript:DoFilter("all")' class="small">Show All</a>
			<br>

			<p class="small">
				<?php

				$projects = $db->getProjects();
				foreach($projects as $project){
				    echo "<a href='javascript:DoFilter(\"".$project["abbrev"]."\");'>".$project["abbrev"]. "</a>: " . $project["title"]. " <br>";
				}


				?>
			</p>
		</div>

	</div>

</body>

<?php $db->close(); ?>