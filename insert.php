<h2>Insert publication</h2>

<p><small>Supported sources: Phsical Review A-E, Physical Review Letters, Review of Modern Physics, arXiv</small></p>

<?php

include_once('tools/db.php');
include_once('tools/parser/arxiv.php');
include_once('tools/parser/aps.php');
include_once('tools/io.php');

$db = new db();
$db->connect();
$projects = $db->getProjects();


function identifierToPaper($pubidstr){
    # Check if we have arxiv or aps identifier
    # TODO Make "waterproof"!
    if (strpos($pubidstr,"Phys") !== False) {
        // Assume aps identifier
        $paper = apsParser::parse($pubidstr);
    } else {
        // Assume arxiv
        $paper = arxivParser::parse($pubidstr);
    }

    return isset($paper)?$paper:False;
}

?>


<form action="index.php?sec=insert" method="post">
  Publication Identifier:<br>
  <input type="text" name="pubidstr" value="<?php echo (!empty($_POST))?$_POST["pubidstr"]:""; ?>" required><br><br>
  Associated project(s):<br>
  <select class="selectpicker" name="projects[]" multiple required>
	<?php
        if (!empty($projects)) {
            foreach($projects as $project){
                if (!empty($_POST["projects"]) && in_array($project["abbrev"],$_POST["projects"])){
                    echo "<option value=".$project["abbrev"]." selected>".$project["abbrev"]."</option>";
                } else {
                    echo "<option value=".$project["abbrev"].">".$project["abbrev"]."</option>";
                }
            }
	} else {
	    echo "0 results";
	}
	?>
  </select>
  <br><br>
  <input type="submit" name="insertForm" value="Submit">
</form>


<br><br>

<?php

# Insert form has been submitted
if (isset($_POST["insertForm"])) {
    

    # TODO User must select at least one project. Maybe possible to enforce in html form?
    
    $paper = identifierToPaper($_POST["pubidstr"]);

    if ($paper === false){
        echo "<b>No paper is matching the given identifier.</b><br><br>";
    } else {
        echo "<b>We found the following paper:</b><br><br>";
        echo Output::PaperToHTMLString($paper);
        echo "<br><br>";
        ?>
        <form action="index.php?sec=insert" method="post">
            <input type=hidden name="pubidstr" value="<?php echo $_POST["pubidstr"]; ?>" >
            <?php
            foreach($_POST["projects"] as $project){
                echo "<input type=hidden name='projects[]' value='".$project."' >";
            }
?>
  Password: <input type="text" name="pw" >

            <input type="submit" name="confirm" value="Confirm">
            <label> <small>(Please confirm that this paper should be added to project(s) <?php echo join(', ', $_POST["projects"]); ?>)</small></label>
        </form>
        <?php

    }
}

?>

<?php

# Paper has been confirmed for insertion
if (isset($_POST["confirm"])){
    
    if ($_POST["pw"]!=="Superposition") {
        echo "<b>Oops, the password is not correct!</b>";
        exit();
    }


    $paper = identifierToPaper($_POST["pubidstr"]);
    $paper["projects"] = $_POST["projects"];
    $succ = $db->insertPaper($paper);
    if ($succ) {
        echo "The paper has been successfully added to our database. Thank you for taking the time!";
    } else {
        echo "There was a problem with our database. Please try again.";
    }
}

?>

<?php

$db->close();

?>
