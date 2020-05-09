<?php

/** 
 *  Addressbook module for drupal 
 *
 *  Copyright (C) 2006-2010
 *  =======================
 *
 *  Created by wplaat
 *
 *  For more information visit the following website.
 *  Website : http://www.plaatsoft.nl 
 *
 *  Or send an email to the following address.
 *  Email   : info@plaatsoft.nl
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, version 2.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
 
// *********************************************************************************
// StateMachine
// *********************************************************************************
 
function addressbook_member($mid=0) {

	//echo var_dump($_POST);
	
	// *****************************
	// Process POST an SESSION variables
	// *****************************
	
	$Action=htmlentities($_POST['Action']);
	if ($mid!=0) {
		$Action="MemberView";
		$_POST["mid"]=$mid;
	}
	addressbook_debug_param("Action", $Action);
		
	// *****************************
	// Process statemachine
	// *****************************
	
	switch ($Action) {
	
		case 'MemberList': 
			$page = addressbook_member_list();
			break;	
							
		case 'MemberEdit': 
			$page = addressbook_member_view(false);
			break;
			
		case 'MemberView': 
			$page = addressbook_member_view(true);
			break;
			
		case 'MemberSave': 
			$error=addressbook_member_save();
			if (!$error) {
				addressbook_save_picture();
				$page = addressbook_member_list();
			} else {
				$page = addressbook_member_view(false);
			}
			break;
			
		case 'MemberRemove':
		    $page = addressbook_member_remove();
			break;
			
		case 'MemberDelete': 
			addressbook_delete_member();
			addressbook_picture_delete();
			$page = addressbook_member_list();
			break;
					
		case 'PictureDelete': 
			addressbook_picture_delete();
			$page = addressbook_member_view(false);
			break;
				
		default:
			$page = addressbook_member_list();
			break;
	}
	
	if ($page!="") {
		print theme("page", $page);
	}
}
			
			
// *********************************************************************************
// Views
// *********************************************************************************

/**
 * Render a page showing the selected address for editing
 * @return HTML
 */
