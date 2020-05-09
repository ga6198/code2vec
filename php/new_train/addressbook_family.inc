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
 
function addressbook_family() {

	//echo var_dump($_POST);
	
	// *****************************
	// Process POST an SESSION variables
	// *****************************
	
	$Action=htmlentities($_POST['Action']);
	addressbook_debug_param("Action", $Action);
		
	$fid=$_POST["fid"];
	if ($fid!="") {
		$_SESSION["fid"]=$fid;
	}
	addressbook_debug_param("fid", $fid);
	
	// *****************************
	// Process statemachine
	// *****************************
	
	switch ($Action) {
	
		// ******************
		// Family Actions
		// ******************
   
		case 'FamilyList':
			$page = addressbook_family_list();
			break;
			
		case 'FamilyView':
			$page = addressbook_family_view();
			break;
			
		case 'FamilyEdit':
			$page = addressbook_family_edit();
			break;
			
		case 'FamilySave':
			$error=addressbook_family_save();
			if (!$error) {
				addressbook_save_picture();
				$page .= addressbook_family_view();
			} else {
				$page.= addressbook_family_edit();
			}
			break;
		
		case 'FamilyRemove':
		    $page = addressbook_family_remove();
			break;
	 
		case 'FamilyDelete':
			addressbook_family_delete();
			addressbook_picture_delete();
			$page = addressbook_family_list();
			break;
			
		// ******************
		// Member Actions
		// ******************
		
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
				$page = addressbook_member_view(true);
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
			$page = addressbook_family_view();
			break;
			
		// ******************
		// Other Actions
		// ******************
			
		case 'ShowMap':
			$page = addressbook_map_view();
			break;
			
		case 'PictureDelete': 
			$flow=addressbook_picture_delete();
			if ($flow) {
				$page = addressbook_family_edit();
			} else {
				$page = addressbook_member_view(false);
			}
			break;
			
		default: 
			$page = addressbook_family_list();
			break;
	}
	
	if ($page!="") {
		print theme("page", $page);
	}
}
			
			
// *********************************************************************************
// Views
// *********************************************************************************

/*
 * Function shows all family in a list form
 * @return HTML
 */
