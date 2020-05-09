1<?php


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
 
 function addressbook_map_view() {

	// *****************************
	// Get posted Inputs
	// *****************************
	
	$fid=$_POST["fid"];
	addressbook_debug_param("fid", $fid);
	
	// *****************************
	// Query Database
	// *****************************
	
	$query  = '
			SELECT 
				b.last_name as last_name,
				b.middle_name as middle_name,
				b.street as street, 
				b.city as city, 
				b.country as country
			FROM 
				addressbook_family b
			WHERE 
				b.fid='.$fid;
		
	$queryResult = db_query($query);
	$data = db_fetch_object($queryResult);
	
	// *****************************
	// Create page
	// *****************************
  
	drupal_set_html_head('<script type=\'text/javascript\' src=\'http://api.map24.com/ajax/1.2/?init=default\'></script>');
	drupal_set_html_head('<script type=\'text/javascript\'>');
	drupal_set_html_head('var map = null;');

	drupal_set_html_head('function goMap24() {');
	drupal_set_html_head('var mrcContainer = new Map24.Webservices.Request.MapletRemoteControl(map);');

	drupal_set_html_head('map = Map24.Webservices.getMap24Application({');
	drupal_set_html_head('AppKey: "'.variable_get('addressbook_map_key', '').'",');
	drupal_set_html_head('MapArea: document.getElementById( "maparea" ),');
	drupal_set_html_head('Maptype: "JAVA",');
	drupal_set_html_head('MapWidth: 640,');
	drupal_set_html_head('MapHeight: 480,');
	drupal_set_html_head('MRC: mrcContainer');
	drupal_set_html_head('});');
	drupal_set_html_head('geocode(\''.$data->street.','.$data->city.','.$data->country.'\')');
	drupal_set_html_head('}');

	drupal_set_html_head('function geocode( addressString ){');
	drupal_set_html_head('map.Webservices.sendRequest(');
	drupal_set_html_head('new Map24.Webservices.Request.MapSearchFree(map, {');
	drupal_set_html_head('SearchText: addressString,');
	drupal_set_html_head('MaxNoOfAlternatives: 50');
	drupal_set_html_head('})');
	drupal_set_html_head(');');

	drupal_set_html_head('map.onMapSearchFree = function( event ){');
	drupal_set_html_head('var mrcContainer = new Map24.Webservices.Request.MapletRemoteControl( map );');
	drupal_set_html_head('var firstResult = event.Alternatives[0];');
	drupal_set_html_head('var lon = firstResult.Coordinate.Longitude;');
	drupal_set_html_head('var lat = firstResult.Coordinate.Latitude;');

	drupal_set_html_head('mrcContainer.push(');
	drupal_set_html_head('new Map24.Webservices.MRC.SetMapView({');
	drupal_set_html_head('Coordinates: new Map24.Coordinate( lon, lat ),');
	drupal_set_html_head('ClippingWidth: new Map24.Webservices.ClippingWidth(');
	drupal_set_html_head('{ MinimumWidth: 250 }');
	drupal_set_html_head(')');
	drupal_set_html_head('})');
	drupal_set_html_head(');');
	drupal_set_html_head('map.Webservices.sendRequest( mrcContainer );');
	drupal_set_html_head('}');
	drupal_set_html_head('}');

	drupal_set_html_head('</script>');

	$page.='<body onload=\'goMap24()\'>';

	$page .= '<form name="AddressbookForm" method="POST" >';	
	$page .= '<div class="addressbook">';
	
	drupal_set_title(t('Addressbook map view'));
		
		// Breadcrumb menu
	$node["child1"] = l(t('Family list'),URL_ADDRESSBOOK_FAMILY);
	$node["child2"] = addressbook_hiddenlink('AddressbookForm', 'fid', $fid, 'FamilyView', t("Family view"));
	$node["child3"] = t("Map view");
	addressbook_breadcrumb($node);

	$page .= '<field>';
	
	$page .= '<br/>';
	$page .= '<h4>'.$data->middle_name.' '.$data->last_name.'</h4>';
	$page .= '<h4>'.$data->street.'</h4>';
	$page .= '<h4>'.$data->city.'</h4>';
	$page .= '<h4>'.$data->country.'</h4>';
	$page .= '<br/>';
	
	$page .= '<div id="maparea"></div>';
		
	$page .= '</field>';
	$page .= addressbook_hiddenlink('AddressbookForm','fid',$fid,'FamilyView',t('Back'));
		
	$page .= '</div>';
	$page .= '</form>';
	$page .= '</body>';
		
	return $page;
}


// *********************************************************************************
// The end
// *********************************************************************************