function addressbook_member_list( $sort='' )  {

	// *****************************
	// Get posted variables
	// *****************************

	$Sort = $_POST["Sort"];
	if ($Sort=="") {
		$Sort=$_SESSION["Sort"];
		if ($Sort=="") {
			$Sort="A";
		}
	}
	$_SESSION["Sort"]=$Sort;
	addressbook_debug_param("Sort", $Sort);

	$Search=$_POST["Search"];
	addressbook_debug_param("Search", $Search);
  
	// *****************************
	// Query Database
	// *****************************
	
		
	if ($Search!="") {
		$parameter=$Search;
	} else {
		if ($Sort==t("ALL")) {
			$parameter="";
		} else {
			$parameter=$Sort;
		}
	}
	addressbook_debug_param("parameter", $parameter);

	// Fetch member from database
	$query  = '
		SELECT 
			a.mid,
			a.first_name, 
			a.middle_name, 
			a.last_name, 
			a.mobile, 
			a.email, 
			a.mid, 
			b.fid, 
			a.uid, 
			b.telephone,
			c.picture as picture
		FROM 
			addressbook_family b, addressbook_member a LEFT JOIN addressbook_picture c ON c.mid=a.mid 
		WHERE 
			a.fid=b.fid and
			a.last_name LIKE "'.$parameter.'%" ';
	$query .= 'ORDER BY a.last_name, a.middle_name ';
	
	$queryResult = db_query($query);

	// *****************************
	// View
	// *****************************

	drupal_set_title(t('Addressbook member list'));
  
	$page .= '<form name="AddressbookForm" method="POST" >';	
	$page .= '<div class="addressbook">';

	// Breadcrumb menu
	$node["child1"] = l(t('Member list'),URL_ADDRESSBOOK_MEMBER);
	addressbook_breadcrumb($node);
  
  	$page .= '<fieldset>' ;
	$page .= '<legend>'.t('Filter').'</legend>';
	
	$page .= addressbook_form_item(
					t('Last name:'),
					addressbook_view_text("Search", 40, 40, $search, false).
					' '.addressbook_hiddenlink('AddressbookForm','','','Search',t('Search')),
					t("Enter lastname and press enter to find the member."), 
					$readonly );
					
	$page .= addressbook_sortbar($Sort,"List");
	
	$page .= '</fieldset>';
 
	$page .= '<table>';
	$page .= '<thead>';

	$page .= '<tr>';
		
	if ( variable_get('addressbook_show_thumbnails',0)==1 ) {
		$page .= '<th>';
		$page .= '<b>'.t('Picture').'</b>';
		$page .= '</th>';
	}
	$page .= '<th>';
	$page .= '<b>'.t('Name').'</b>';
	$page .= '</th>';

	$page .= '<th>';
	$page .= '<b>'.t('Telephone').'</b>';
	$page .= '</th>';

	$page .= '<th>';
	$page .= '<b>'.t('Mobile').'</b>';
	$page .= '</th>';

	$page .= '<th>';
	$page .= '<b>'.t('Email').'</b>';
	$page .= '</th>';

	$page .= '</tr>';	
	$page .= '</thead>';
	$page .= '<tbody>';
	
	// Show all found members
	$page_tmp='';
	while ($data = db_fetch_object($queryResult)) {
	
		if ((++$i%2)==0) {
			$page_tmp .= '<tr class="even">';
		} else {
			$page_tmp .= '<tr class="odd">';
		}
		
		if ( variable_get('addressbook_show_thumbnails',0)==1 ) {
			$page_tmp .= '<td>';
			$page_tmp .= addressbook_load_picture($data->picture, 30, 25, "left", false);
			$page_tmp .= '</td>';
		}
		     
		// Format Name
		$page_tmp .= '<td>';
		$name=addressbook_view_name($data->first_name,$data->middle_name,$data->last_name,true);
		$page_tmp .= addressbook_hiddenlink('AddressbookForm', 'mid', $data->mid, 'MemberView', $name);
		$page_tmp .= '</td>';
		
		$page_tmp .= '<td>';
		$page_tmp .= $data->telephone;
		$page_tmp .= '</td>';
		
		$page_tmp .= '<td>';
		$page_tmp .= $data->mobile;
		$page_tmp .= '</td>';
		
		$page_tmp .= '<td>';
		$page_tmp .= l($data->email,'mailto:'.$data->email);
		$page_tmp .= '</td>';
		
		$page_tmp .= '</tr>';
	}

	if ( $page_tmp!='') {
     $page .= $page_tmp;
	} else {
		// No content found
		$page .= '<tr>'; 
		$page .= t('No records found');
		$page .= '</tr>';
  }
  $page .= '</table>';
  
  $page .= '</div>';
  $page .= '</form>'; 
  
  return $page;
}

/**
 * Render a page showing the selected member in readonly mode
 * @return HTML
 */
