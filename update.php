<?php

include_once('config/config.php');
include_once('tools/db.php');
include_once('tools/parser/idToPaper.php');

$db = new db();
$db->connect();
$projects = $db->getProjects();



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
$id = isset($_GET['id']) ? $_GET['id'] : -1;

// print $id in the following line
print $id;


print __LINE__;

foreach ($publications as $p) {
    print __LINE__;
    if ($p["id"] == $id) {
        print __LINE__;
        $validID = true;
        $oldpaper = $p;
    }
}

?>

<html>

<head>

    <link href="css/style.css" rel="stylesheet">
    <link href="Fonts/opensans.css" rel="stylesheet">

    <script type="text/javascript" src="js/script.js"></script>
    <script type="text/javascript">

        function getUrlVars() {
            var vars = {};
            var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
                vars[key] = value;
            });
            return vars;
        }

        function printPublication() {
            if (<?php echo $validID; ?>) {
                var id = getUrlVars()["id"].toString();
                var pubs = <?php echo json_encode($publications); ?>;
                var pubselected = pubs.filter(function (x) { return x.id.toString() == id; })[0];
                updatepubp.innerHTML = PublicationToHTMLString(pubselected);
                updatepubp.innerHTML = updatepubp.innerHTML + "<span id=deletespan><a href='javascript:document.forms[\"deleteform\"].submit();'>[Delete]</a></span>";
            }
            var pub = <?php echo ($publ === False) ? "\"none\"" : json_encode($publ); ?>;
            if (pub != "none") {
                var pubstr = PublicationToHTMLString(pub);
                pubp.innerHTML = pubstr;
                // foundbox.style.display = 'inline';
            }
        }
    </script>
</head>

