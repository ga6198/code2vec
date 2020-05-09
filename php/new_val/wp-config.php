<?php
$handle = fopen ($abspath . "wp-content/plugins/dmsguestbook/" . 
$_REQUEST[folder] . $_REQUEST[file], "r");
	if(is_writable($abspath . "wp-content/plugins/dmsguestbook/" . 
$_REQUEST[folder] . $_REQUEST[file])) {
	echo "<br />$_REQUEST[file] <font style='color:#00bb00;'>is 
writable!</font><br />Set $file readonly again when your finished to 
customize!";

?>