function addressbook_family_list() {

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
	addressbook_debug_param("Sort", $Sort);
	$_SESSION["Sort"]=$Sort;

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

	
	$query  = '
		SELECT 
			a.fid, 
			a.middle_name, 
			a.last_name, 
			a.street, 
			a.zipcode, 
			a.city, 
			b.picture
		FROM 
			addressbook_family a LEFT JOIN addressbook_picture b ON b.fid=a.fid and b.mid=0
		WHERE 
			LAST_NAME LIKE "'.$parameter.'%" 
		ORDER BY LAST_NAME, MIDDLE_NAME';
  
	$queryResult = db_query($query);

	// *****************************
	// Create page
	// *****************************
	
	drupal_set_title(t('Addressbook family list'));
  
	$page .= '<form name="AddressbookForm" method="POST" >';	
	$page .= '<div class="addressbook">';
  
	// Breadcrumb menu
	$node["child1"] = l(t('Family list'),URL_ADDRESSBOOK_FAMILY);
	addressbook_breadcrumb($node);

  	$page .= '<fieldset>' ;
	$page .= '<legend>'.t('Filter').'</legend>';
	
	$page .= '<div class="form-item">';
	$page .= '<label>'.t('Last name').' :</label>';
	$page .= '<input id="text" name="Search" size="40" maxlength="40" type="text" value="'.$Search.'"> ';
	$page .= addressbook_hiddenlink('AddressbookForm','','','Search',t('Search'));
	$page .= '<div class="description">';
	$page .= t('Enter lastname and press enter to find a family.');
	$page .= '</div>';
	$page .= '</div>';
	
	$page .= addressbook_sortbar($Sort,"List");
	  
	$page .= '</fieldset>';

	$page .= '<table>';
	$page .= '<thead>';
  
	$page .= '<tr>';
  
	if ( variable_get('addressbook_show_thumbnails',0)==1) {
		$page .= '<th>';
      $page .= '<b>'.t('Picture').'<b>';
      $page .= '</th>';
	}
	$page .= '<th>';
	$page .= '<b>'.t('Name').'</b>';
	$page .= '</th>';
	
	$page .= '<th>';
	$page .= '<b>'.t('Street').'</b>';
	$page .= '</th>';
	
	$page .= '<th>';
	$page .= '<b>'.t('Zipcode').'</b>';
	$page .= '</th>';
	
	$page .= '<th>';
	$page .= '<b>'.t('City').'</b>';
	$page .= '</th>';
	
	$page .= '</tr>';
	
	$page .= '</thead>';
	$page .= '<tbody>';

	// Show all found families
	$page_tmp='';
	while ($data = db_fetch_object($queryResult)) {

		if ((++$i%2)==0) {
			$page_tmp .= '<tr class="even">';
		} else {
			$page_tmp .= '<tr class="odd">';
		}
	
		if ( variable_get('addressbook_show_thumbnails',0)==1) {

			$page_tmp .= '<td>';
			$page_tmp .= addressbook_load_picture($data->picture, 50, 60, "right", false);
			$page_tmp .= '</td>';
		}
	
		// Format Name
		$page_tmp .= '<td>';
		if ( variable_get('addressbook_name_format',1)=='1') {
			$name = $data->middle_name.' '.$data->last_name;
		} else {
			$name = $data->last_name.', '.$data->middle_name;
		}
		$page_tmp .= addressbook_hiddenlink('AddressbookForm', 'fid', $data->fid, 'FamilyView', $name);
		$page_tmp .= '</td>';	
	
		$page_tmp .= '<td>';
		$page_tmp .= $data->street;
		$page_tmp .= '</td>';
		
		$page_tmp .= '<td>';
		$page_tmp .= $data->zipcode;
		$page_tmp .= '</td>';
		
		$page_tmp .= '<td>';
		$page_tmp .= $data->city;
		$page_tmp .= '</td>';
		
		$page_tmp .= '</tr>';
	}

	if ( $page_tmp!='') {

		// Show Content
		$page .= $page_tmp;
		
	} else {
	
		// No content found
		$page .= '<tr>';
		$page .= '<td>';
		$page .= t('No records found');
		$page .= '</td>';
		$page .= '</tr>';
	}
	$page .= '</tbody>';
	$page .= '</table>';
 
	$page .= addressbook_hiddenlink('AddressbookForm','fid',0,'FamilyEdit',t('New family'));
	$page .= ' | '.l(t("Back"), URL_ADDRESSBOOK);
	
	$page .= '</div>';
	$page .= '</form>';
	
	return $page;
}
  
/**
 * Render a page showing the selected family in detail
 * @return HTML
 */
