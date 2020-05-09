<?php
require_once("../../prepend.inc");
/**
 * The version list page
 * 
 * @license http://www.browsercrm.com/licence.php  BrowserCRM Software Licence
 * @copyright Copyright (c) 2003 - 2007 BrowserCRM Limited
 * @link http://www.browsercrm.com/
 * @package Documents
 */

$version_list = getWebMATObject(
	'WebMATList', 
	'Documents', 
	array(
		'table' => 'version',
		'form_values' => $_GET
	)
);

$version_list->db_functions->setMasterIds($version_list->form_values['parent_id']);
$version_list->main();


?>
