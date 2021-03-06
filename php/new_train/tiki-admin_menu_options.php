<?php
// $Header: /cvsroot/tikiwiki/tiki/tiki-admin_menu_options.php,v 1.6.4.3 2004/03/31 10:53:27 damosoft Exp $

// Copyright (c) 2002-2004, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

// Initialization
require_once('tiki-setup.php');
include_once('lib/menubuilder/menulib.php');

if($tiki_p_admin != 'y') {
    $smarty->assign('msg',tra("You dont have permission to use this feature"));
    $smarty->display("styles/$style_base/error.tpl");
    die;
}

if(!isset($_REQUEST["menuId"])) {
    $smarty->assign('msg',tra("No menu indicated"));
    $smarty->display("styles/$style_base/error.tpl");
    die;
}

$maxPos = $menulib->get_max_option($_REQUEST["menuId"]);

$smarty->assign('menuId',$_REQUEST["menuId"]);
$menu_info = $tikilib->get_menu($_REQUEST["menuId"]);
$smarty->assign('menu_info',$menu_info);

if(!isset($_REQUEST["optionId"])) {
    $_REQUEST["optionId"]=0;
}
$smarty->assign('optionId',$_REQUEST["optionId"]);


if($_REQUEST["optionId"]) {
  $info = $menulib->get_menu_option($_REQUEST["optionId"]);
} else {
  $info = Array();
  $info["name"]='';
  $info["url"]='';
  $info["type"]='o';
  $info["position"]=$maxPos+1;
}
$smarty->assign('name',$info["name"]);
$smarty->assign('url',$info["url"]);
$smarty->assign('type',$info["type"]);
$smarty->assign('position',$info["position"]);

if(isset($_REQUEST["remove"])) {
	$area = 'delmenuoption';
	if (isset($_POST['daconfirm']) and isset($_SESSION["ticket_$area"])) {
                key_check($area);
		$menulib->remove_menu_option($_REQUEST["remove"]);
		$maxPos = $menulib->get_max_option($_REQUEST["menuId"]);
		$smarty->assign('position',$maxPos+1);
	} else {
		key_get($area);
	}
}

if(isset($_REQUEST["save"])) {
	check_ticket('admin-menu-options');
   $menulib->replace_menu_option($_REQUEST["menuId"], $_REQUEST["optionId"], $_REQUEST["name"], $_REQUEST["url"], $_REQUEST["type"],$_REQUEST["position"]);
   $smarty->assign('position',$_REQUEST["position"]+1);
   $smarty->assign('name','');
   $smarty->assign('optionId',0);
   $smarty->assign('url','');
   $smarty->assign('type','o');
}

if(!isset($_REQUEST["sort_mode"])) {
  $sort_mode = 'position_asc'; 
} else {
  $sort_mode = $_REQUEST["sort_mode"];
} 

if(!isset($_REQUEST["offset"])) {
  $offset = 0;
} else {
  $offset = $_REQUEST["offset"]; 
}
$smarty->assign_by_ref('offset',$offset);

if(isset($_REQUEST["find"])) {
  $find = $_REQUEST["find"];  
} else {
  $find = ''; 
}
$smarty->assign('find',$find);

$smarty->assign_by_ref('sort_mode',$sort_mode);
$channels = $menulib->list_menu_options($_REQUEST["menuId"],0,-1,$sort_mode,$find);
$cant_pages = ceil($channels["cant"] / $maxRecords);
$smarty->assign_by_ref('cant_pages',$cant_pages);
$smarty->assign('actual_page',1+($offset/$maxRecords));
if($channels["cant"] > ($offset+$maxRecords)) {
  $smarty->assign('next_offset',$offset + $maxRecords);
} else {
  $smarty->assign('next_offset',-1); 
}
// If offset is > 0 then prev_offset
if($offset>0) {
  $smarty->assign('prev_offset',$offset - $maxRecords);  
} else {
  $smarty->assign('prev_offset',-1); 
}

$smarty->assign_by_ref('channels',$channels["data"]);

ask_ticket('admin-menu-options');

// Display the template
$smarty->assign('mid','tiki-admin_menu_options.tpl');
$smarty->display("styles/$style_base/tiki.tpl");
?>
