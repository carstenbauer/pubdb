<?php

include_once('tools/db.php');
include_once('tools/io.php');

$db = new db();

$db->connect();

$publications = $db->getPublications();

?>

<h2>Projects</h2>

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
			    echo "<a href='?project=".$project["abbrev"]."'>".$project["abbrev"]. "</a>: " . $project["title"]. " <br>";
			}


			?>
		</p>
	</div>

</div>

<?php $db->close(); ?>