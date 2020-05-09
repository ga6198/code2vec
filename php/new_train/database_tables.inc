<?php
/**
 * A collection of classes extended from WebMATDatabaseTable
 * 
 * You need an extended class for each table in you module not including the 
 * dropdown tables.
 * 
 * @license http://www.browsercrm.com/licence.php  BrowserCRM Software Licence
 * @copyright Copyright (c) 2003 - 2007 BrowserCRM Limited
 * @link http://www.browsercrm.com/
 * @package Documents
 */

/**
 * This class handles the database functions for the item table
 * 
 * This module is an extend verison of WebMATDatabaseTable with the table_name 
 * set and the alert_where set.
 * 
 */
class itemDocumentsDatabaseTable extends WebMATDatabaseTable {
	
	
	/**
	 * The constructor class for itemDocumentsDatabaseTable
	 *
	 * @param string $module_name the module name
	 * @param array $data_definition the data definition
	 */
	function itemDocumentsDatabaseTable($module_name, $data_definition) {
		global $USER;
		//$this->alert_where = 'active=1 AND seen=0 AND user_id=' . $USER['user_id']; //alert where statement
		$this->table_name = 'item';
		$this->WebMATDatabaseTable($module_name, $data_definition); //calls the constructor for WebMATDatabaseTable
	}
	
	// need to ask phil how to change this
	// /**
	 // * returns the 
	 // * 
	 // * 
	 // */
	// function getItemsWithFilename($order_by = false, $limit = false, $where = false) {  //get all
		// global $SERVICES, $USER;
		// 
		// $tables_name_version = str_replace('item', 'version', $this->table_name);
		// $table_name = $this->table_name;
		// $tables = '' . $table_name . ' LEFT JOIN ' . $tables_name_version . ' ON ' .
			// $table_name . '.version_id = ' . $tables_name_version . '.id';
			// 
		// $fields = 
			// $table_name . '.id AS id, ' .
			// $table_name . '.name, ' .
			// $table_name . '.folder_id, ' .
			// $table_name . '.contact_id, ' .
			// $table_name . '.mime_type_id, ' .
			// $tables_name_version . '.id AS version_id';
			// $tables_name_version . '.file_name AS file_name';
			// 
		// 
		// $contact_fields = array();
			// 
		// $tables = $fields = '';	// set in processGetItemsContactFields and processGetItemsForeignFields
		// 
		// $contact_fields = $this->processGetItemsContactFields($tables, $fields);
		// 
		// $foreign_fields = $this->processGetItemsForeignFields($tables, $fields);
		// 
		// $sort = $this->getOrderBy($order_by);
		// if ($where) {
			// $where = $where;
		// } else {
			// $where = $this->getWhereStatement();
		// }
		// $result = $this->db->select($fields, $tables, $where, $sort, '',$limit);
		// if ($this->db->hasError())	$this->db->printError();
		// while ($row = $this->db->getRow($result)) {
			// if ($this->isViewable( $row['id'], $row )) { // Phil
				// $items[$row['id']] = $this->processGetRowContactFields($row, $contact_fields);
				// $items[$row['id']] = $this->processGetRowForeignFields($row, $foreign_fields);
			// }
		// }
	// 
		// return $items;
	// }
}


class versionDocumentsDatabaseTable extends WebMATDatabaseDetailTable {
	
	
	/**
	 * The constructor class for versionDocumentsDatabaseTable
	 *
	 * @param string $module_name the module name
	 * @param array $data_definition the data definition
	 */
	function versionDocumentsDatabaseTable($module_name, $data_definition) {
		global $USER;
		$this->table_name = 'version';
		$this->master_table = 'item';
		$this->WebMATDatabaseTable($module_name, $data_definition);
	}
	/**
         * Creates the where string
         *
         * @return string the where statement
	 *
         */
	
	// Bosco | 20050702 | Overridden To display versions of the selected Document only.
        function getWhereStatement() {
		$where = parent::getWhereStatement();
                if ($this->where_statement) {
			$parent_id = $this->getParentId();
			if($parent_id) {	
                        	return $where." AND Documents__version.item_id = ".$parent_id;
			}
                }		
		return $where;
	}
		
	/**
         * Gets the Parent Id from query string and returns
         *
         * @return integer the parent id
	 *
	 * @author Bosco | 20050702 | To display versions of only the selected Document
         */
	function getParentId() {
		global $SERVICES;
		
		$query_string_array=explode('&',$SERVICES->getGlobal('query_string'));
		if(!empty($query_string_array)) {
			$query_string = array();
			foreach ($query_string_array as $k => $v) {
				list($k,$val) = explode('=',$v);
				$query_string = bcrm_array_merge($query_string,array($k => $val));
			}	
			$parent_id = $query_string['parent_id']?$query_string['parent_id']:($query_string['item[item_id]']?$query_string['item[item_id]']:'');
		}
		return $parent_id;
	}
}

class mime_typeDocumentsDatabaseTable extends WebMATDatabaseDetailTable {
	
	
	/**
	 * The constructor class for versionDocumentsDatabaseTable
	 *
	 * @param string $module_name the module name
	 * @param array $data_definition the data definition
	 */
	function mime_typeDocumentsDatabaseTable($module_name, $data_definition) {
		global $USER;
		$this->table_name = 'mime_type';
		$this->master_table = 'item';
		$this->WebMATDatabaseTable($module_name, $data_definition);
	}
}
?>