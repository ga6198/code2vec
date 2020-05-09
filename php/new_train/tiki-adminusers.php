<?php
// $Header: /cvsroot/tikiwiki/tiki/tiki-adminusers.php,v 1.8.2.4 2004/03/30 20:37:29 damosoft Exp $

// Copyright (c) 2002-2004, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

// Initialization
require_once('tiki-setup.php');

function discardUser($u,$reason) {
	$u['reason'] = $reason;
	return $u;
}

function batchImportUsers() {
global $userlib, $smarty, $style_base;
	$fname = $_FILES['csvlist']['tmp_name'];
  	$fhandle = fopen($fname,"r");
  	
  	//Get the field names
  	$fields = fgetcsv($fhandle,1000);
  	//any?
  	if (!$fields[0]) {
	    $smarty->assign('msg',tra("The file is not a CSV file or has not a correct syntax"));
	    $smarty->display("styles/$style_base/error.tpl");
	    die;
  	}
  	//now load the users in a table
  	while (!feof($fhandle)) {
  		$data = fgetcsv($fhandle,1000);
  		for ($i=0;$i<count($fields);$i++) {
  			@$ar[$fields[$i]] = $data[$i];
  		}
  		$userrecs[] = $ar;
  	}
  	fclose($fhandle);
  	// any?
    if (!is_array($userrecs)) {
      $smarty->assign('msg',tra("No records were found. Check the file please!"));
      $smarty->display("styles/$style_base/error.tpl");
      die;
    }
    // Process user array
    $added = 0;
    foreach ($userrecs as $u) {
    	if (@!$u['login']) {
    		$discarded[] = discardUser($u,tra("User login is required"));
    	}
    	elseif (@!$u['password']) {
    		$discarded[] = discardUser($u,tra("Password is required"));
    	}
    	elseif (@!$u['email']) {
    		$discarded[] = discardUser($u,tra("Email is required"));
    	}
    	elseif ($userlib->user_exists($u['login']) and (!$_REQUEST['overwrite'])) {
    		$discarded[] = discardUser($u,tra("User is duplicated"));
    	}
    	else {
    		if (!$userlib->user_exists($u['login'])) {
    			$userlib->add_user($u['login'],$u['password'],$u['email']);
    		}
    		$userlib->set_user_fields($u);
    		if (@$u['groups']) {
    			$grps = explode(",",$u['groups']);
    			foreach ($grps as $grp) {
    				if ($userlib->group_exists($grp)) {
    					$userlib->assign_user_to_group($u['login'],$grp);
    				}
    			}
    		}
    		$added++;
    	}
    }
    $smarty->assign('added',$added);
    if (@is_array($discarded)) { $smarty->assign('discarded',count($discarded)); }
   	@$smarty->assign('discardlist',$discarded);
}


if($user != 'admin') {
  if($tiki_p_admin != 'y') {
    $smarty->assign('msg',tra("You dont have permission to use this feature"));
    $smarty->display("styles/$style_base/error.tpl");
    die;
  }
}

// Process the form to add a user here
if(isset($_REQUEST["newuser"])) {
	check_ticket('admin-users');
  // if no user data entered, check if it's a batch upload  
  if ((!$_REQUEST["name"]) and (is_uploaded_file($_FILES['csvlist']['tmp_name']))) {  	
  	batchImportUsers();
  }
  else {
	  // Check if the user already exists
	  if($_REQUEST["pass"] != $_REQUEST["pass2"]) {
	    $smarty->assign('msg',tra("The passwords dont match"));
	    $smarty->display("styles/$style_base/error.tpl");
	    die;
	  } else {
	    if($userlib->user_exists($_REQUEST["name"])) {
	      $smarty->assign('msg',tra("User already exists"));
	      $smarty->display("styles/$style_base/error.tpl");
	      die;
	    } else {
	      $userlib->add_user($_REQUEST["name"],$_REQUEST["pass"],$_REQUEST["email"]);
	    }
	  }
  }
}

// Process actions here
// Remove user or remove user from group
if(isset($_REQUEST["action"])) {
  if($_REQUEST["action"]=='delete') {
	$area = 'deluser';
	if (isset($_POST['daconfirm']) and isset($_SESSION["ticket_$area"])) {
                key_check($area);
		$userlib->remove_user($_REQUEST["user"]); 
	} else {
		key_get($area);
	}
  } 
  if($_REQUEST["action"]=='removegroup') {
	$area = 'deluserfromgroup';
	if (isset($_POST['daconfirm']) and isset($_SESSION["ticket_$area"])) {
                key_check($area);
		$userlib->remove_user_from_group($_REQUEST["user"],$_REQUEST["group"]); 
	} else {
		key_get($area);
	}
  }
}

if(!isset($_REQUEST["sort_mode"])) {
  $sort_mode = 'login_desc'; 
} else {
  $sort_mode = $_REQUEST["sort_mode"];
} 
$smarty->assign_by_ref('sort_mode',$sort_mode);

// If offset is set use it if not then use offset =0
// use the maxRecords php variable to set the limit
// if sortMode is not set then use lastModif_desc
if(!isset($_REQUEST["numrows"])) {
	$numrows = $maxRecords;
} else {
	$numrows = $_REQUEST["numrows"];
}
$smarty->assign_by_ref('numrows',$numrows);

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

$users = $userlib->get_users($offset,$numrows,$sort_mode,$find);
$cant_pages = ceil($users["cant"] / $numrows);
$smarty->assign_by_ref('cant_pages',$cant_pages);
$smarty->assign('actual_page',1+($offset/$numrows));
if($users["cant"] > ($offset+$numrows)) {
  $smarty->assign('next_offset',$offset + $numrows);
} else {
  $smarty->assign('next_offset',-1); 
}
// If offset is > 0 then prev_offset
if($offset>0) {
  $smarty->assign('prev_offset',$offset - $numrows);  
} else {
  $smarty->assign('prev_offset',-1); 
}



// Get users (list of users)
$smarty->assign_by_ref('users',$users["data"]);

ask_ticket('admin-users');

// Display the template
$smarty->assign('mid','tiki-adminusers.tpl');
$smarty->display("styles/$style_base/tiki.tpl");
?>