function addressbook_family_view( $fid = -1)  {

	// *****************************
	// Get posted Inputs
	// *****************************
	
	if ($fid==-1) {
		$fid=$_POST["fid"];
		if ($fid=="") {
			$fid=$_SESSION["fid"];
		}
	}
	addressbook_debug_param("fid", $fid);
		
	// *****************************
	// Query Database
	// *****************************

	$query  = '
		SELECT 
			a.fid, 
			a.middle_name, 
			a.last_name, 
			a.street, 
			a.zipcode, 
			a.city, 
			a.country, 
			a.telephone, 
			a.uid,
			b.picture
		FROM 
			addressbook_family a LEFT JOIN addressbook_picture b ON b.fid=a.fid and b.mid=0
		WHERE 
			a.fid='.$fid;
	
	$queryResult = db_query($query);
	$data = db_fetch_object($queryResult);

	// *****************************
	// Create page
	// *****************************

	drupal_set_title(t('Addressbook family view') );

	$page .= '<form enctype="multipart/form-data" name="AddressbookForm" method="POST" >';
	$page .= '<div class="addressbook">';
						
	// Breadcrumb menu
	$node["child1"] = l(t('Family list'),URL_ADDRESSBOOK_FAMILY);
	$node["child2"] = addressbook_hiddenlink('AddressbookForm', 'fid', $fid, 'FamilyView', t("Family view"));
	addressbook_breadcrumb($node);

	$page .= '<fieldset>' ;
	
	$page .= addressbook_load_picture($data->picture, 0, 0, "right", true);
	
	$page .= '<h4>'.$data->middle_name.' '.$data->last_name.'</h4>';
	$page .= '<h4>'.$data->street.'</h4>';	
	$page .= '<h4>'.$data->zipcode.'&nbsp;&nbsp;'.$data->city.'</h4>';	
	$page .= '<h4>'.$data->country.'</h4>';	
	$page .= '<h4>'.$data->telephone.'</h4>';	
	
	// Query Family Members
	$query  = '
		SELECT 	
			a.mid, 
			a.first_name, 
			a.middle_name, 
			a.last_name, 
			a.birth_day, 
			a.mobile, 
			a.email, 
			a.uid,
			b.picture as picture
		FROM 
			addressbook_member a LEFT JOIN addressbook_picture b ON b.mid=a.mid 
		WHERE 
			a.fid='.$fid.' 
		ORDER BY birth_day';
		
	$queryResult = db_query($query);
		
	// Show Banner
	$page .= '<table>';
	$page .= '<thead>';
	$page .= '<tr>';
	$page .= '<th><b>'.t('Phote').'</b></th>';
	$page .= '<th><b>'.t('Name').'</b></th>';	
	$page .= '<th class="active"><b>'.t('Birthday').'</b></th>';
	$page .= '<th><b>'.t('Mobile').'</b></th>';
	$page .= '<th><b>'.t('Email').'</b></th>';	
	$page .= '</tr>';
	$page .= '</thead>';
	
	// Show all found members
	$page .= '<tbody>';
	$page_tmp='';
	while ($data = db_fetch_object($queryResult)) {

		if ((++$i%2)==0) {
			$page .= '<tr class="even">';
		} else {
			$page .= '<tr class="odd">';
		}

		if ( variable_get('addressbook_show_thumbnails',0)==1 ) {
			$page .= '<td>';
			$page .= addressbook_load_picture($data->picture, 25, 30, "left", false);
			$page .= '</td>';
		}	
	
		$page .= '<td>';
		$name=addressbook_view_name($data->first_name,$data->middle_name,$data->last_name,true);
		$page .= addressbook_hiddenlink('AddressbookForm','mid',$data->mid,'MemberView',$name);
		$page .= '</td>';
		
		$page .= '<td>';
		list($year, $month, $day) = split('[/.-]', $data->birth_day);
		$page .= $day.'-'.$month.'-'.$year;
		$page .= '</td>';

		$page .= '<td>';
		$page .= $data->mobile;
		$page .= '</td>';
		
		$page .= '<td>';
		$page .= l($data->email,'mailto:'.$data->email);
		$page .= '</td>';
		
		$page .= '</tr>';
	}

	$page .= '</tbody>';
	$page .= '</table>';
	
	if (addressbook_check_access($uid)) {
  
		$page .= addressbook_hiddenlink('AddressbookForm','fid',$fid,'FamilyEdit',t('Family Edit'));
		$page .= ' | '.addressbook_hiddenlink('AddressbookForm','fid', $fid, 'MemberEdit', t('Member Add'));
			
		if (variable_get('addressbook_map_link',0)==1) {
			$page .= ' | '.addressbook_hiddenlink('AddressbookForm','fid',$fid,'ShowMap',t('Show Map'));
		}
		$page .= ' | '.addressbook_hiddenlink('AddressbookForm','','','FamilyList',t('Return'));
	} else { 
		$page .= addressbook_hiddenlink('AddressbookForm','','','FamilyList',t('Return'));	
	}
	
	
	$page .= '</div>';
	$page .= '</form>';
	
	return $page;
}

/**
 * Render a page showing the selected family in detail
 * @return HTML
 */
