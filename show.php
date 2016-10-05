<?php

include_once('tools/db.php');
include_once('tools/io.php');

$db = new db();

$db->connect();

$publications = false;

if(isset($_GET["project"])){

    $publications = $db->getPublicationsOfProject($_GET["project"]);

    if ($publications===false){
        echo "No valid project specified. Showing all publications.";
        $publications = $db->getPublications();
    } 
} else {
    $publications = $db->getPublications();
}

?>

<h2>Projects</h2>

<br>
<a href="?">Show All</a>
<br>

<p>
<?php

$projects = $db->getProjects();
foreach($projects as $project){
    echo "<a href='?project=".$project["abbrev"]."'>".$project["abbrev"]. "</a>: " . $project["title"]. " <br>";
}


?>
</p>

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


$db->close();
?>
</p>
