<?php
// $Header: /cvsroot/tikiwiki/tiki/tiki-admin_chart_items.php,v 1.3.2.2 2004/03/28 09:59:50 damosoft Exp $

// Copyright (c) 2002-2004, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

require_once('tiki-setup.php');
include_once('lib/charts/chartlib.php');


if($feature_charts != 'y') {
  $smarty->assign('msg',tra("This feature is disabled"));
  $smarty->display("styles/$style_base/error.tpl");
  die;  
}

if($tiki_p_admin_charts != 'y') {
  $smarty->assign('msg',tra("Permission denied"));
  $smarty->display("styles/$style_base/error.tpl");
  die;  
}

if(!isset($_REQUEST['chartId'])) {
  $smarty->assign('msg',tra("No chart indicated"));
  $smarty->display("styles/$style_base/error.tpl");
  die;  

}

if(!isset($_REQUEST['itemId'])) $_REQUEST['itemId'] = 0;
if($_REQUEST["itemId"]) {
  $info = $chartlib->get_chart_item($_REQUEST["itemId"]);
} else {
  $info = Array(
    'title' => '',
    'description' => '',
    'created' => 0,
    'URL' => '',
    'votes' => 0,
    'points' => 0,
    'average' => 0
  );
}

$smarty->assign_by_ref('item_info',$info);
$smarty->assign('chartId',$_REQUEST['chartId']);
$smarty->assign('itemId',$_REQUEST['itemId']);
$smarty->assign('info',$info);
if(isset($_REQUEST["delete"])) {
	check_ticket('admin-chart-items');
  foreach(array_keys($_REQUEST["item"]) as $item) {      	
    $chartlib->remove_chart_item($item);
  }
}



if(isset($_REQUEST['save'])) {
	check_ticket('admin-chart-items');
    $vars = Array();
    $vars['chartId']=$_REQUEST['chartId'];
    $_REQUEST['created']=date("U");
	foreach(array_keys($info) as $key) {
	  if(isset($_REQUEST[$key])) {
	  $vars[$key] = $_REQUEST[$key];
	  }
	}
	$itemId = $chartlib->replace_chart_item($_REQUEST['itemId'],$vars);
  $info = Array(
    'title' => '',
    'description' => '',
    'created' => 0,
    'URL' => '',
    'votes' => 0,
    'points' => 0,
    'average' => 0
  );
  $_REQUEST['itemId']=0;
  $smarty->assign('itemId',0);
  $smarty->assign('info',$info);
}

$where = 'chartId='.$_REQUEST['chartId']; 

if(!isset($_REQUEST["sort_mode"])) {  $sort_mode = 'created_desc'; } else {  $sort_mode = $_REQUEST["sort_mode"];} 
if(!isset($_REQUEST["offset"])) {  $offset = 0;} else {  $offset = $_REQUEST["offset"]; }$smarty->assign_by_ref('offset',$offset);
if(isset($_REQUEST["find"])) { $find = $_REQUEST["find"];  } else {  $find = ''; } $smarty->assign('find',$find);
$smarty->assign('where',$where); $smarty->assign_by_ref('sort_mode',$sort_mode);
$items = $chartlib->list_chart_items($offset,$maxRecords,$sort_mode,$find,$where);
$smarty->assign('cant',$items['cant']);

$cant_pages = ceil($items["cant"] / $maxRecords);$smarty->assign_by_ref('cant_pages',$cant_pages);$smarty->assign('actual_page',1+($offset/$maxRecords));
if($items["cant"] > ($offset+$maxRecords)) {  $smarty->assign('next_offset',$offset + $maxRecords);} else {  $smarty->assign('next_offset',-1); }
if($offset>0) {  $smarty->assign('prev_offset',$offset - $maxRecords);  } else {  $smarty->assign('prev_offset',-1); }
$smarty->assign_by_ref('items',$items["data"]);

$sameurl_elements = Array('offset','sort_mode','where','find','chartId','itemId');

ask_ticket('admin-chart-items');

$smarty->assign('mid','tiki-admin_chart_items.tpl');
$smarty->display("styles/$style_base/tiki.tpl");
?> 
