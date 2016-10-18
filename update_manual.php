<?php

include_once('config/config.php');
include_once('tools/db.php');
include_once('tools/generic_functions.php');
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
    if (empty($_POST['puburl'])||empty($_POST['pubyear'])||empty($_POST['pubmonth'])||empty($_POST['pubtitle'])||empty($_POST['pubjournal'])||empty($_POST['pubauthors'])) {
        return 'Please fill out all fields.';
    } 
    if (empty($_POST['pubidentifier']))
        if (empty($_POST['pubvolume']) || empty($_POST['pubnumber']))
            return 'You must either fill out BOTH the fields \'Volume\' and \'Article or page number\' or the single field \'Identifier\'';
    if (filter_var($_POST['puburl'], FILTER_VALIDATE_URL) === FALSE) {
        return 'Not a valid URL.';
    }
    if (!is_numeric($_POST['pubyear'])) {
        return 'Year is not a number.';
    }

    return true;
}

$publications = $db->getPublications();
$validID = false;
$oldpaper = false;
$id = isset($_GET['id'])?$_GET['id']:-1;
foreach ($publications as $p) {
    if ($p["id"]==$id) {
        $validID = true;
        $oldpaper = $p;
    }
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
        $paper["month"] = monthStrToInt($_POST['pubmonth']);
        $paper["bibtex"] = $_POST['pubbibtex'];
        echo $paper['bibtex'];
        $paper["identifier"] = $_POST['pubidentifier'];
        $paper["volume"] = $_POST['pubvolume'];
        $paper["number"] = $_POST['pubnumber'];
    }
    else {
        $validInput = false;
        $paper = false;
    }
}

?>

