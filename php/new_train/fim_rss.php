<?php
$cat = $wpdb->get_row("SELECT * FROM $cats WHERE id = $_GET[album]");
$images = $wpdb->get_results("SELECT * FROM $imgs WHERE cat = $_GET[album] AND status = 'include'");
?>