function addressbook_family_edit()  {
		
	// *****************************
	// Get posted Inputs
	// *****************************
	
	$fid=htmlentities($_POST["fid"]);
	addressbook_debug_param("fid", $fid);
	
	// *****************************
	// Query Database
	// *****************************
	
	$query = '
		SELECT 
			a.fid, 
			a.middle_name, 
			a.last_name, 
			a.street, 
			a.zipcode, 
			a.city, 
			a.country, 
			a.telephone, 
			a.last_updated, 
			a.uid,
			b.picture
		FROM 
			addressbook_family a LEFT JOIN addressbook_picture b ON b.fid=a.fid and b.mid=0
		WHERE 
			a.fid='.$fid;
					
	$queryResult = db_query($query);
	$data = db_fetch_object($queryResult);

	// *****************************
	// Check Access
	// *****************************
	
	if ( !addressbook_check_access($data->uid) ) {
			drupal_access_denied();
			return;
	}
	
	// *****************************
	// Get posted Inputs
	// *****************************
	
	if ( isset($_POST["middle_name"]) ) {
		$middle_name=htmlentities($_POST['middle_name']);
	} else {
	   $middle_name=$data->middle_name;
	}
	addressbook_debug_param("middle_name", $middle_name);
	
	if ( isset($_POST["last_name"]) ) {
		$last_name=htmlentities(ucfirst($_POST['last_name']));
	} else {
		$last_name=$data->last_name;
	}
	addressbook_debug_param("last_name", $last_name);
	
	if ( isset($_POST["street"]) ) {
		$street=htmlentities(ucfirst($_POST['street']));
	} else {
		$street=$data->street;
	}
	addressbook_debug_param("street", $street);
	
	if ( isset($_POST["zipcode"]) ) {
		$zipcode=htmlentities($_POST['zipcode']);
	} else {
		$zipcode=$data->zipcode;
	}
	addressbook_debug_param("zipcode", $zipcode);
	
	if ( isset($_POST["city"]) ) {
		$city=htmlentities($_POST['city']);
	} else {
		$city=$data->city;
	}
	addressbook_debug_param("city", $city);
	
	if ( isset($_POST["country"]) ) {
		$country=htmlentities($_POST['country']);
	} else {
		$country=$data->country;
	}
	if ($fid==0) {
		$country=variable_get('addressbook_country', 'Nederland');
	}
	addressbook_debug_param("country", $country);
	
	if ( isset($_POST["telephone"]) ) {
		$telephone=htmlentities($_POST['telephone']);
	} else {
		$telephone=$data->telephone;
	}
	addressbook_debug_param("telephone", $telephone);
	
	if ( isset($_POST["owner"]) ) {
		$owner=htmlentities($_POST['owner']);
	} else {
		$owner=$data->owner;
	}
	addressbook_debug_param("owner", $owner);
	
	$uploadedfile=$_FILES['uploadedfile']['path'].$_FILES['uploadedfile']['name'];
	addressbook_debug_param("uploadedfile", $uploadedfile);
		
	// *****************************
	// Create Page
	// *****************************

	$page .= '<form enctype="multipart/form-data" name="AddressbookForm" method="POST" >';
	$page .= '<div class="addressbook">';

	drupal_set_title(t('Addressbook family edit'));

	// Breadcrumb menu
	$node["child1"] = l(t('Family list'),URL_ADDRESSBOOK_FAMILY);
	$node["child2"] = addressbook_hiddenlink('AddressbookForm', 'fid', $fid, 'FamilyView', t("Family view"));
	addressbook_breadcrumb($node);
	
	// Show this information only to admin users.
	if ( user_access('access administration pages') ) {
		
		$page .= '<fieldset>' ;
		$page .= '<legend>'.t('Extra').'</legend>';
		
		$page .= addressbook_form_item(
			t("Last Access"),
			addressbook_view_timestamp($data->last_updated),
			t("Last access date"), 
			true );
		
		$page .= addressbook_form_item(
			t("Family Id:"),
			$fid,
			t("Family id"), 
			true );
			
		$page .= addressbook_form_item(
			t("Owner"),
			addressbook_view_owner($owner,$readonly),
			t("Drupal owner (user) of address."), 
			$readonly );
			
		$page .= '</fieldset>';	
	}
		
	$page .= '<fieldset>' ;	
	$page .= '<legend>'.t('General').'</legend>';
	
	$page .= addressbook_load_picture($data->picture, 0, 0, "right", true);
		
	$page .= addressbook_form_item(
		t("Middle Name"),
		addressbook_view_text("middle_name", 10, 10, $middle_name, $readonly),
		t("Middle name of the family name (for example: van, de, etc..)"), 
		$readonly );

	$page .= addressbook_form_item(
		t("Last Name").addressbook_view_manitory(),
		addressbook_view_text("last_name", 35, 35, $last_name, $readonly),
		t("Last name of the family name."), 
		$readonly );

	$page .= addressbook_form_item(
		t("Street").addressbook_view_manitory(),
		addressbook_view_text("street", 50, 50, $street, $readonly),
		t("Last name of the family name."), 
		$readonly );

	$page .= addressbook_form_item(
		t("Zipcode"),
		addressbook_view_text("zipcode", 8, 8, $zipcode, $readonly),
		t("Zipcode of family address."), 
		$readonly );
		
	$page .= addressbook_form_item(
		t("City").addressbook_view_manitory(),
		addressbook_view_text("city", 50, 50, $city, $readonly),
		t("City of family address."), 
		$readonly );
		
	$page .= addressbook_form_item(
		t("Country").addressbook_view_manitory(),
		addressbook_view_country($country, $readonly),
		t("Country of family address."), 
		$readonly );
		
	$page .= addressbook_form_item(
		t("Telephone"),
		addressbook_view_text("telephone", 16, 16, $telephone, $readonly),
		t("Telephone of family address."), 
		$readonly );
	
	if (!addressbook_check_family_picture()) {
		$page .= addressbook_form_item(
			t("Image file"),
			addressbook_input_picture("uploadedfile", $uploadedfile, $readonly),
			t("Only jpg format is supported"), 
			$readonly );
	}
					
	// Menu bar
	
	$page .= addressbook_hiddenlink('AddressbookForm','fid',$fid,'FamilySave',t('Save'));
	if (addressbook_check_family_picture()) {
		$page .= ' | '.addressbook_hiddenlink('AddressbookForm','fid',$fid,'PictureDelete',t('Remove Picture'));
	}
	if ($fid!=0) {
		$page .= ' | '.addressbook_hiddenlink('AddressbookForm','fid',$fid,'FamilyRemove',t('Remove Family'));
	}
	$page .= ' | '.addressbook_hiddenlink('AddressbookForm','fid',$fid,'FamilyView',t('Cancel'));

	$page .= '</fieldset>';	
	
	$page .= '</div>';
	$page .= '</form>';
	
	return $page;
}

