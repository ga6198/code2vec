<?php

if($page==null) $page = $_GET['id'];
 
$sql = "SELECT * FROM page INNER JOIN node ON
node.node_id=page.node_id WHERE node.node_id=$page";

?>