<body onload='printPublication();'>

    <h2>
        <?php echo $deleteMode ? "Delete publication" : "Update publication"; ?>
    </h2>

    <b>Selected publication:</b> <br><br>

    <?php

    if (!$validID) {
        echo '<p id="updatepubp" style="color:red;">Invalid publication identifier!</p>';
        exit();
    } else
        echo '<p id="updatepubp"></p>';
    ?>

    <div id="insertformdiv" <?php echo $deleteMode ? "hidden" : ""; ?>>
        <br><br>
        <b>Replace by:</b><br><br>

        <p>Use <b>automatic lookup</b> for arXiv, APS journals, Nature journals, the Quantum journal or perform <b><a
                    href='index.php?sec=update_manual&id=<?php echo $oldpaper["id"]; ?>'>manual update</a></b>.</p>

        <form action="index.php?sec=update&id=<?php echo $oldpaper["id"]; ?>" method="post">
            Publication identifier:<br>
            <input type="text" name="pubidstr"
                value="<?php echo (!empty($_POST)) ? htmlspecialchars($_POST["pubidstr"]) : $oldpaper["identifier"]; ?>"
                required><br><br>
            Associated project(s):<br>
            <select class="selectpicker" name="projects[]" multiple required>
                <?php
                if (!empty($projects)) {
                    foreach ($projects as $project) {
                        if (!empty($_POST["projects"]) && in_array($project["abbrev"], $_POST["projects"])) {
                            echo "<option value=" . $project["abbrev"] . " selected>" . $project["abbrev"] . "</option>";
                        } else if (empty($_POST["projects"]) && in_array($project["abbrev"], $oldpaper["projects"])) {
                            echo "<option value=" . $project["abbrev"] . " selected>" . $project["abbrev"] . "</option>";
                        } else {
                            echo "<option value=" . $project["abbrev"] . ">" . $project["abbrev"] . "</option>";
                        }
                    }
                } else {
                    echo "0 results";
                }
                ?>
            </select>
            <br><br>
            <input type="submit" name="insertForm" value="Submit">&nbsp; <input type="button" name="abort" value="Abort"
                onClick="window.location='index.php?sec=show';" />
        </form>
    </div>

    <form id="deleteform" action="index.php?sec=update&id=<?php echo $oldpaper["id"]; ?>" method="post">
        <input type=hidden name="delete" value="delete">
    </form>

    <br><br><br>

    <?php

    # Delete paper has been requested
    if ($deleteMode && !isset($_POST["confirmdelete"])) {

        echo "<div><b>Do you really want to <span style='color: red;'>delete</span> the publication entry above?</b><br><br>";
        ?>

        <form action="index.php?sec=update&id=<?php echo $oldpaper["id"]; ?>" method="post">
            Password: <input type="password" name="pw">

            &nbsp;<input type="submit" name="confirmdelete" value="Confirm">&nbsp; <input type="button" name="abort"
                value="Abort" onClick="window.location='index.php?sec=show';" />
        </form>
        </div>
        <?php
    } else if ($deleteMode && isset($_POST["confirmdelete"]) && $_POST["pw"] == INSERTPASSWORD) {
        $succ = $db->removePaper($oldpaper);
        if ($succ) {
            echo "The selected paper has been successfully removed from our database.";
            echo "<br><br>";
            echo "<input type=\"button\" name=\"back\" value=\"Back to publications\" onClick=\"window.location='index.php?sec=show';\" />";
        } else {
            echo "There was a problem with our database during removal process. Please try again.";
            echo "<br><br>";
            echo "<input type=\"button\" name=\"back\" value=\"Back to publications\" onClick=\"window.location='index.php?sec=show';\" />";
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

        if ($paper === false || $paper["title"] == "") {
            echo "<b>No paper is matching the given identifier.</b><br><br>";
        } else {
            echo "<div><b>We found the following paper for replacement:</b><br><br>";
            ?>

            <p id="pubp"></p>
            <br>
            <form action="index.php?sec=update&id=<?php echo $oldpaper["id"]; ?>" method="post">
                <input type=hidden name="pubidstr" value="<?php echo $_POST["pubidstr"]; ?>">
                <?php
                foreach ($_POST["projects"] as $project) {
                    echo "<input type=hidden name='projects[]' value='" . $project . "' >";
                }
                ?>
                <br>
                Please confirm that this paper should replace the selected one with assignment to project(s)
                <?php echo htmlspecialchars(join(', ', $_POST["projects"])); ?>.<br><br>
                Password: <input type="password" name="pw">

                <input type="submit" name="confirm" value="Confirm"> &nbsp; <input type="button" name="abort" value="Abort"
                    onClick="window.location='index.php?sec=show';" />
            </form>
            </div>
            <?php

        }
    }

    ?>

    <?php

    # Paper has been confirmed for insertion
    if (isset($_POST["confirm"])) {

        $paper = identifierToPaper($_POST["pubidstr"]);
        $paper['projects'] = $_POST['projects'];

        if ($paper === false || $paper["title"] == "") {
            echo "<b>No paper is matching the given identifier.</b><br><br>";
        } elseif ($_POST["pw"] !== INSERTPASSWORD) {
            echo "<b>Oops, the password is not correct!</b>";
        } else {
            if ($db->paperExistsApartFromOldPaper($paper, $oldpaper)) {
                echo "This paper is already in our database.";
                echo "<br><br>";
                echo "<input type=\"button\" name=\"back\" value=\"Back to publications\" onClick=\"window.location='index.php?sec=show';\" />";
            } else {
                $succ = $db->removePaper($oldpaper);
                if (!$succ) {
                    echo "There was a problem with our database during removal process. Please try again.";
                    echo "<br><br>";
                    echo "<input type=\"button\" name=\"back\" value=\"Back to publications\" onClick=\"window.location='index.php?sec=show';\" />";
                    exit();
                }

                $succ = $db->insertPaper($paper);
                if ($succ) {
                    echo "The paper has been successfully updated. Thank you for taking the time!";
                    echo "<br><br>";
                    echo "<input type=\"button\" name=\"back\" value=\"Back to publications\" onClick=\"window.location='index.php?sec=show';\" />";
                } else {
                    echo "There was a problem with our database during insertion process. Please try again.";
                    echo "<br><br>";
                    echo "<input type=\"button\" name=\"back\" value=\"Back to publications\" onClick=\"window.location='index.php?sec=show';\" />";
                }
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
    <script type="text/javascript" async src="//cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-MML-AM_CHTML">
    </script>

</body>

<?php
$db->close();
?>