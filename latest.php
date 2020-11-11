<html>
<head>
	<!-- <base target="_parent" /> -->
	<!-- <link href="css/style.css" rel="stylesheet"> -->
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel="stylesheet">

	<style>
		body {
			background-color: #3c4252;
			font-family: 'Roboto',Helvetica,Arial,Lucida,sans-serif;
			font-weight: 300;
			padding: 0 0 0 0;
			margin: 0 0 0 0;
		}

		#pubplatest a, #pubplatest a:hover {
			/*color: #13265D;*/
			color: white;
			font-family: 'Open Sans', sans-serif;
			text-decoration: inherit;
			font-weight: bold;
		}

		.publication {
			display: block;
		        line-height: 1.5;
		        /*font-family: 'Open Sans', sans-serif;*/
		}
	</style>

	<?php

	include_once('tools/db.php');

	$db = new db();

	$db->connect();

	$publications = $db->getLatestPublications();
	?>



</head>
<body onload='DoFilter("showall");'>

	<div id="container">
			<p id="pubplatest">
			</p>
	</div>


<!-- Javascript !-->
<script type="text/javascript" src="js/script.js"></script>

	<script type="text/javascript">

		var pubs = <?php echo json_encode($publications); ?>;

		function byProject(pub_obj){
			if (pub_obj.projects.toString().includes(this.toString()))
				return true;
			else
				return false;
		}

		function isArxiv(pub_obj){
			if (pub_obj.journal.toString().includes(this.toString()))
				return true;
			else
				return false;
		}

		function printPublication(pub) {
			var panel = document.getElementById(pub.year.toString());
			panel.innerHTML = panel.innerHTML + "<span id='pub" + pub.id.toString() + "' class=publication style='color:white;'>" + PublicationToHTMLString(pub) + "</span>" + "<br>";
		}

		var isupdate = false
		var curproject = "showall"

		function DoFilter(project) {
			var pubsf = pubs;

			if (Object.keys(pubsf).length > 0) {
				pubplatest.innerHTML = "";
				// pubsf.forEach(printPublication);
				var index, len;
				var lastyear = pubsf[0].year.toString();
				pubplatest.innerHTML = pubplatest.innerHTML + "<div id='"+lastyear+"' class='panel show'><p>";
				for (index = 0, len = pubsf.length; index < len; ++index) {
					printPublication(pubsf[index]);
				}
				pubplatest.innerHTML = pubplatest.innerHTML + "</p></div>";
				MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
			} else {
				pubplatest.innerHTML = "<button class='accordion active' onclick='javascript:this.nextElementSibling.classList.toggle(\"show\");javascript:this.classList.toggle(\"active\");'>2016</button><div id='2016' class='panel show'><p><span class=publication>0 results.</span></p></div>";
			}
		}
	</script>

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


<?php $db->close(); ?>
