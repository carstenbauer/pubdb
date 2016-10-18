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


function checkAPS($pubidstr){
    $apsarray = array("PRL","PRB","PRE", "PRA", "PRC", "PRD", "RMP", "Rev", "Physical", "10.1103/");
    if (contains($pubidstr,$apsarray))
        return True;
    
    return False;
}

function checkNature($pubidstr){
    $naturearray = array("Nature", "10.1038/", "ncomms", "nphys", "Nat");
    if (contains($pubidstr,$naturearray))
        return True;
    
    return False;

}

function identifierToPaper($pubidstr){
    # Check if we have arxiv or aps identifier
    # TODO Make "waterproof"!
    if (checkAPS($pubidstr)){
        // Assume aps identifier
        $paper = apsParser::parse($pubidstr);
    } elseif (checkNature($pubidstr)) {
        $paper = natureParser::parse($pubidstr);
    } else {
        // Assume arxiv
        $paper = arxivParser::parse($pubidstr);
    }

    return isset($paper)?$paper:False;
}

function generateArXivBibTeX($paper){
    // $arr = explode("/", $paper["authors"], 2);
    // $first = $arr[0];
    $firstauthor = $paper["authors"][0];
    $names = explode(' ', $firstauthor);
    $lastname = end($names);
    $authorstring = join(' and ',$paper["authors"]);

    return "@article{".$lastname.$paper["year"].",
  title                    = {{".$paper["title"]."}},
  author                = {".$authorstring."},
  eprint                 = {arXiv:".$paper["identifier"]."},
  year                 = {".$paper["year"]."}
}";
// archivePrefix = \"arXiv\"
}

if (isset($_POST["delete"]) || isset($_POST["confirmdelete"]))
    $deleteMode = true;
else
    $deleteMode = false;

if (!$deleteMode && !empty($_POST) && isset($_POST["pubidstr"]))
    $publ = identifierToPaper($_POST["pubidstr"]);
else
    $publ = False;

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

?>

<html>
<head>

    <link href="css/style.css" rel="stylesheet">

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
                updatepubp.innerHTML = updatepubp.innerHTML + "<span id=deletespan><a href='javascript:document.forms[\"deleteform\"].submit();'>[Delete]</a></span>";
            }
            var pub = <?php echo ($publ===False)?"\"none\"":json_encode($publ); ?>;
            if (<?php echo $deleteMode?"true":"false"; ?>) {
                pubp.innerHTML = PublicationToHTMLString(pubselected);
            } else if (pub != "none") {
                var pubstr = PublicationToHTMLString(pub);
                pubp.innerHTML = pubstr;
                // foundbox.style.display = 'inline';
            }
        }
    </script>
</head>

<body onload='printPublication();'>

<h2>Update publication</h2>

Selected publication: <br>

<?php 

if (!$validID){
    echo '<p id="updatepubp" style="color:red;">Invalid publication identifier!</p>';
    exit();
} else 
    echo '<p id="updatepubp"></p>';
?>

<div id="insertformdiv" <?php echo $deleteMode?"hidden":""; ?>>
<br>
Please specify a newer version of the publication:
<p class="small"><b>Automatic lookup</b> supported for arXiv, APS journals, Nature journals. Elsewise, perform a <b><a href='index.php?sec=update_manual&id=<?php echo $oldpaper["id"]; ?>'>manual update</a></b>.</p>

<form action="index.php?sec=update&id=<?php echo $oldpaper["id"]; ?>" method="post">
  Publication Identifier:<br>
  <input type="text" name="pubidstr" value="<?php echo (!empty($_POST))?$_POST["pubidstr"]:""; ?>" required><br><br>
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
  <input type="submit" name="insertForm" value="Submit">
</form>
</div>

<form id="deleteform" action="index.php?sec=update&id=<?php echo $oldpaper["id"]; ?>" method="post">
    <input type=hidden name="delete" value="delete">
</form>

<br><br>

<?php

# Delete paper has been requested
if ($deleteMode && !isset($_POST["confirmdelete"])) {

    echo "<div><b>Do you really want to <span style='color: red;'>delete</span> the following publication entry:</b><br><br>";
?>

        <p id="pubp"></p>
        <br>
        <form action="index.php?sec=update&id=<?php echo $oldpaper["id"]; ?>" method="post">

    <label> <small>Please confirm that this entry should be deleted.</small></label><br>
    Password: <input type="password" name="pw" >

            <input type="submit" name="confirmdelete" value="Confirm">
        </form>
        </div>
        <?php
} else if ($deleteMode && isset($_POST["confirmdelete"]) && $_POST["pw"]==INSERTPASSWORD) {
    $succ = $db->removePaper($oldpaper);
    if ($succ) {
        echo "The selected paper has been successfully removed from our database.<br><br>";
    } else {
        echo "There was a problem with our database during removal process. Please try again.";
        exit();
    }
} else if ($deleteMode && isset($_POST["confirmdelete"])) 
    echo "<b>Oops, the password is not correct!</b>";

?>

<?php

# Insert form has been submitted
if (isset($_POST["insertForm"])) {

    $paper = identifierToPaper($_POST["pubidstr"]);
    $paper["projects"] = $oldpaper["projects"];

    if ($paper === false || $paper["title"]==""){
        echo "<b>No paper is matching the given identifier.</b><br><br>";
    } else {
        echo "<div><b>We found the following paper:</b><br><br>";
        ?>

        <p id="pubp"></p>
        <br>
        <form action="index.php?sec=update&id=<?php echo $oldpaper["id"]; ?>" method="post">
            <input type=hidden name="pubidstr" value="<?php echo $_POST["pubidstr"]; ?>" >
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

    $paper = identifierToPaper($_POST["pubidstr"]);
    $paper['projects'] = $_POST['projects'];

    if ($paper === false || $paper["title"]==""){
        echo "<b>No paper is matching the given identifier.</b><br><br>";
    } elseif ($_POST["pw"]!==INSERTPASSWORD) {
        echo "<b>Oops, the password is not correct!</b>";
    } else {
        $succ = $db->removePaper($oldpaper);
        if ($succ) {
            echo "The old paper has been successfully removed from our database.<br><br>";
        } else {
            echo "There was a problem with our database during removal process. Please try again.";
            exit();
        }

        if($paper["journal"]=="arxiv"){
            $paper["bibtex"] = generateArXivBibTeX($paper);
        }
        $succ = $db->insertPaper($paper);
        if ($succ) {
            echo "The paper has been successfully added to our database. <br><br><b>Thank you for taking the time!</b>";
        } else {
            echo "There was a problem with our database during insertion process. Please try again.";
        }
    }
}

?>


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

<?php
$db->close();
?>
