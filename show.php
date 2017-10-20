<html>
<head>
	<!-- <base target="_parent" /> -->
	<link href="css/style.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel="stylesheet">

	<?php

	include_once('tools/db.php');

	$db = new db();

	$db->connect();

	$publications = $db->getPublications();
	?>



</head>
<body onload='DoFilter("showall");'>

	<div id="container">
			<span id="filter">
				<a id=showall class=projectfilter href='javascript:DoFilter("showall")'>Show all</a> or

					filter by project:
					<?php
					$projects = $db->getProjects();
					foreach($projects as $project){
						if($project["abbrev"] != "Z")
					    	echo "<a id=".$project["abbrev"]." class='projectfilter ".substr($project["abbrev"], 0, 1)."' href='javascript:DoFilter(\"".$project["abbrev"]."\");'>".$project["abbrev"]. "</a> ";
					}


					?>
			</span>

                        <span id="navigation"><a href="?sec=insert">&#43 Insert publication</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href='javascript:DoFilter("update");' id="update">&#43 Update reference</a></span>

			<p id="pubp">
			</p>

	</div>


<!-- Javascript !-->
<script type="text/javascript" src="js/script.js"></script>

	<script type="text/javascript">

		var pubs = <?php echo json_encode($publications); ?>;

		function showOptions(id){
			var pub = pubs.filter(function(x) { return x.id.toString()==id; })[0];
			var puboptions = document.getElementById("pub".concat(pub.id.toString()).concat("options"));
			puboptions.style.visibility = "visible";
		}

		function hideOptions(id){
			var pub = pubs.filter(function(x) { return x.id.toString()==id; })[0];
			var puboptions = document.getElementById("pub".concat(pub.id.toString()).concat("options"));
			puboptions.style.visibility = "hidden";
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

		function isArxiv(pub_obj){
			if (pub_obj.journal.toString().includes(this.toString()))
				return true;
			else
				return false;
		}

		function printPublication(pub) {
			var panel = document.getElementById(pub.year.toString());
			if (pub.bibtex.toString()!="")
				panel.innerHTML = panel.innerHTML + "<span id='pub" + pub.id.toString() + "' class=publication onmouseover='showOptions(".concat(pub.id.toString()).concat(")' onmouseout='hideOptions(").concat(pub.id.toString()).concat(")'>") + PublicationToHTMLString(pub) + "<span id='pub" + pub.id.toString() + "options' class=publication-options style=\"visibility: hidden;\">[<a href=?sec=update&id=".concat(pub.id.toString()) + ">Update</a>, <a href='javascript:openBibTexModal(".concat(pub.id.toString()) + ")'>BibTeX</a>]</span></span>" + "<br>";
			else
				panel.innerHTML = panel.innerHTML + "<span id='pub" + pub.id.toString() + "' class=publication onmouseover='showOptions(".concat(pub.id.toString()).concat(")' onmouseout='hideOptions(").concat(pub.id.toString()).concat(")'>") + PublicationToHTMLString(pub) + "<span id='pub" + pub.id.toString() + "options' class=publication-options style=\"visibility: hidden;\">[<a href=?sec=update&id=".concat(pub.id.toString()) + ">Update</a>]</span></span>" + "<br>";
		}

		function printPublicationWithOptions(pub) {
			var panel = document.getElementById(pub.year.toString());
			panel.innerHTML = panel.innerHTML + "<span id='pub" + pub.id.toString() + "' class=publication >" + PublicationToHTMLString(pub) + "<span id='pub" + pub.id.toString() + "options' class=publication-options style=\"visibility: hidden;\">[<a href=?sec=update&id=".concat(pub.id.toString()) + ">Update</a>]</span></span>" + "<br>";

			showOptions(pub.id);
		}

		var isupdate = false
		var curproject = "showall"
		document.getElementById("showall").style.fontWeight = "bold";
		function DoFilter(project) {
			var pubsf = pubs;

			// Update toggle
			if (project == "update"){
				isupdate = !isupdate;
				if (isupdate)
					document.getElementById("update").style.fontWeight = "bold";
				else
					document.getElementById("update").style.fontWeight = "400";
			}

			//  Highlight active project
			if ((project != curproject) && (project != "update")){
				document.getElementById(curproject).style.fontWeight = "400";
				document.getElementById(project).style.fontWeight = "bold";
				curproject = project;
			}

			if (curproject != "showall")
				pubsf = pubs.filter(byProject, curproject);
			else
				pubsf = pubs;

			if (isupdate)
				pubsf = pubsf.filter(isArxiv, "arxiv");

			if (Object.keys(pubsf).length > 0) {
				pubp.innerHTML = "";
				// pubsf.forEach(printPublication);
				var index, len;
				var lastyear = pubsf[0].year.toString();
				pubp.innerHTML = pubp.innerHTML + "<button class='accordion active' onclick='javascript:this.nextElementSibling.classList.toggle(\"show\");javascript:this.classList.toggle(\"active\");'>"+lastyear+"</button><div id='"+lastyear+"' class='panel show'><p>";
				for (index = 0, len = pubsf.length; index < len; ++index) {
					var year = pubsf[index].year.toString();
					if (year!=lastyear){
						pubp.innerHTML = pubp.innerHTML + "</p></div>";
						pubp.innerHTML = pubp.innerHTML + "<button class='accordion active' onclick='javascript:this.nextElementSibling.classList.toggle(\"show\");javascript:this.classList.toggle(\"active\");'>"+year+"</button><div id='"+year+"' class='panel show'><p>";
						lastyear = year;
					}

					if (isupdate){
						printPublicationWithOptions(pubsf[index]);
					} else {
						printPublication(pubsf[index]);
					}
				}
				pubp.innerHTML = pubp.innerHTML + "</p></div>";
				MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
			} else {
				pubp.innerHTML = "<button class='accordion active' onclick='javascript:this.nextElementSibling.classList.toggle(\"show\");javascript:this.classList.toggle(\"active\");'>2016</button><div id='2016' class='panel show'><p><span class=publication>0 results.</span></p></div>";
			}
		}
	</script>

    <!-- MathJax -->
    <script type="text/x-mathjax-config">
    MathJax.Hub.Config({
      tex2jax: {inlineMath: [['%!','%!']]}
          });
    </script>
    <script type="text/javascript" async
      src="//cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-MML-AM_CHTML">
    </script>
</body>


<?php $db->close(); ?>
