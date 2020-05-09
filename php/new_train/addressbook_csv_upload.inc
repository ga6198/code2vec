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

/**
 * Render a page for the csv addressbook upload
 * @return HTML
 */
 
 function addressbook_upload_csv() {

	//only addressbook administrators can access the functionality
	if (!user_access('administer addressbook')) {
		return drupal_access_denied();;
	}
  		
	// Breadcrumb menu
	$node["child1"] = t('Addressbook csv upload');
	$page .= addressbook_breadcrumb($node);
	
	drupal_set_title(t('Addressbook upload'));
	
	$page .= '<form enctype="multipart/form-data" name="OostpoortForm" method="POST" >';
	$page .= '<div class="addressbook">';
	
	$page .= addressbook_save_csv();
		
	$page .= '<fieldset>' ;
	$page .= '<legend>'.t('Addressbook file upload').'</legend>';	
	$page .= '<div class="form-item">';
	$page .= '  <label>'.t('File').':</label>';
	$page .= '  <input name="uploadedfile" type="file" size="100" onChange="submit();" />';
	$page .= '  <div class="description">';
	$page .= t('Only CSV file format is supported and maximum file is ').ini_get('upload_max_filesize').'B';
	$page .= '  </div>';
	$page .= '</div>';
	$page .= '</fieldset>' ;  
	
	$page .= '<a href="'.url(URL_ADDRESSBOOK).'">'.t('Back').'</a>';
	
	$page .= '</div>';
	$page .= '</form>';  
		
	print theme("page", $page);
}

function addressbook_save_csv() {

	// Fetch Image upload in a HTTP request
	if ($_FILES['uploadedfile']['tmp_name']!='')  {
	
		if ( (strstr($_FILES['uploadedfile']['name'],'.csv')) || 
		     (strstr( $_FILES['uploadedfile']['name'],'.CSV')) ) {
	
			$page .= addressbook_process_csv($_FILES['uploadedfile']['tmp_name']);
			
		} else {
				
			$msg = t("Only CSV file format is supported.");
			drupal_set_message($msg, 'error');
	
			$_FILES['uploadedfile']['name']='';
		}
	}
	return $page;
}
 
/**
 * process csv upload file.
 * @return errors in HTML format
 */
function addressbook_process_csv($filename) {

  global $user;

  $line_counter=0;
  $csv_file_error=0;

  $fh = fopen($filename, 'r');

  // Header line, skip it!
  $theData = fgets($fh);
  $line_counter++;

  $theData = fgets($fh);
  $line_counter++;
  
  while ($theData!="") {
    $line_error=0;

	// *******************************
    // Conver data
    // *******************************
	
	$value=split( ',' ,$theData);
    
	$data["first_name"]=ucfirst($value[0]);
    
	$data["middle_name"]=$value[1];
    
	$data["last_name"]=ucfirst($value[2]);
    
	$data["street"]=ucfirst($value[3]);
    
	$data["zipcode"]=$value[4];
    
	$data["city"]=ucfirst($value[5]);
    
	$data["country"]=$value[6];
    
	$data["telephone"]=$value[7];
	
    $data["mobile"]=$value[8];
	
    $data["email"]=$value[9];

    $data["birthday"]=$value[10];

    $tmp=str_replace(array("~~"), "\r\n", $value[11]);
    $data["work"]=$tmp;

    $tmp=str_replace(array("~~"), "\r\n", $value[12]);
    $data["notes"]=$tmp;

    $tmp=str_replace(array("~~"), "\r\n", $value[13]);
    $data["active_roles"]=$tmp;

    $tmp=str_replace(array("~~"), "\r\n", $value[14]);
    $data["wanted_roles"]=$tmp;

	// *******************************
    // Validate data
    // *******************************
	
    if ($data["first_name"]=='') {
		$msg = t('Line').' ['.$line_counter.'] '.t('First name empty');
		drupal_set_message($msg, 'error');
		$line_error=1;
    }
	
    if ($data["last_name"]=='') {
		$msg = t('Line').' ['.$line_counter.'] '.t('Last name empty');
		drupal_set_message($msg, 'error');
		$line_error=1;
    }
	
	if ($data["street"]=='') {
		$msg = t('Line').' ['.$line_counter.'] '.t('street empty');
		drupal_set_message($msg, 'error');
		$line_error=1;
    }
	
	if ($data["zipcode"]=='') {
		$msg = t('Line').' ['.$line_counter.'] '.t('zipcode empty');
		drupal_set_message($msg, 'error');
		$line_error=1;
    }
	
	// *******************************
    // Process data
    // *******************************

	if ($line_error==0) {
	
		$data["fid"]=addressbook_family_csv_update($data);
		addressbook_member_csv_update($data);     
	}
	   
	$theData = fgets($fh);
	$line_counter++; 
  }
  fclose($fh);

  // Delete csv file
  unlink($filename);
  
  // Update drupal module variable
  variable_set("addressbook_last_upload_date",date("d-m-Y"));
	
}


