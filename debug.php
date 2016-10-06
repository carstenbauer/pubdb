<?php

include_once('tools/db.php');

$db = new db();
$db->connect(); 
$paper["id"] = 29;
$db->removePaper($paper);
$db->close();

?>
