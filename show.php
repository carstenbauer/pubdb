<html>
<head>

	<?php

	include_once('tools/db.php');

	$db = new db();

	$db->connect();

	$publications = $db->getPublications();
	?>



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


<!-- Javascript !-->
<script type="text/javascript" src="js/script.js"></script>

	<script type="text/javascript">

		var pubs = <?php echo json_encode($publications); ?>;

		function showOptions(id){
			var pub = pubs.filter(function(x) { return x.id.toString()==id; })[0];
			var puboptions = document.getElementById("pub".concat(pub.id.toString()).concat("options"));
			puboptions.removeAttribute("hidden");
		}

		function hideOptions(id){
			var pub = pubs.filter(function(x) { return x.id.toString()==id; })[0];
			var puboptions = document.getElementById("pub".concat(pub.id.toString()).concat("options"));
			puboptions.setAttribute("hidden", true);
		}

		function openBibTexModal(id){
			var pub = pubs.filter(function(x) { return x.id.toString()==id; })[0];
			alert(pub.bibtex.toString());
		}

		function byProject(pub_obj){
			if (pub_obj.projects.toString().includes(this.toString()))
				return true;
			else
				return false;
		}

		function printPublication(pub) {
			if (pub.bibtex.toString()!="")
				pubp.innerHTML = pubp.innerHTML + "<span id='pub" + pub.id.toString() + "' class=publication onmouseover='showOptions(".concat(pub.id.toString()).concat(")' onmouseout='hideOptions(").concat(pub.id.toString()).concat(")'>") + PublicationToHTMLString(pub) + "<span id='pub" + pub.id.toString() + "options' class=publication-options hidden>[<a href=?sec=update&id=".concat(pub.id.toString()) + ">Update</a>, <a href='javascript:openBibTexModal(".concat(pub.id.toString()) + ")'>BibTex</a>]</span></span>" + "<br><br>";
			else 
				pubp.innerHTML = pubp.innerHTML + "<span id='pub" + pub.id.toString() + "' class=publication onmouseover='showOptions(".concat(pub.id.toString()).concat(")' onmouseout='hideOptions(").concat(pub.id.toString()).concat(")'>") + PublicationToHTMLString(pub) + "<span id='pub" + pub.id.toString() + "options' class=publication-options hidden>[<a href=?sec=update&id=".concat(pub.id.toString()) + ">Update</a>]</span></span>" + "<br><br>";
		}

		function DoFilter(project) {
			var pubsf = pubs;
			if (project != "all")
				pubsf = pubs.filter(byProject, project);

			if (Object.keys(pubsf).length > 0) {
				pubp.innerHTML = "";
				pubsf.forEach(printPublication);
				MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
			} else {
				pubp.innerHTML = "0 results.";
			}
		}
	</script>

</body>

<?php $db->close(); ?>