function addressbook_family_csv_update($data) {
	
	// *****************************
	// Update Database
	// *****************************

	// Check if family all exist
	$query = '
		select 
			fid 
		from 
			addressbook_family 
		where 
			zipcode like "'.$data["zipcode"].'" and 
			street like "'.$data["street"].'"';
			
	$queryResult = db_query($query);
	$tmp = db_fetch_object($queryResult);
	
	if ($tmp->fid=="") {
		
		$query  = 'INSERT INTO addressbook_family 
					(middle_name, 
					last_name, 
					street, 
					zipcode, 
					city,
					country, 
					telephone, 
					last_updated) VALUES (';
		$query .= '"'.$data["middle_name"].'",';
		$query .= '"'.$data["last_name"].'",';
		$query .= '"'.$data["street"].'",';
		$query .= '"'.$data["zipcode"].'",';
		$query .= '"'.$data["city"].'",';
		$query .= '"'.$data["country"].'",';
		$query .= '"'.$data["telephone"].'",';
		$query .= 'SYSDATE() )';

		db_query($query);
	
		// Check new family entry.
		$query = '	select 
						fid 
					from 
						addressbook_family 
					where 
						zipcode like "'.$data["zipcode"].'" and 
						street like "'.$data["street"].'"';
			
		$queryResult = db_query($query);
		$tmp = db_fetch_object($queryResult);
		
		// *****************************
		// create message
		// *****************************
	
		$msg .= t('Family').' '.$tmp->fid.' '.t('created!');
		drupal_set_message($msg, 'status');
		watchdog('user', $msg);
		
	} else {
		
		// *****************************
		// Update Database
		// *****************************
	
		$query = 'UPDATE addressbook_family SET ';
		$query .= 'middle_name = "'.$data['middle_name'].'",';
		$query .= 'last_name = "'.$data["last_name"].'",';
		$query .= 'street = "'.$data["street"].'",';
		$query .= 'zipcode = "'.$data["zipcode"].'",';
		$query .= 'city = "'.$data["city"].'",';
		$query .= 'country = "'.$data["country"].'",';
		$query .= 'telephone = "'.$data["telephone"].'",';
		$query .= 'uid = "'.$data["owner"].'",';
		$query .= 'last_updated = SYSDATE() ';
		$query .= 'WHERE fid='.$tmp->fid;

		db_query($query);
	
		// *****************************
		// create message
		// *****************************
	
		$msg .= t('Family').' '.$tmp->fid.' '.t('updated');
		drupal_set_message($msg, 'status');
		watchdog('user', $msg);
		
	}
	return $tmp->fid;
}


function addressbook_member_csv_update($data) {

	// Check if user allready exist. Protect against reload button
	$query  = '
		select
			a.mid as mid 
		from 
			addressbook_member a 
		where ' ;
	$query .= ' a.first_name="'.$data["first_name"].'" and ';
	$query .= ' a.middle_name="'.$data["middle_name"].'" and ';
	$query .= ' a.last_name="'.$data["last_name"].'"';
	
	addressbook_debug_sql($query);
	$queryResult = db_query($query);     
	$tmp = db_fetch_object($queryResult);
	
	if ($tmp->mid=="") {
	
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
				fid,
				last_updated) VALUES (';
		$query .= '"'.$data["first_name"].'",';
		$query .= '"'.$data["middle_name"].'",';
		$query .= '"'.$data["last_name"].'",';
		$query .= '"'.addressbook_convert_date_reverse($data["birthday"]).'",';
		$query .= '"'.$data["mobile"].'",';
		$query .= '"'.$data["email"].'",';
		$query .= '"'.$data["notes"].'",';
		$query .= '"'.$data["work"].'",';
		$query .= '"'.$data["active_roles"].'",';
		$query .= '"'.$data["wanted_roles"].'",';
		$query .= '"'.$data["fid"].'",';
		$query .= 'SYSDATE() )';
		
		addressbook_debug_sql($query);
		db_query($query);
		
		$query  = '
		select
			a.mid as mid 
		from 
			addressbook_member a 
		where ' ;
		$query .= ' a.first_name="'.$data["first_name"].'" and ';
		$query .= ' a.middle_name="'.$data["middle_name"].'" and ';
		$query .= ' a.last_name="'.$data["last_name"].'"';
	
		addressbook_debug_sql($query);
		$queryResult = db_query($query);     
		$tmp = db_fetch_object($queryResult);
		
		// *****************************
		// create message
		// *****************************
	
		$msg .= t('Member').' '.$tmp->mid.' '.t('created!');
		drupal_set_message($msg, 'status');
		watchdog('user', $msg);
		
	
	} else {
	
		$query = 'UPDATE addressbook_member SET ';
		$query .= 'first_name = "'.$data["first_name"].'",';
		$query .= 'middle_name = "'.$data["middle_name"].'",';
		$query .= 'last_name = "'.$data["last_name"].'",';
		$query .= 'birth_day = "'.addressbook_convert_date_reverse($data["birthday"]).'",';
		$query .= 'mobile = "'.$data["mobile"].'",';
		$query .= 'email = "'.$data["email"].'",';
		$query .= 'notes = "'.$data["notes"].'",';
		$query .= 'work = "'.$data["work"].'",';
		$query .= 'active_roles = "'.$data["active_roles"].'",';
		$query .= 'wanted_roles = "'.$data["wanted_roles"].'",';
		$query .= 'fid = "'.$data["fid"].'",';
		$query .= 'last_updated = SYSDATE() WHERE mid='.$tmp->mid;

		db_query($query);		
		addressbook_debug_sql($query);

		// *****************************
		// create message
		// *****************************
	
		$msg .= t('Member').' '.$tmp->mid.' '.t('updated');
		drupal_set_message($msg, 'status');
		watchdog('user', $msg);	
	}	
}