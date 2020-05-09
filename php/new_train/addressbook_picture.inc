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
 
 
 
// ********************************************
// Picture methods
// ********************************************

function addressbook_input_picture($id, $value, $readonly) {
	if (!$readonly) {
		return '<input name="'.$id.'" size="60" type="file" value="'.$value.'"/>'; 
	}
}

function addressbook_check_family_picture() {

	$returnValue=false;
	
	// get fid from session scope
	$fid=$_POST["fid"];
	if ($fid=="") {
		$fid=0;
	}
	addressbook_debug_param("fid",$fid);
  
	if ($fid!=0) {
		$query = 'SELECT picture FROM addressbook_picture WHERE fid='.$fid;
		addressbook_debug_sql($query);
		$queryResult = db_query($query);
		$data = db_fetch_object($queryResult);
		
		if ($data->picture!="") {
			// Construct a valid drupal path for the named file
			$file=ADDRESSBOOK_THUMBNAILS_DIR.'/'.$data->picture;
			$target = file_create_path($file);

			// Check to see if the file exists
			if (file_exists($target)) {
				$returnValue=true;
			} 
		}
	} 
	
	return $returnValue;
}

function addressbook_check_member_picture() {

	$returnValue=false;
	
	// get mid from session scope
	$mid=$_POST["mid"];
	if ($mid=="") {
		$mid=0;
	}
	addressbook_debug_param("mid",$mid);

	if ($mid!=0) {
		$query = 'SELECT picture FROM addressbook_picture WHERE mid='.$mid;
		addressbook_debug_sql($query);
		$queryResult = db_query($query);
		$data = db_fetch_object($queryResult);
		
		if ($data->picture!="") {
			// Construct a valid drupal path for the named file
			$file=ADDRESSBOOK_THUMBNAILS_DIR.'/'.$data->picture;
			$target = file_create_path($file);

			// Check to see if the file exists
			if (file_exists($target)) {
				$returnValue=true;
			} 
		}
	} 
	
	return $returnValue;
}

/**
 * Load picture
 */
function addressbook_load_picture($filename, $height, $width, $align, $lightbox) {
	
	if ($filename!="") {
	
		// Construct a valid drupal path for the named file
		$file=ADDRESSBOOK_THUMBNAILS_DIR.'/'.$filename;
		$file_normal=ADDRESSBOOK_IMAGE_DIR.'/'.$filename;
		$target = file_create_path($file);
		$target_normal = file_create_path($file_normal);

		// Check to see if the file exists
		if (file_exists($target)) {	
			$filename=str_replace(array("?q="), "", url($file));
			$filename_normal=str_replace(array("?q="), "", url($file_normal));
			if ($lightbox) {
				$page.='<a href="'.$filename_normal.'" rel="lightbox" >';
			}
			$page.='<img align="'.$align.'" ';
			if ($width!=0) {
				$page.='width="'.$width.'" ';
			}
			if ($height!=0) {
				$page.='height="'.$height.'" ';
			}
			$page.='src="'.$filename.'" />';
			if ($lightbox) {
				$page.='</a>';
			}
		}
	}
	return $page;
}

/**
 * Save picture
 */
