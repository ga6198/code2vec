<?php
// $Header: /cvsroot/tikiwiki/tiki/tiki-assignpermission.php,v 1.9.4.3 2004/03/31 10:53:27 damosoft Exp $

// Copyright (c) 2002-2004, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

// This script is used to assign groups to a particular user
// ASSIGN USER TO GROUPS
// Initialization
require_once('tiki-setup.php');

if($user != 'admin') {
  if($tiki_p_admin != 'y') {
    $smarty->assign('msg',tra("You dont have permission to use this feature"));
    $smarty->display("styles/$style_base/error.tpl");
    die;
  }
}


if(!isset($_REQUEST["group"])) {
  $smarty->assign('msg',tra("Unknown group"));
  $smarty->display("styles/$style_base/error.tpl");
  die; 
}
$group=$_REQUEST["group"];
if(!$userlib->group_exists($group)) {
  $smarty->assign('msg',tra("Group doesnt exist"));
  $smarty->display("styles/$style_base/error.tpl");
  die;  
}
$smarty->assign_by_ref('group',$group);


if(isset($_REQUEST['allper'])) {
	check_ticket('admin-perms');
  if($_REQUEST['oper']=='assign') {
    $userlib->assign_level_permissions($group,$_REQUEST['level']);
  } else {
    $userlib->remove_level_permissions($group,$_REQUEST['level']);
  }
}


if(isset($_REQUEST["action"])) {
  if($_REQUEST["action"]=='assign') {
    $userlib->assign_permission_to_group($_REQUEST["perm"],$group);  
  }  
  if($_REQUEST["action"]=='remove') {
	$area = 'delpermassign';
	if (isset($_POST['daconfirm']) and isset($_SESSION["ticket_$area"])) {
                key_check($area);
		$userlib->remove_permission_from_group($_REQUEST["permission"],$group); 
	} else {
		key_get($area);
	}
  }
}

$group_info = $userlib->get_group_info($group);
$smarty->assign_by_ref('group_info',$group_info);

if(!isset($_REQUEST["sort_mode"])) {
  $sort_mode = 'type_asc'; 
} else {
  $sort_mode = $_REQUEST["sort_mode"];
} 
$smarty->assign_by_ref('sort_mode',$sort_mode);

$maxRecords=9999;
// If offset is set use it if not then use offset =0
// use the maxRecords php variable to set the limit
// if sortMode is not set then use lastModif_desc
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

if(!isset($_REQUEST["type"])) {
  $_REQUEST["type"]='tiki';
} 
$smarty->assign('type',$_REQUEST["type"]);

if(isset($_REQUEST["createlevel"])) {
	check_ticket('admin-perms');
  $userlib->create_dummy_level($_REQUEST['level']);
}

if(isset($_REQUEST['update'])) {
 foreach(array_keys($_REQUEST['permName']) as $per) {
    $userlib->change_permission_level($per,$_REQUEST['level'][$per]);
    if(isset($_REQUEST['perm'][$per])) {
      $userlib->assign_permission_to_group($per,$group);  
    } else {
      $userlib->remove_permission_from_group($per,$group); 
    }
 }
}

$levels = $userlib->get_permission_levels();
$smarty->assign('levels',$levels);

$perms = $userlib->get_permissions($offset,$maxRecords,$sort_mode,$find,$_REQUEST["type"],$group);
$cant_pages = ceil($perms["cant"] / $maxRecords);
$smarty->assign_by_ref('cant_pages',$cant_pages);
$smarty->assign('actual_page',1+($offset/$maxRecords));
if($perms["cant"] > ($offset+$maxRecords)) {
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

// Get users (list of users)
$smarty->assign_by_ref('perms',$perms["data"]);

ask_ticket('admin-perms');

// Display the template
$smarty->assign('mid','tiki-assignpermission.tpl');
$smarty->display("styles/$style_base/tiki.tpl");
?>
