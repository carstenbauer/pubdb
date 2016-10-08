<?php

include_once('tools/db.php');
include_once('tools/io.php');

$db = new db();

$db->connect();

$publications = $db->getPublications();
?>


<script type="text/javascript">
	function byProject(pub_obj){
		if (pub_obj.projects.toString().includes(this.toString()))
			return true;
		else
			return false;
	}

	function MyFilter(project) {
		var pubs = <?php echo json_encode($publications); ?>;
		// alert(pubs[3].projects);
		// alert(project);
		var publications = pubs.filter(byProject, project);
		// JSON.stringify(publications);
		// $publications=json_decode($_POST['publications']);
	}
</script>


<div id="container">	

	<div id="main">
		<br>
		<h2>Publications</h2>
		<br>

		<p>
		<?php

		if ($publication===false){
		    echo "Error reading publications.";
		} else if (!empty($publications)) {
		    foreach($publications as $pub){
		        echo Output::PaperToHTMLString($pub)."<br><br>";
		    }
		} else {
		    echo "0 results";
		}

		?>
		</p>
	</div>

	<div id="filter">
		<br>
		<a href="?" class="small">Show All</a>
		<br>

		<p class="small">
			<?php

			$projects = $db->getProjects();
			foreach($projects as $project){
			    echo "<a href='javascript:MyFilter(\"".$project["abbrev"]."\");'>".$project["abbrev"]. "</a>: " . $project["title"]. " <br>";
			}


			?>
		</p>
	</div>

</div>

<?php $db->close(); ?>