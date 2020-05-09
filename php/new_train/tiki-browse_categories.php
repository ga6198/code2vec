<?php
// $Header: /cvsroot/tikiwiki/tiki/tiki-browse_categories.php,v 1.7.4.7 2004/03/28 09:59:50 damosoft Exp $

// Copyright (c) 2002-2004, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

// Initialization
require_once('tiki-setup.php');
include_once('lib/categories/categlib.php');
include_once('lib/tree/categ_browse_tree.php');


if($feature_categories != 'y') {
  $smarty->assign('msg',tra("This feature is disabled"));
  $smarty->display("styles/$style_base/error.tpl");
  die;
}

if ($tiki_p_view_categories != 'y') {
        $smarty->assign('msg', tra("You dont have permission to use this feature"));
        $smarty->display("styles/$style_base/error.tpl");
        die;
}


// Check for parent category or set to 0 if not present
if(!isset($_REQUEST["parentId"])) {
  $_REQUEST["parentId"]=0;
}
$smarty->assign('parentId',$_REQUEST["parentId"]);

// If the parent category is not zero get the category path
if($_REQUEST["parentId"]) {
  $path = $categlib->get_category_path_browse($_REQUEST["parentId"]);
  $info = $categlib->get_category($_REQUEST["parentId"]);
  $description = $info["description"];
  $father = $info["parentId"];
} else {
  $path = tra("TOP");
  $description = "";
  $father = 0;
}
$smarty->assign('path',$path);
$smarty->assign('father',$father);
$smarty->assign('description',$description);

//$children = $categlib->get_child_categories($_REQUEST["parentId"]);
//$smarty->assign_by_ref('children',$children);

// Convert $childrens
//$debugger->var_dump('$children');
$ctall = $categlib->get_all_categories_ext(); //get_all_categories() is obsolete by now?
$tree_nodes = array();
foreach ($ctall as $c)
{
  $tree_nodes[] = array("id"=>$c["categId"],
                        "parent"=>$c["parentId"],
                        "data"=>'<a class="catname" href="tiki-browse_categories.php?parentId='.$c["categId"].'" title="'.$c["description"].'">'.$c["name"].'</a><br />');

}
//$debugger->var_dump('$tree_nodes');
$tm = new CatBrowseTreeMaker("categ");
$res = $tm->make_tree($_REQUEST["parentId"], $tree_nodes);
$smarty->assign('tree', $res);

if(!isset($_REQUEST["sort_mode"])) {
  $sort_mode = 'name_asc';
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

if(isset($_REQUEST["deep"])&&$_REQUEST["deep"]=='on') {
  $objects = $categlib->list_category_objects_deep($_REQUEST["parentId"],$offset,$maxRecords,$sort_mode,$find);
  $smarty->assign('deep','on');
} else {
  $objects = $categlib->list_category_objects($_REQUEST["parentId"],$offset,$maxRecords,$sort_mode,$find);
  $smarty->assign('deep','off');
}
$smarty->assign_by_ref('objects',$objects["data"]);
$smarty->assign_by_ref('cantobjects',$objects["cant"]);

$cant_pages = ceil($objects["cant2"] / $maxRecords);
$smarty->assign_by_ref('cant_pages',$cant_pages);
$smarty->assign('actual_page',1+($offset/$maxRecords));
if($objects["cant2"] > ($offset+$maxRecords)) {
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

$section='categories';
include_once('tiki-section_options.php');

ask_ticket('browse-categories');

// Display the template
$smarty->assign('mid','tiki-browse_categories.tpl');
$smarty->display("styles/$style_base/tiki.tpl");
?>