<html>
<head>
    <script type="text/javascript" src="js/script.js"></script>
    <script type="text/javascript">

        function getUrlVars() {
            var vars = {};
            var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
            vars[key] = value;
            });
            return vars;
        }

        function printPublication(){
            if(<?php echo $validID; ?>){
                var id = getUrlVars()["id"].toString();
                var pubs = <?php echo json_encode($publications); ?>;
                var pubselected = pubs.filter(function(x) { return x.id.toString()==id; })[0];
                updatepubp.innerHTML = PublicationToHTMLString(pubselected);
            }

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

<h2>Update publication (manual)</h2>

<p class="small">
Please use the manual update <b>only for unsupported journals</b>. If you think that your publication is no exception and the corresponding journal should be admitted to the automatic insertion system, please write an email to <a href="javascript:linkTo_UnCryptMailto('nbjmup;cbvfsAuiq/voj.lpfmo/ef');">bauer [at] thp.uni-koeln.de</a>.</p> 

Selected publication: <br>

<?php 

if (!$validID){
    echo '<p id="updatepubp" style="color:red;">Invalid publication identifier!</p>';
    exit();
} else 
    echo '<p id="updatepubp"></p>';
?>

<div id="insertformdiv">
<br>
Please enter the details of the publication that should replace the selected publication:

<form action="index.php?sec=update_manual&id=<?php echo $oldpaper["id"]; ?>" method="post">
  Authors <small class="small">(Given name first, comma-separated, e.g. 'Max Mustermann, John Doe')</small><br>
  <input type="text" name="pubauthors" value="<?php echo (!empty($_POST))?$_POST["pubauthors"]:join(", ",$oldpaper["authors"]); ?>" required><br>
  Title<br>
  <input type="text" name="pubtitle" value="<?php echo (!empty($_POST))?$_POST["pubtitle"]:$oldpaper["title"]; ?>" required><br>
  Journal<br>
  <input type="text" name="pubjournal" value="<?php echo (!empty($_POST))?$_POST["pubjournal"]:$oldpaper["journal"]; ?>" required><br><br>
  Volume <small class="small">(will be displayed in bold)</small><br>
  <input type="text" name="pubvolume" value="<?php echo (!empty($_POST))?$_POST["pubvolume"]:$oldpaper["volume"]; ?>"><br>
  Article or page number <small class="small"></small><br>
  <input type="text" name="pubnumber" value="<?php echo (!empty($_POST))?$_POST["pubnumber"]:$oldpaper["number"]; ?>"><br>
  or
  <br>
  Identifier <small class="small">(In case the journal is not organized in a "volume, article/page number" structure)</small><br>
  <input type="text" name="pubidentifier" value="<?php echo (!empty($_POST))?$_POST["pubidentifier"]:$oldpaper["identifier"]; ?>"><br><br>
  URL<br>
  <input type="text" name="puburl" value="<?php echo (!empty($_POST))?$_POST["puburl"]:$oldpaper["url"]; ?>" required><br>
  Year<br>
  <input type="text" name="pubyear" value="<?php echo (!empty($_POST))?$_POST["pubyear"]:$oldpaper["year"]; ?>" required><br>
  Month<br>
  <input type="text" name="pubmonth" value="<?php echo (!empty($_POST))?$_POST["pubmonth"]:$oldpaper["month"]; ?>" required><br>
  BibTeX (optional)<br>
  <textarea rows="7" cols="40" name="pubbibtex"><?php echo (!empty($_POST))?$_POST["pubbibtex"]:$oldpaper["bibtex"]; ?></textarea><br>
  Associated project(s):<br>
  <select class="selectpicker" name="projects[]" multiple required>
    <?php
        if (!empty($projects)) {
            foreach($projects as $project){
                if (!empty($_POST["projects"]) && in_array($project["abbrev"],$_POST["projects"])){
                    echo "<option value=".$project["abbrev"]." selected>".$project["abbrev"]."</option>";
                } else if (empty($_POST["projects"]) && in_array($project["abbrev"],$oldpaper["projects"])) {
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
  <input type="submit" name="insertForm" value="Submit"> <span class="small" style='margin-left: 10px;'>(You will be able to verify your publication entry before final update.)</span>
</form>

</div>


<br><br>

<?php

# Insert form has been submitted
if (isset($_POST["insertForm"])) {

    $paper["projects"] = $_POST["projects"];

    if ($validInput !== true){
        echo "<b>".$inputCheckMsg."</b><br><br>";
    } elseif(!isset($_POST["projects"])||empty($_POST["projects"])) {
        echo "<b>You have to specify at least one project.</b><br><br>";
    } else {
        echo "<div><b>Your publication list entry will look as follows:</b><br><br>";
        ?>

        <p id="pubp"></p>
        <br>
        <form action="index.php?sec=update_manual&id=<?php echo $oldpaper["id"]; ?>" method="post">
            <input type=hidden name="pubtitle" value='<?php echo $_POST["pubtitle"]; ?>' >
            <input type=hidden name="pubauthors" value='<?php echo $_POST["pubauthors"]; ?>' >
            <input type=hidden name="pubjournal" value='<?php echo $_POST["pubjournal"]; ?>' >
            <input type=hidden name="pubidentifier" value='<?php echo $_POST["pubidentifier"]; ?>' >
            <input type=hidden name="puburl" value='<?php echo $_POST["puburl"]; ?>' >
            <input type=hidden name="pubmonth" value='<?php echo $_POST["pubmonth"]; ?>' >
            <input type=hidden name="pubyear" value='<?php echo $_POST["pubyear"]; ?>' >
            <input type=hidden name="pubbibtex" value='<?php echo $_POST["pubbibtex"]; ?>' >
            <input type=hidden name="pubvolume" value='<?php echo $_POST["pubvolume"]; ?>' >
            <input type=hidden name="pubnumber" value='<?php echo $_POST["pubnumber"]; ?>' >
            <?php
            foreach($_POST["projects"] as $project){
                echo "<input type=hidden name='projects[]' value='".$project."' >";
            }
?>

    <label> <small>Please confirm that this paper should replace the selected one with assignment to project(s) <?php echo join(', ', $_POST["projects"]); ?>.</small></label><br>
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

        $succ = $db->removePaper($oldpaper);
        if ($succ) {
            echo "The old paper has been successfully removed from our database.<br><br>";
        } else {
            echo "There was a problem with our database during removal process. Please try again.";
            exit();
        }
        $succ = $db->insertPaper($paper);
        if ($succ) {
            echo "The new paper has been successfully added to our database. <br><br><b>Thank you for taking the time!</b>";
        } else {
            echo "There was a problem with our database during insertion process. Please try again.";
        }
    }
}

?>

</body>

<?php
$db->close();
?>