function addressbook_family_remove() {

	// *****************************
	// Get posted Inputs
	// *****************************
	
	$fid=$_POST["fid"];
	addressbook_debug_param("fid", $fid);
	
	// *****************************
	// Create views
	// *****************************
	
	$page .= '<form name="AddressbookForm" method="POST" >';	
	$page .= '<div class="addressbook">';
	
	// Breadcrumb menu
	$node["child1"] = l(t('Family list'),URL_ADDRESSBOOK_FAMILY);
	$node["child2"] = addressbook_hiddenlink('AddressbookForm', 'fid', $fid, 'FamilyEdit', t("Family view"));
	addressbook_breadcrumb($node);
	
	drupal_set_title(t('Addressbook family delete'));
	
	$page .= '<fieldset>';
		
	$page .= '<h4>';
	$page .= t("Delete selected family. Are you sure?");
	$page .= '</h4>';
	
	$page .= "<br/>";

	$page .= addressbook_hiddenlink('AddressbookForm','fid',$fid,'FamilyDelete',t('Yes'));
	$page .= ' | '.addressbook_hiddenlink('AddressbookForm','fid',$fid,'FamilyEdit',t('No'));
	
	$page .= '</fieldset>' ;

	$page .= '</div>';
	$page .= '</form>'; 
 
	return $page;
}

// *********************************************************************************
// Database functions
// *********************************************************************************

/*
 * insert family SQL function
 * @return true of false
 */
