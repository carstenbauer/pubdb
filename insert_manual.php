<?php

include_once('config/config.php');
include_once('tools/db.php');
include_once('tools/parser/arxiv.php');
include_once('tools/parser/aps.php');
include_once('tools/parser/nature.php');

$db = new db();
$db->connect();
$projects = $db->getProjects();

function contains($string, $array, $caseSensitive = false)
{
    $stripedString = $caseSensitive ? str_replace($array, '', $string) : str_ireplace($array, '', $string);
    return strlen($stripedString) !== strlen($string);
}

function checkUserInput(){
    // Check that Authors are comma-separated, URL is real URL etc.
    // Check that journal is none of the known journals, or redirect to usual insert page
    if (empty($_POST['puburl'])||empty($_POST['pubyear'])||empty($_POST['pubtitle'])||empty($_POST['pubjournal'])||empty($_POST['pubidentifier'])||empty($_POST['pubauthors'])) {
        return 'Please fill out all fields.';
    } 
    if (filter_var($_POST['puburl'], FILTER_VALIDATE_URL) === FALSE) {
        return 'Not a valid URL.';
    }
    if (!is_numeric($_POST['pubyear'])) {
        return 'Year is not a number.';
    }

    return true;
}

if (isset($_POST["insertForm"])||isset($_POST["confirm"])) {
    $inputCheckMsg = checkUserInput();
    if ($inputCheckMsg === true){
        $validInput = true;
        $paper["title"] = $_POST['pubtitle'];
        $paper["authors"] = $_POST['pubauthors'];
        $paper["journal"] = $_POST['pubjournal'];
        $paper["url"] = $_POST['puburl'];
        $paper["year"] = $_POST['pubyear'];
        $paper["bibtex"] = $_POST['pubbibtex'];
        $paper["identifier"] = $_POST['pubidentifier'];
    }
    else
        $validInput = false;
}

?>

<html>
<head>
    <script type="text/javascript" src="js/script.js"></script>
    <script type="text/javascript">
        function printPublication(){
            var pub = <?php echo (!$validInput)?"none":json_encode($paper); ?>;
            if (pub != "none") {
                var pubstr = PublicationToHTMLString(pub);
                pubp.innerHTML = pubstr;
                // foundbox.style.display = 'inline';
            }
        }
    </script>
</head>

<body onload='printPublication();'>

<h2>Insert publication (manual)</h2>

<p><b>Please use the manual insertion only for unsupported journals to avoid messiness!</b> <br>
If you think that your publication is no exception and the corresponding journal should be admitted to the automatic insertion system, please write an email to <a href="mailto:bauer@thp.uni-koeln.de">bauer@thp.uni-koeln.de</a>.</p>

<form action="index.php?sec=insert_manual" method="post">
  Authors <small class="small">(Given name first, comma-separated, e.g. 'Max Mustermann, John Doe')</small><br>
  <input type="text" name="pubauthors" value="<?php echo (!empty($_POST))?$_POST["pubauthors"]:""; ?>" required><br>
  Title<br>
  <input type="text" name="pubtitle" value="<?php echo (!empty($_POST))?$_POST["pubtitle"]:""; ?>" required><br>
  Journal <small class="small">(How it should be displayed)</small><br>
  <input type="text" name="pubjournal" value="<?php echo (!empty($_POST))?$_POST["pubjournal"]:""; ?>" required><br>
  Identifier <small class="small">(Depending on the journal, this could be 'Volume, Pages')</small><br>
  <input type="text" name="pubidentifier" value="<?php echo (!empty($_POST))?$_POST["pubidentifier"]:""; ?>" required><br>
  URL<br>
  <input type="text" name="puburl" value="<?php echo (!empty($_POST))?$_POST["puburl"]:""; ?>" required><br>
  Year<br>
  <input type="text" name="pubyear" value="<?php echo (!empty($_POST))?$_POST["pubyear"]:""; ?>" required><br>
  BibTex (optional)<br>
  <textarea rows="7" cols="40" name="pubbibtex" value="<?php echo (!empty($_POST))?$_POST["pubbibtex"]:""; ?>"></textarea><br>
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
    
    if ($validInput !== true){
        echo "<b>".$inputCheckMsg."</b><br><br>";
    } elseif(!isset($_POST["projects"])||empty($_POST["projects"])) {
        echo "<b>You have to specify at least one project.</b><br><br>";
    } else {
        echo "<div><b>Your publication list entry will look as follows:</b><br><br>";
        ?>

        <p id="pubp"></p>
        <br>
        <form action="index.php?sec=insert_manual" method="post">
            <input type=hidden name="pubtitle" value="<?php echo $_POST["pubtitle"]; ?>" >
            <input type=hidden name="pubauthors" value="<?php echo $_POST["pubauthors"]; ?>" >
            <input type=hidden name="pubjournal" value="<?php echo $_POST["pubjournal"]; ?>" >
            <input type=hidden name="pubidentifier" value="<?php echo $_POST["pubidentifier"]; ?>" >
            <input type=hidden name="puburl" value="<?php echo $_POST["puburl"]; ?>" >
            <input type=hidden name="pubyear" value="<?php echo $_POST["pubyear"]; ?>" >
            <input type=hidden name="pubbibtex" value="<?php echo $_POST["pubbibtex"]; ?>" >
            <?php
            foreach($_POST["projects"] as $project){
                echo "<input type=hidden name='projects[]' value='".$project."' >";
            }
?>

    <label> <small>Please confirm that this publication should be added to project(s) <?php echo join(', ', $_POST["projects"]); ?>.</small></label><br>
    Password: <input type="password" name="pw" >

            <input type="submit" name="confirm" value="Confirm">
        </form>
        </div>
        <?php

    }
}

?>

<?php

# Paper has been confirmed for insertion
if (isset($_POST["confirm"])){

    if ($validInput !== true){
        echo "<b>".$inputCheckMsg."</b><br><br>";
    } elseif(!isset($_POST["projects"])||empty($_POST["projects"])) {
        echo "<b>You have to specify at least one project.</b><br><br>";
    } elseif ($_POST["pw"]!==INSERTPASSWORD) {
        echo "<b>Oops, the password is not correct!</b>";
    } else {
        $paper["projects"] = $_POST["projects"];
        $succ = $db->insertPaper($paper);
        if ($succ) {
            echo "The paper has been successfully added to our database. Thank you for taking the time!";
        } else {
            echo "There was a problem with our database. Please try again.";
        }
    }
}

?>


</body>

<?php
$db->close();
?>