function addressbook_member_view( $readonly=true )  {

	// *****************************
	// Get posted Inputs
	// *****************************
	
	$mid=$_POST["mid"];
	if ($mid=="") {
		$mid=0;
	}
	addressbook_debug_param("mid", $mid);
	
	$fid=$_POST["fid"];
	if ($fid=="") {
		$fid=$_SESSION["fid"];
	}
	addressbook_debug_param("fid", $fid);

	// *****************************
	// Check Access
	// *****************************
	
	if ( !readonly && !addressbook_check_access($data->uid) ) {
		drupal_access_denied();
		return;
	}
	
	// *****************************
	// Query Database
	// *****************************
	
	// Fetch member from database
	if ($mid==0) {
	
		$query  = '
			SELECT 
				b.street as street, 
				b.zipcode as zipcode, 
				b.city as city, 
				b.country as country, 
				b.telephone as telephone,
				b.last_name as last_name,
				b.middle_name as middle_name
			FROM 
				addressbook_family b
			WHERE 
				b.fid='.$fid;
			
	} else {
			
		$query  = '
			SELECT 
				a.first_name as first_name, 
				a.middle_name as middle_name, 
				a.last_name as last_name, 
				a.birth_day as birthday, 
				a.mobile as mobile, 
				a.email as email, 
				a.work as work, 
				a.notes as notes,
				a.active_roles as active_roles, 
				a.wanted_roles as wanted_roles, 
				a.uid as uid, 
				a.fid as fid, 
				a.last_updated as last_updated,
				b.street as street, 
				b.zipcode as zipcode, 
				b.city as city, 
				b.country as country, 
				b.telephone as telephone,
				c.picture as picture
			FROM 
				addressbook_member a, addressbook_family b LEFT JOIN addressbook_picture c ON c.mid='.$mid.' 
			WHERE 
				b.fid=a.fid and
				a.mid='.$mid;
	}
	$queryResult = db_query($query);
	$data = db_fetch_object($queryResult);

	// *****************************
	// Get posted Inputs
	// *****************************
	
	if ( isset($_POST["first_name"]) && !$readonly) {
		$first_name=htmlentities($_POST['first_name']);
	} else {
		$first_name=$data->first_name;
	}
	addressbook_debug_param("first_name",$first_name);

	if ( isset($_POST["middle_name"]) && !$readonly) {
		$middle_name=htmlentities($_POST['middle_name']);
	} else {
	   $middle_name=$data->middle_name;
	}
	addressbook_debug_param("middle_name", $middle_name);
		
	if ( isset($_POST["last_name"]) && !$readonly) {
		$last_name=htmlentities($_POST['last_name']);
	} else {
		$last_name=$data->last_name;
	}
	addressbook_debug_param("last_name",$last_name);
	
	if ( isset($_POST["mobile"]) && !$readonly) {
		$mobile=htmlentities(ucfirst($_POST['mobile']));
	} else {
		$mobile=$data->mobile;
	}
	addressbook_debug_param("mobile", $mobile);
	
	if ( isset($_POST["email"]) && !$readonly) {
		$email=htmlentities(ucfirst($_POST['email']));
	} else {
		$email=$data->email;
	}
	addressbook_debug_param("email", $email);

	if ( isset($_POST["birthday_day"]) && !$readonly) {
		$birthday =$_POST['birthday_year'].'-';
		$birthday.=$_POST['birthday_month'].'-';
		$birthday.=$_POST['birthday_day'];
	} else {
		$birthday=$data->birthday;
	}
	addressbook_debug_param("birthday",$birthday);
	
	if ( isset($_POST["work"]) && !$readonly) {
		$work=htmlentities($_POST['work']);
	} else {
		$work=$data->work;
	}
	addressbook_debug_param("work",$work);
	
	if ( isset($_POST["notes"]) && !$readonly) {
		$notes=htmlentities($_POST['notes']);
	} else {
		$notes=$data->notes;
	}
	addressbook_debug_param("notes",$notes);
  	
	$uploadedfile=$_FILES['uploadedfile']['path'].$_FILES['uploadedfile']['name'];
	addressbook_debug_param("uploadedfile", $uploadedfile);
	
	// *****************************
	// Create page
	// *****************************

	drupal_set_title(t('Addressbook member view') );

	$page .= '<form enctype="multipart/form-data" name="AddressbookForm" method="POST" >';
	$page .= '<div class="addressbook">';
		
	// Breadcrumb menu
	if ($fid!="") {
		$node["child1"] = l(t('Family list'),URL_ADDRESSBOOK_FAMILY);
		$node["child2"] = addressbook_hiddenlink('AddressbookForm', 'fid', $fid, 'FamilyView', t("Family view"));
		$node["child3"] = addressbook_hiddenlink('AddressbookForm', 'mid', $mid, 'MemberView', t("Member view"));
	} else {
		$node["child1"] = l(t('Member list'),URL_ADDRESSBOOK_MEMBER);
		$node["child2"] = addressbook_hiddenlink('AddressbookForm', 'mid', $mid, 'MemberView', t("Member view"));
	}
	addressbook_breadcrumb($node);
		
	$page .= '<fieldset>' ;

	$page .= addressbook_load_picture($data->picture, 0, 0, "right", true);
	
	$page .= '<h4>'.$data->street.'</h4>';	
	$page .= '<h4>'.$data->zipcode.'&nbsp;&nbsp;'.$data->city.'</h4>';	
	$page .= '<h4>'.$data->country.'</h4>';	
	$page .= '<h4>'.$data->telephone.'</h4>';	

	$page .= '</fieldset>' ;
		
	// Show this information only to admin users.
	if ( user_access('access administration pages') ) {
		
		$page .= '<fieldset>' ;
		$page .= '<legend>'.t('Extra').'</legend>';
		
		if ($data->last_updated!="") {
			list($year, $time) = split(' ', $data->last_updated);
			list($access_year, $access_month, $access_day) = split('-', $year);
			list($access_hour, $access_minute, $access_sec) = split(':', $time);
			$last_updated=$access_day.'-'.$access_month.'-'.$access_year.' '.$access_hour.':'.$access_minute.':'.$access_sec;

			$page .= addressbook_form_item(
				t("Last updated:"),
				addressbook_view_text("last_updated", 22, 22, $last_updated, true),
				"", 
				true );
		}
		
		$page .= addressbook_form_item(
			t("Member Id:"),
			$mid,
			t("Member id"), 
			true );
			
		$page .= addressbook_form_item(
			t("Owner:"),
			addressbook_view_owner($data->uid, $readonly),
			t("Drupal owner of this member"), 
			$readonly );
			
		$page .= '</fieldset>' ;
	}

	$page .= '<fieldset>' ;
	$page .= '<legend>'.t('General').'</legend>';
	
	$page .= addressbook_form_item(
					t("First Name:").addressbook_view_manitory(),
					addressbook_view_text("first_name", 50, 50, $first_name, $readonly),
					t("First Name of member"), 
					$readonly );
				
	$page .= addressbook_form_item(
					t("Middle Name:"),
					addressbook_view_text("middle_name", 10, 10, $middle_name, $readonly),
					t("Middle Name of member"), 
					$readonly );
				
	$page .= addressbook_form_item(
					t("Last Name:").addressbook_view_manitory(),
					addressbook_view_text("last_name", 50, 50, $last_name, $readonly),
					t("Last Name of member"), 
					$readonly );
						
	$page .= addressbook_form_item(
					t("Mobile:"),
					addressbook_view_text("mobile", 16, 16, $mobile, $readonly),
					t("Mobile number of member"), 
					$readonly );
					
	$page .= addressbook_form_item(
					t("Email:"),
					addressbook_view_text("email", 50, 50, $email, $readonly),
					t("Mobile number of member"), 
					$readonly );
					
	$page .= addressbook_form_item(
					t("Birthday:"),
					addressbook_view_birthday($birthday, $readonly),
					t("Birtday of member"), 
					$readonly );
					
	$page .= addressbook_form_item(
					t("Work:"),
					addressbook_view_textarea('work', 80, 4, 512, $work, $readonly),
					t("Work notes"), 
					$readonly );
					
	$page .= addressbook_form_item(
					t("Notes:"),
					addressbook_view_textarea('notes', 80, 4, 512, $notes, $readonly),
					t("General notes"), 
					$readonly );

	if (!addressbook_check_member_picture())  {
		$page .= addressbook_form_item(
					t("Image file"),
					addressbook_input_picture("uploadedfile", $uploadedfile, $readonly),
					t("Only jpg format is supported"), 
					$readonly );
	}
		
	$page .= addressbook_view_roles($data->active_roles,$data->wanted_roles, $readonly);

	$page .= '</fieldset>' ;

	// *****************************
	// Create Menu Bar
	// *****************************

	if ($readonly) {
		if (addressbook_check_access($uid)) {
			$page .= addressbook_hiddenlink('AddressbookForm','mid',$mid,'MemberEdit',t('Edit'));		
			$page .= ' | '.addressbook_hiddenlink('AddressbookForm','fid',$fid,'FamilyView',t('Back'));
		} else {
			$page .= addressbook_hiddenlink('AddressbookForm','fid',$fid,'FamilyView',t('Back'));
		}
	} else {
		$page .= addressbook_hiddenlink('AddressbookForm','mid',$mid,'MemberSave',t('Save'));
		if ($mid!=0) {
			if (addressbook_check_member_picture()) {
				$page .= ' | '.addressbook_hiddenlink('AddressbookForm','mid',$mid,'PictureDelete',t('Delete Picture'));
			}
			$page .= ' | '.addressbook_hiddenlink('AddressbookForm','mid',$mid,'MemberRemove',t('Delete Member'));
			$page .= ' | '.addressbook_hiddenlink('AddressbookForm','mid',$mid,'MemberView',t('Cancel'));
		} else {
			$page .= ' | '.addressbook_hiddenlink('AddressbookForm','fid',$fid,'FamilyView',t('Cancel'));
		}
	}
		
	
	$page .= '</div>';
	$page .= '</form>';
 
	return $page;
}