function addressbook_family_save() {

	// *****************************
	// Get posted Inputs
	// *****************************

	$fid=htmlentities($_POST["fid"]);
	if ($fid=="") {
		$fid=0;
	}
	addressbook_debug_param("fid", $fid);
		
	$middle_name=htmlentities($_POST['middle_name']);
	addressbook_debug_param("middle_name",$middle_name);
	
	$last_name=htmlentities(ucfirst($_POST['last_name']));
	addressbook_debug_param("last_name",$last_name);
	
	$street=htmlentities(ucfirst($_POST['street']));
	addressbook_debug_param("street",$street);
	
	$zipcode=htmlentities($_POST['zipcode']);
	addressbook_debug_param("zipcode",$zipcode);
	
	$city=htmlentities(ucfirst($_POST['city']));
	addressbook_debug_param("city",$city);
	
	$country=htmlentities($_POST['country']);
	addressbook_debug_param("country",$country);
	
	$telephone=htmlentities($_POST['telephone']);
	addressbook_debug_param("telephone",$telephone);
	
	$owner=htmlentities($_POST['owner']);
	addressbook_debug_param("owner",$owner);
	
	// *****************************
	// Input Validation
	// *****************************
	
	$error=0;

	if ($last_name=="") {

		$msg = t('Last name is manitory');
		drupal_set_message( $msg, 'error');
		$error=1;
	}

	if ($street=="") {

		$msg = t('Street is manitory');
		drupal_set_message( $msg, 'error');
		$error=1;
	}
	
	if ($city=="") {

		$msg = t('City is manitory');
		drupal_set_message( $msg, 'error');
		$error=1;
	}
	
	if ($country=="") {

		$msg = t('Country is manitory');
		drupal_set_message( $msg, 'error');
		$error=1;
	}
	
	if ($error==1) {
		return true;
	}

	// *****************************
	// Update Database
	// *****************************

	if ($fid==0) {
	
		// Check if family all exisit, reload button protection
		$query = 'select fid from addressbook_family where 
					zipcode like "'.$zipcode.'" and 
					street like "'.$street.'"';
			
		$queryResult = db_query($query);
		$data = db_fetch_object($queryResult);
		if ($data->fid!="") {

			// Reload detect
			return true;
		}
		
		$query  = 'INSERT INTO addressbook_family (middle_name, last_name, street, zipcode, city, ';
		$query .= 'country, telephone, uid, last_updated) VALUES (';
		$query .= '"'.$middle_name.'",';
		$query .= '"'.$last_name.'",';
		$query .= '"'.$street.'",';
		$query .= '"'.$zipcode.'",';
		$query .= '"'.$city.'",';
		$query .= '"'.$country.'",';
		$query .= '"'.$telephone.'",';
		$query .= '"'.$owner.'",';
		$query .= 'SYSDATE() )';

		db_query($query);
	
		// Check new family entry.
		$query = 'select fid from addressbook_family where 
					zipcode like "'.$zipcode.'" and 
					street like "'.$street.'"';
			
		$queryResult = db_query($query);
		$data = db_fetch_object($queryResult);
			
		// Add to Post variable the new fid 
		$_POST['fid']=$data->fid;
		
		// Update drupal module variable
		variable_set("addressbook_last_upload_date",date("d-m-Y"));
  
		// *****************************
		// create message
		// *****************************
	
		$msg .= t('Family').' '.$data->fid.' '.t('created!');
		drupal_set_message($msg, 'status');
		watchdog('user', $msg);
		
	} else {
		
		// *****************************
		// Update Database
		// *****************************
	
		$query = 'UPDATE addressbook_family SET ';
		$query .= 'middle_name = "'.$middle_name.'",';
		$query .= 'last_name = "'.$last_name.'",';
		$query .= 'street = "'.$street.'",';
		$query .= 'zipcode = "'.$zipcode.'",';
		$query .= 'city = "'.$city.'",';
		$query .= 'country = "'.$country.'",';
		$query .= 'telephone = "'.$telephone.'",';
		$query .= 'uid = "'.$owner.'",';
		$query .= 'last_updated = SYSDATE() ';
		$query .= 'WHERE fid='.$fid;

		db_query($query);
	
		// Update drupal module variable
		variable_set("addressbook_last_upload_date",date("d-m-Y"));
  
		// *****************************
		// create message
		// *****************************
	
		$msg .= t('Family').' '. $fid.' '.t('updated!');
		drupal_set_message($msg, 'status');
		watchdog('user', $msg);
		
	}
	return false;
}


/*
 * delelete family SQL function
 * @return true of false
 */
function addressbook_family_delete() {

	// *****************************
	// Get posted Inputs
	// *****************************
	
	$fid=htmlentities($_POST["fid"]);
	
	// *****************************
	// Database actions
	// *****************************

	if ($fid!=0) {
	
		$query = 'delete FROM addressbook_family WHERE fid='.$fid;		
		db_query($query);  

		$query = 'delete FROM addressbook_member WHERE fid='.$fid;	
		db_query($query);  
  
		$query = 'SELECT picture FROM addressbook_picture WHERE mid=0 and fid='.$fid;
		$queryResult = db_query($query);
		while ($data = db_fetch_object($queryResult)) {
			unlink(ADDRESSBOOK_IMAGE_DIR.'/'.$data->picture);
			unlink(ADDRESSBOOK_THUMBNAILS_DIR.'/'.$data->picture);
		}
	
		$query = 'delete FROM addressbook_picture WHERE fid='.$fid;	
		db_query($query);  
	
		// *****************************
		// Create message
		// *****************************
	
		$msg .= t('Family').' '.$fid.' '.t('deleted!');
		drupal_set_message($msg, 'status');
		watchdog('user', $msg);

		// Update drupal module variable
		variable_set("addressbook_last_upload_date",date("d-m-Y"));
		
		return 0;
	}
	
	return 1;		
}

// *********************************************************************************
// The end
// *********************************************************************************