function addressbook_save_picture() {

	global $user;

	// get fid from session scope
	$fid=$_POST["fid"];
	if ($fid=="") {
		$fid=0;
	}
	addressbook_debug_param("fid",$fid);
  
	// get fid from session scope
	$mid=$_POST["mid"];
	if ($mid=="") {
		$mid=0;
	}
	addressbook_debug_param("mid",$mid);
  
	// Fetch Image upload in a HTTP request
	if ($_FILES['uploadedfile']['name']!='') {
		  
		if ( (strstr($_FILES['uploadedfile']['name'],'.jpg')) || 
				(strstr( $_FILES['uploadedfile']['name'],'.JPG')) ) {
			
			$filename= date('YmdHis', time()).'.jpg';
	
			$query = 'SELECT picture FROM addressbook_picture WHERE fid='.$fid.' and mid='.$mid;
			$queryResult = db_query($query);
			$data = db_fetch_object($queryResult);

			// Check if picture not exist (reload protection)
			if (strlen($data->picture)==0) {
  
				$query  = 'INSERT INTO addressbook_picture (fid,mid,picture,uid,last_updated) VALUES (';
				$query .= '"'.$fid.'",';
				$query .= '"'.$mid.'",';
				$query .= '"'.$filename.'",';
				$query .= '"'.$user->uid.'",';
				$query .= 'SYSDATE() )';
				db_query($query);
		
				$error='0';
				if(!is_dir(ADDRESSBOOK_IMAGE_DIR)) {
					if( !mkdir(ADDRESSBOOK_IMAGE_DIR) ) {
				
						$msg .= t('Creating image directory failed!');
						drupal_set_message($msg, 'error');
						$error='1';
					}
				}
			
				// Check dimensions of image
				$dimensions = getimagesize($_FILES['uploadedfile']['tmp_name']);
				$width  = $dimensions[0];
				$height = $dimensions[1];
				$resize = 0;

				// Limit picture 
				if ($width>$height) {
			
					if ($width>0) {
						$ratio = 800 / $width;
					}
				
					if ($ratio < 1) {
						$width=800;
						$height=$height*$ratio;
						$resize = 1;
					}
				} else  {
					if ($width>0) {
						$ratio = 600 / $width;
					}
					if ($ratio < 1) {
						$width=600;
						$height=$height*$ratio;
						$resize = 1;
					}
				}

				// Resize uploaded picture
				if ( $resize == 1 ) {
					if (!image_scale($_FILES['uploadedfile']['tmp_name'], 
							file_create_path(ADDRESSBOOK_IMAGE_DIR.'/'.$filename), $width, $height)) {
				
						$msg .= t('Image file size to big!');
						drupal_set_message($msg, 'error');
					
						$error='1';
					}
				} else {
					// Not resize needed, move file to storage place
					copy($_FILES['uploadedfile']['tmp_name'],ADDRESSBOOK_IMAGE_DIR.'/'.$filename);
				}

				if ($error==0)	{
					chmod(ADDRESSBOOK_IMAGE_DIR.'/'.$filename, 0666);
				
					// Create thumbnails directory store
					if(!is_dir(ADDRESSBOOK_THUMBNAILS_DIR)) {
						if( !mkdir(ADDRESSBOOK_THUMBNAILS_DIR) )	{
					
							$msg .= t('Creating image directory failed!');
							drupal_set_message($msg, 'error');
					
							$error='1';
						}
					}

					$resize = 0;

					// Limit picture size on screen
					if ($width>$height) {
						if ($width>0) $ratio  = 160 / $width;
						if ($ratio < 1) {
							$width=160;
							$height=$height*$ratio;
							$resize = 1;
						}
					} else {
						if ($width>0) $ratio  = 120 / $width;
						if ($ratio < 1) {
							$width=120;
							$height=$height*$ratio;
							$resize = 1;
						}
					}

					// Create thumbnail picture
					// Resize uploaded picture
					if ( $resize == 1 ) {
						if (!image_scale(ADDRESSBOOK_IMAGE_DIR.'/'.$filename, 
							file_create_path(ADDRESSBOOK_THUMBNAILS_DIR.'/'.$filename), $width, $height)) {
					
							$msg .= t('Image file size to big');
							drupal_set_message($msg, 'error');
			
							$error='1';
						}
					} else {
						// Not resize needed, move file to storage place
						copy(ADDRESSBOOK_IMAGE_DIR.'/'.$filename, ADDRESSBOOK_THUMBNAILS_DIR.'/'.$filename);
					}

					if ($error=='0') {
				
						chmod(ADDRESSBOOK_THUMBNAILS_DIR.'/'.$filename, 0666);
				
						// Update drupal module variable
						variable_set("addressbook_last_upload_date",date("d-m-Y"));
  
						$msg .= t('Image is saved');
						drupal_set_message($msg, 'status');
					}
				}
			} 
			
		} else {
		
			$msg .= t('Only jpg format is supported.');
			drupal_set_message($msg, 'error');
		}
	}
	
	// Clear uploaded file
	$_FILES['uploadedfile']['name']='';
}

/** 
 * Delete picture 
 * 
 * @return true (delete family phote) or false (delete member phote)
 */
function addressbook_picture_delete() {

	// *****************************
	// Get posted Inputs
	// *****************************
	
	$found=false;
	
	$fid=$_POST["fid"];
	if ($fid=="") {
		$fid=0;
	}
	addressbook_debug_param("fid",$fid);
  
	// get fid from session scope
	$mid=$_POST["mid"];
	if ($mid=="") {
		$mid=0;
	}
	addressbook_debug_param("mid",$mid);

	if ($fid!=0) {
		$query = 'SELECT picture FROM addressbook_picture WHERE fid='.$fid;
	} else {
		$query = 'SELECT picture FROM addressbook_picture WHERE mid='.$mid;
	}
	
	// In old addressbook version more image could be connected to one family.
	// therefor we must delete all related image files.
	$queryResult = db_query($query);
	while ($data = db_fetch_object($queryResult)) {
		unlink(ADDRESSBOOK_IMAGE_DIR.'/'.$data->picture);
		unlink(ADDRESSBOOK_THUMBNAILS_DIR.'/'.$data->picture);
		$found=true;
	}
	
	if ($fid!=0) {
		$query = 'delete FROM addressbook_picture WHERE fid='.$fid;	
	} else { 
		$query = 'delete FROM addressbook_picture WHERE mid='.$mid;
	}
	db_query($query);  
	
	// *****************************
	// Create message
	// *****************************
	if ($found) {
		$msg .= t('Picture is deleted!');
		drupal_set_message($msg, 'status');
		watchdog('user', $msg);
	}
	
	if ($fid!=0) {
		return true;
	} else {
		return false;
	}
}

// ********************************************
// The End 
// ********************************************