/** 
  * Member remove - are you sure dialog
  */
function addressbook_member_remove() {

	// *****************************
	// Get posted Inputs
	// *****************************
	
	$mid=$_POST["mid"];
	if ($mid=="") {
		$mid=0;
	}
	addressbook_debug_param("mid", $mid);
	
	// *****************************
	// Create views
	// *****************************
	
	$page .= '<form name="AddressbookForm" method="POST" >';	
	$page .= '<div class="addressbook">';

	// Breadcrumb menu
	$node["child1"] = l(t('Member list'),URL_ADDRESSBOOK_MEMBER);
	$node["child2"] = addressbook_hiddenlink('AddressbookForm', 'mid', $mid, 'MemberEdit', t("Member view"));
	addressbook_breadcrumb($node);
	
	drupal_set_title(t('Addressbook member delete'));
	
	$page .= '<fieldset>';
	
	$page .= '<h4>';
	$page .= t("Delete selected member. Are you sure?");
	$page .= '</h4>';
	
	$page .= "<br/>";
	
	$page .= addressbook_hiddenlink('AddressbookForm','mid',$mid,'MemberDelete',t('Yes'));
	$page .= ' | '.addressbook_hiddenlink('AddressbookForm','mid',$mid,'MemberEdit',t('No'));

	$page .= '</fieldset>' ;

	$page .= '</div>';
	$page .= '</form>'; 
 
	return $page;
}

