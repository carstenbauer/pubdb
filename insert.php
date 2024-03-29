<?php
include_once('config/config.php');
include_once('tools/db.php');
include_once('tools/parser/idToPaper.php');

$db = new db();
$db->connect();
$projects = $db->getProjects();



if (!empty($_POST) && isset($_POST["pubidstr"]))
    $publ = identifierToPaper($_POST["pubidstr"]);
else
    $publ = False;
?>

<html>

<head>

    <link href="css/style.css" rel="stylesheet">
    <link href="Fonts/opensans.css" rel="stylesheet">
    <script type="text/javascript" src="js/script.js"></script>
    <script type="text/javascript">
        function printPublication() {
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

    <h2>Insert publication</h2>

    <p>Use <b>automatic lookup</b> for arXiv, APS journals, Nature journals, SciPost Physics, the Quantum journal or
        insert <b><a href='index.php?sec=insert_manual'>manual entry</a></b>.</p>

    <form action="index.php?sec=insert" method="post">
        Publication identifier:<br>
        <input type="text" name="pubidstr" value="<?php echo (!empty($_POST)) ? $_POST["pubidstr"] : ""; ?>"
            required><br><br>
        Associated project(s):<br>
        <select class="selectpicker" name="projects[]" multiple required>
            <?php
            if (!empty($projects)) {
                foreach ($projects as $project) {
                    if (!empty($_POST["projects"]) && in_array($project["abbrev"], $_POST["projects"])) {
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
        <input type="submit" name="insertForm" value="Submit"> &nbsp; <input type="button" name="abort" value="Abort"
            onClick="window.location='index.php?sec=show';" />
    </form>
    <br>
    <p class="medium"><b>Note</b>: Please only submit manuscripts to the CRC database that explicitly acknowledge
        funding through the CRC by including a sentence of the form "This work was partially supported by the DFG within
        the CRC 183 (project C03).”</p>
    <br><br><br>

    <?php

    # Insert form has been submitted
    if (isset($_POST["insertForm"])) {

        $paper = identifierToPaper($_POST["pubidstr"]);

        if ($paper === false || $paper["title"] == "") {
            echo "<b>No paper is matching the given identifier.</b><br><br>";
        } elseif (!isset($_POST["projects"]) || empty($_POST["projects"])) {
            echo "<b>You have to specify at least one project.</b><br><br>";
        } else {
            echo "<div><b>We found the following paper:</b><br><br>";
            ?>

            <p id="pubp"></p>
            <br>
            <form action="index.php?sec=insert" method="post">
                <input type=hidden name="pubidstr" value="<?php echo $_POST["pubidstr"]; ?>">
                <?php
                foreach ($_POST["projects"] as $project) {
                    echo "<input type=hidden name='projects[]' value='" . htmlspecialchars($project) . "' >";
                }
                ?>
                <br><br>
                Please confirm that this paper should be added to project(s)
                <?php echo htmlspecialchars(join(', ', $_POST["projects"])); ?>.<br><br>
                Password: <input type="password" name="pw">

                &nbsp; <input type="submit" name="confirm" value="Confirm"> &nbsp; <input type="button" name="abort"
                    value="Abort" onClick="window.location='index.php?sec=show';" />
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

        if ($paper === false || $paper["title"] == "") {
            echo "<b>No paper is matching the given identifier.</b><br><br>";
        } elseif (!isset($_POST["projects"]) || empty($_POST["projects"])) {
            echo "<b>You have to specify at least one project.</b><br><br>";
        } elseif ($_POST["pw"] !== INSERTPASSWORD) {
            echo "<b>Oops, the password is not correct!</b>";
        } else {
            $paper["projects"] = $_POST["projects"];

            if ($db->paperExists($paper)) {
                echo "This paper is already in our database.";
                echo "<br><br>";
                echo "<input type=\"button\" name=\"back\" value=\"Back to publications\" onClick=\"window.location='index.php?sec=show';\" />";
            } else {
                $succ = $db->insertPaper($paper);
                if ($succ) {
                    echo "The paper has been successfully added to our database. Thank you for taking the time!";
                    echo "<br><br>";
                    echo "<input type=\"button\" name=\"back\" value=\"Back to publications\" onClick=\"window.location='index.php?sec=show';\" />";
                } else {
                    echo "There was a problem with our database. Please try again.";
                    echo "<br><br>";
                    echo "<input type=\"button\" name=\"back\" value=\"Back to publications\" onClick=\"window.location='index.php?sec=show';\" />";
                }
            }
        }
    }

    ?>


</body>

<?php
$db->close();
?>