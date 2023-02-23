
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    <title>PUBDB</title>

    <!-- Bootstrap core CSS -->
<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.4/css/bootstrap.min.css" integrity="sha384-2hfp1SzUoho7/TsGGGDaFdsuuDL0LX2hnUp6VkX3CUQ2K4K+xjboZdsXyp4oUHZj" crossorigin="anonymous"> --!>

<link href="/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/bootstrap-select/1.11.2/css/bootstrap-select.min.css">

<script src="js/jquery-3.0.0.min.js"></script>
<script src="/tether/1.2.0/js/tether.min.js"></script>
<!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.4/js/bootstrap.min.js" integrity="sha384-VjEeINv9OSwtWFLAtmc4JCtEJXXBub00gtSnszmspDLCtC0I4z4nqz7rEFbIZLLU" crossorigin="anonymous"></script> --!>
<script src="/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="/bootstrap-select/1.11.2//js/bootstrap-select.min.js"></script>

  </head>

  <body>


    <div class="mycontainer">
     
        <?php
            if(isset($_GET["sec"])) {
                switch($_GET["sec"]) {
                    case "show": include("show.php"); break;
                    case "insert": include("insert.php"); break;
                    case "insert_manual": include("insert_manual.php"); break;
                    case "update": include("update.php"); break;
                    case "update_manual": include("update_manual.php"); break;
                    default: include("show.php"); break;
                }
            } else {
                include("show.php");
            }
            


        ?>
      
    </div>


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <!-- <script src="../../assets/js/ie10-viewport-bug-workaround.js"></script> --!>

  </body>
</html>