// *********************************************************************************
// Database functions
// *********************************************************************************

/*
 * Save member data to database
 *
 * @return error (true of false)
 */
function addressbook_member_save() {

	// *****************************
	// Get posted Inputs
	// *****************************

	$mid=htmlentities($_POST["mid"]);
	addressbook_debug_param("mid", $mid);
	
	$fid=$_SESSION["fid"];
	addressbook_debug_param("fid", $fid);
	
	$first_name=htmlentities($_POST['first_name']);
	addressbook_debug_param("first_name", $first_name);
	
	$middle_name=htmlentities($_POST['middle_name']);
	addressbook_debug_param("middle_name", $middle_name);
	
	$last_name=htmlentities(ucfirst($_POST['last_name']));
	addressbook_debug_param("last_name", $last_name);
	
	$mobile=htmlentities($_POST['mobile']);
	addressbook_debug_param("mobile", $mobile);
	
	$email=htmlentities($_POST['email']);
	addressbook_debug_param("email", $email);
	
	$notes=htmlentities($_POST['notes']);
	addressbook_debug_param("notes", $notes);
	
	$work=htmlentities($_POST['work']);
	addressbook_debug_param("work", $work);
	
	$notes=htmlentities($_POST['notes']);
	addressbook_debug_param("notes", $notes);
	
	$owner=htmlentities($_POST['owner']);
	addressbook_debug_param("owner", $owner);
	
	$birthday=$_POST['birthday_day'].'-'.$_POST['birthday_month'].'-'.$_POST['birthday_year'];
	addressbook_debug_param("birthday", $birthday);
	
	/* Fetch all active and wanted roles from the HTTP request */
	$i=0;
	$active_first=1;
	$wanted_first=1;
	$tmp=split( ",", variable_get('addressbook_roles',''));
	while ($tmp[$i]!='') {
		if ($_POST["active_$tmp[$i]"]=='on') {
			if ($active_first=='1') {
				$active_roles=$tmp[$i];
				$active_first=0;
			} else {
				$active_roles.=';'.$tmp[$i];
			}
		}
    
		if ($_POST["wanted_$tmp[$i]"]=='on') {
			if ($wanted_first=='1') {
				$wanted_roles=$tmp[$i];
				$wanted_first=0;
			} else {
				$wanted_roles.=';'.$tmp[$i];
			}
		}
		$i++;
	}
	addressbook_debug_param("active_roles", $active_roles);
	addressbook_debug_param("wanted_roles", $wanted_roles);

	// *****************************
	// Input Validation
	// *****************************
	
	$error=0;
	
	if ($first_name=="") {

		$msg = t('First name is manitory');
		drupal_set_message( $msg, 'error');
		$error=1;
	}

	if ($last_name=="") {

		$msg = t('Last name is manitory');
		drupal_set_message( $msg, 'error');
		$error=1;
	}

	if ($fid==0) { 
		$msg = t('Family id is null');
		drupal_set_message( $msg, 'error');
		$error=1;
	}
	
	if ($error==1) {
		return true;
	}
	
	// *****************************
	// Update Database
	// *****************************
	
	if ($mid==0) {
		
		// Check if user allready exist. Protect against reload button
		$query  = 'select ';
		$query .= ' a.uid as uid ';
		$query .= 'from ';
		$query .= ' addressbook_member a ';
		$query .= 'where ' ;
		$query .= ' a.first_name="'.$first_name.'" and ';
		$query .= ' a.middle_name="'.$middle_name.'" and ';
		$query .= ' a.last_name="'.$last_name.'"';
	
		addressbook_debug_sql($query);
		$queryResult = db_query($query);     
		$data = db_fetch_object($queryResult);
	
		if ($data->uid!="") {
		
			// reload detect, return direct!
			return true;
		}
				
		// Insert new member 
		$query  = '
			INSERT INTO addressbook_member (
				first_name, 
				middle_name, 
				last_name, 
				birth_day, 
				mobile, 
				email, 
				notes, 
				work, 
				active_roles, 
				wanted_roles, 
				uid, 
				fid,
				last_updated) VALUES (';
		$query .= '"'.$first_name.'",';
		$query .= '"'.$middle_name.'",';
		$query .= '"'.$last_name.'",';
		$query .= '"'.addressbook_convert_date_reverse($birthday).'",';
		$query .= '"'.$mobile.'",';
		$query .= '"'.$email.'",';
		$query .= '"'.$notes.'",';
		$query .= '"'.$work.'",';
		$query .= '"'.$active_roles.'",';
		$query .= '"'.$wanted_roles.'",';
		$query .= '"'.$owner.'",';
		$query .= '"'.$fid.'",';
		$query .= 'SYSDATE() )';

		db_query($query);
		
		// Get new Member id
		$query  = 'select ';
		$query .= ' a.mid as mid ';
		$query .= 'from ';
		$query .= ' addressbook_member a ';
		$query .= 'where ' ;
		$query .= ' a.first_name="'.$first_name.'" and ';
		$query .= ' a.middle_name="'.$middle_name.'" and ';
		$query .= ' a.last_name="'.$last_name.'"';
	
		addressbook_debug_sql($query);
		$queryResult = db_query($query);     
		$data = db_fetch_object($queryResult);

		// Update drupal module variable
		variable_set("addressbook_last_upload_date",date("d-m-Y"));
	
		// Post created member id
		$_POST["mid"]=$data->mid;
		
		// *****************************
		// create message
		// *****************************
	
		$msg .= t('Member').' '.$data->mid.' '.t('created');
		drupal_set_message($msg, 'status');
		watchdog('user', $msg);	
	
	} else {
		
		$query = 'UPDATE addressbook_member SET ';
		$query .= 'first_name = "'.$first_name.'",';
		$query .= 'middle_name = "'.$middle_name.'",';
		$query .= 'last_name = "'.$last_name.'",';
		$query .= 'birth_day = "'.addressbook_convert_date_reverse($birthday).'",';
		$query .= 'mobile = "'.$mobile.'",';
		$query .= 'email = "'.$email.'",';
		$query .= 'notes = "'.$notes.'",';
		$query .= 'work = "'.$work.'",';
		$query .= 'uid = "'.$owner.'",';
		$query .= 'active_roles = "'.$active_roles.'",';
		$query .= 'wanted_roles = "'.$wanted_roles.'",';
		$query .= 'uid = "'.$owner.'",';
		$query .= 'last_updated = SYSDATE() WHERE mid='.$mid;

		db_query($query);		
		addressbook_debug_sql($query);

		// Update drupal module variable
		variable_set("addressbook_last_upload_date",date("d-m-Y"));
  
		// *****************************
		// create message
		// *****************************
	
		$msg .= t('Member').' '.$mid.' '.t('updated');
		drupal_set_message($msg, 'status');
		watchdog('user', $msg);	
	}
	return false;
}


/*
 * Delete member SQl function
 * @return true of false
 */
function addressbook_delete_member() {

	// *****************************
	// Get posted Inputs
	// *****************************
	
	$mid=htmlentities($_POST["mid"]);
	addressbook_debug_param("mid", $mid);
	
	// *****************************
	// Database actions
	// *****************************

	if ($mid!=0) {
	
		// Delete member
		$query = 'delete FROM addressbook_member WHERE mid='.$mid;		
		db_query($query);  
	
		// Update drupal module variable
		variable_set("addressbook_last_upload_date",date("d-m-Y"));
  
		// *****************************
		// Create message
		// *****************************
	
		$msg .= t('Member').' '.$mid.' '.t('is deleted.');
		drupal_set_message($msg, 'status');
		watchdog('user', $msg);
		
		return 0;
	}
	return 1;	
}

// *********************************************************************************
// The end
// *********************************************************************************