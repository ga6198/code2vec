<?php
class Search{
	// define search interface objects arrays
	private static $opt_search_selection_array = array(
										'forum'	=>	'Forum',
										'article'=>	'Articles'
										);
	
	private static $select_search_sort_array = array(
									'post_date'=>'Date',
									'post_rate'=>'Rate'	
									);									

	private static $check_search_display_field_array_common = array(
									'post_rate'=> 'Ratings',
									'title'=>'Post Title'
										);								

	public static function getSearchSelectionOptions($selected_values){
		
		if($selected_values==""){
			$selected_values='forum';
		}
		$html = '
				<table width="70%" border="0" align="center" cellpadding="0" cellspacing="0">
				 	<tr>';
		
		foreach(self::$opt_search_selection_array as $opt_key => $opt_value){
			$selected = "";
			if($opt_key==$selected_values){
				$selected = 'checked="checked"';
			}
			$html .= '
						<td width="16%" align="left" valign="bottom">
							<input type="radio" name="opt_search_select" '.$selected.' class="styled" value="'.$opt_key.'"/>
				            <span class="TextBoldDark">'.$opt_value.'</span> 
				      	</td>
					';
		} 
		
		return $html .='
					</tr>
				</table>
				';
/*		
			           		<table width="70%" border="0" align="center" cellpadding="0" cellspacing="0">
				               <tr>
				                 <td width="16%" align="left" valign="bottom"><input type="radio" name="opt_search_select"  class="styled" value="all" />
				                     <span class="TextBoldDark">All</span> 
				                 </td>
				               </tr>
			           		</table>
*/
	}								
	public static function getSearchSortSelection($selected_values){
		if($selected_values==""){
			$selected_values='post_date';
		}
		$html = '
				<select class="SelcetBox" name="select_sort" tabindex="2" >
				';
		
		foreach(self::$select_search_sort_array as $opt_key => $opt_value){
			$selected = "";
			if($opt_key==$selected_values){
				$selected = 'selected="selected"';
			}
			$html .= '
						<option value="'.$opt_key.'" '.$selected.' >'.$opt_value.'</option>
					';
		} 
		
		return $html .='
					</select>
					';	
		
/*			
<select class="myselectbox" name="select_sort" tabindex="2" style="width: 50px">
   <option value="post_date">Date</option>
   <option value="post_rate">Rate</option>
</select>

*/
	}	
	
	public static function getSearchDisplayFiledCheckBoxs($selected_values, $selectMode){
		
		$selectMode=="" ? $selectMode="forum" : $selectMode;
		
		switch ($selectMode){
			case 'forum':
				$html = self::getdisplayFieldCommon($selected_values);
				break;
				
			case 'article':	
				$html = self::getdisplayFieldCommon($selected_values);
				break;
				
			case 'posts':	
				$html = self::getdisplayFieldCommon($selected_values);
				break;

			case 'users':	
				$html = self::getdisplayFieldCommon($selected_values);
				break;
				
		}
		
		return $html;	
	}	

	private static function getdisplayFieldCommon(&$selected_values){
		if(empty($selected_values)){
			$selected_values =array_keys(self::$check_search_display_field_array_common);
		}
		$html = "";
		foreach(self::$check_search_display_field_array_common as $opt_key => $opt_value){
			$selected = "";
			if( in_array($opt_key,$selected_values) ){
				$selected = 'checked="checked"';
			}
			$html .= '<td width="50%" align="left" valign="top">
							<input type="checkbox" name="search_display_field[]" value="'.$opt_key.'" '.$selected.' class="styled" />
										<span class="TextSmallDark">'.$opt_value.'</span>
					</td>';
		} 
		
		return $html;		
/*		
								<td width="50%" align="left" valign="top">
									<input type="checkbox" name="search_display_field[]" value="post_rate" checked="checked" class="styled" />
										<span class="TextSmallDark">Ratings</span> 
								</td>
								<td width="50%" align="left" valign="top">
									<input type="checkbox" name="search_display_field[]" value="title" checked="checked" class="styled" /> 
								 	<span class="TextSmallDark">Post Title </span>
								</td>
*/
	} 
	
	//get category structure for search criteria
	public static function getSearchCategoryList($selected_cat_array = array()){
		//$selected_cat_array - array contain all the values alrady selected
		$html = '
		<div class="SearchCategories">
			<div class="SearchControls">
	  			<table width="100%" border="0" cellpadding="0" cellspacing="0">
        			<tr>
          				<td width="99"><!--<a class="button" id="check_all_cat" href="#" onclick="this.blur(); return false;"><span>Check All </span></a>--></td>
          				<td width="145"><!--<a class="button" id="uncheck_all_cat" href="#" onclick="this.blur(); return false;"><span>Unckeck All </span></a>--></td>
          				<td width="136" align="right"> <a id="close_search_cat" href="javascript:void(0)" class="Link">Close [X]</a></td>
        			</tr>
      			</table>
			</div>
			<div class="SearchCategorList">';
		$main_cat_array = Category::FindAll("parent_category_id=0", "*", array('order_id'));
		
		if(empty($main_cat_array)){
			$html .= "
				<ul>
					<li>
						Oppps..! No category found..! 
					</li>
				</ul>
			</div>
		</div>		
			";
			return $html;
		}
		
		foreach ($main_cat_array as $main_cat_object){
			//check for main category check
				
			$html .= "
					
					<ul>
						<span>
							<h2>".$main_cat_object->getCategoryName()."</h2>
						</span>
						";
						
			$sub_cat_array = $main_cat_object->getSubCategories();
			if(empty($sub_cat_array)){
				$html.="
						<li>
							No sub Category found..!	
						</li>
				";
			}
			foreach ($sub_cat_array as $sub_cat_object){
				//check for already checked items in the POST 
				$checked = "";
				if(!empty($selected_cat_array)){
					if( in_array( $sub_cat_object->getCategoryId(), array_values($selected_cat_array) ) ){
						$checked = "checked='checked'";
					}
				}
				$html.='
					<li>
					<input name="search_categories[]" value="'.$sub_cat_object->getCategoryId().'" class="styled" type="checkbox" '.$checked.'>							<span>'.$sub_cat_object->getCategoryName().'</span>						
					</li>				
				';
			}
			$html .= "</ul>";
		}
		$html .= "
			</div>
		</div>";
		
		return $html;
	}

	//return teh search result
	public static function getSearchCriteria($search_selection, &$post_array){
		switch ($search_selection){
			case 'forum':
				$sql = self::getSearchSQLforFORUM($post_array);
				break;
				
			case 'article':
				$sql = self::getSearchSQLforARTICLE($post_array);
				break;
				
		}
		
		return $sql;
	}

	//search sql genarate for forum search
	private static function getSearchSQLforFORUM(&$post_array){
		//common for all, posts, topics 
		$table_selection = 'posts';
		
		//search text sql string
		$author_sql = "";
		if($post_array['txt_author']!=""){
			$user_id =  User::getUserIdbyName($post_array['txt_author']);
			$author_sql = " AND user_id=$user_id";
		}
		
		
		// build string for main search keyword
		$search_text_sql = "";
		if($post_array['txtsearch']!=""){
			$search_text_sql = " AND (post LIKE '%" . $post_array['txtsearch']."%' OR title LIKE '%" . $post_array['txtsearch']."%') ";
		}
		
		//build search category string	
		$category_sql = "";
		if(isset($post_array['search_categories'])){
			$search_categories = $post_array['search_categories'];
			
			foreach ($search_categories as $key=>$value){
				$category_array[] = "category_id='$value' ";
			}	
			
			$category_sql = " AND (" . implode("OR ", $category_array) .")";
		}
		
		$final_sql = " $author_sql $search_text_sql $category_sql ";	
		
		//compulsory where to fit in with post class find all params
		$compulsory_where = "  parent_post_id=0 AND  status=1";
		$where_sql = $compulsory_where . $final_sql;					
		
		//assign session variables for strore search values that need to reload in the pagiing
		return  array(
						"where_sql"=>$where_sql,
						"select_sort"=>$post_array['select_sort'],
						"search_table"=>$table_selection
					);						
	}

	//search sql genarate for forum search
	private static function getSearchSQLforARTICLE(&$post_array){
		//common for article text, article title 
		$table_selection = 'article';
		
		// build string for main search keyword
		$search_text_sql = "";

		if($post_array['txtsearch']!=""){
			$search_text_sql = "  (article_revision.article_title LIKE '%" . $post_array['txtsearch']."%' OR article_revision.article_content LIKE '%" . $post_array['txtsearch']."%') AND article_revision.status='"._ACTIVE."'  AND article.status='"._ACTIVE."' AND article.article_id=article_revision.article_id";
		}
		
		$final_sql = "$search_text_sql";	
		
		//compulsory where to fit in with post class find all params
		//$compulsory_where = " status=1";
		$where_sql = $final_sql;					
		
		//assign session variables for strore search values that need to reload in the pagiing
		return  array(
						"where_sql"=>$where_sql,
						"select_sort"=>'no_views',
						"search_table"=>$table_selection
					);						
	}
	
	
	//search sql genarate for all search
	private static function getSearchSQLforALL(&$post_array){
		//common for all, posts, topics 
		$table_selection = 'posts';
		
		//search text sql string
		$author_sql = "";
		if($post_array['txt_author']!=""){
			$user_id =  User::getUserIdbyName($post_array['txt_author']);
			$author_sql = " AND user_id=$user_id";
		}
		
		
		// build string for main search keyword
		$search_text_sql = "";
		if($post_array['txtsearch']!=""){
			$search_text_sql = " AND (post LIKE '%" . $post_array['txtsearch']."%' OR title LIKE '%" . $post_array['txtsearch']."%') ";
		}
		
		//build search category string	
		$category_sql = "";
		if(isset($post_array['search_categories'])){
			$search_categories = $post_array['search_categories'];
			
			foreach ($search_categories as $key=>$value){
				$category_array[] = "category_id='$value' ";
			}	
			
			$category_sql = " AND (" . implode("OR ", $category_array) .")";
		}
		
		$final_sql = " $author_sql $search_text_sql $category_sql ";	
		
		//compulsory where to fit in with post class find all params
		$compulsory_where = " parent_post_id=0 AND status=1";
		$where_sql = $compulsory_where . $final_sql;					
		
		//assign session variables for strore search values that need to reload in the pagiing
		return  array(
						"where_sql"=>$where_sql,
						"select_sort"=>$post_array['select_sort']
					);						
	}
	
	//search sql genarate for topics
	private static function getSearchSQLforTOPICS(&$post_array){
		//common for all, posts, topics 
		$table_selection = 'posts';
		
		//search text sql string
		$author_sql = "";
		if($post_array['txt_author']!=""){
			$user_id =  User::getUserIdbyName($post_array['txt_author']);
			$author_sql = " AND user_id=$user_id";
		}
		
		// build string for main search keyword
		$search_text_sql = "";
		if($post_array['txtsearch']!=""){
			$search_text_sql = " AND (title LIKE '%" . $post_array['txtsearch']."%') ";
		}

		
		//build search category string	
		$category_sql = "";
		if(isset($post_array['search_categories'])){
			$search_categories = $post_array['search_categories'];
			
			foreach ($search_categories as $key=>$value){
				$category_array[] = "category_id='$value' ";
			}	
			
			$category_sql = " AND (" . implode("OR ", $category_array) .")";
		}
		
		$final_sql = " $author_sql $search_text_sql $category_sql ";	
		
		//compulsory where to fit in with post class find all params
		$compulsory_where = " parent_post_id=0 AND  status=1";
		$where_sql = $compulsory_where . $final_sql;					
		
		//assign session variables for strore search values that need to reload in the pagiing
		return  array(
						"where_sql"=>$where_sql,
						"select_sort"=>$post_array['select_sort']
					);						
	}
	
	//search sql genarate for all Posts
	private static function getSearchSQLforPOSTS(&$post_array){
		//common for all, posts, topics 
		$table_selection = 'posts';
		
		//search text sql string
		$author_sql = "";
		if($post_array['txt_author']!=""){
			$user_id =  User::getUserIdbyName($post_array['txt_author']);
			$author_sql = " AND user_id=$user_id";
		}
		
		// build string for suthor
		$search_text_sql = "";
		if($post_array['txtsearch']!=""){
			$search_text_sql = " AND (post LIKE '%" . $post_array['txtsearch']."%') ";
		}
		
		//build search category string	
		$category_sql = "";
		if(isset($post_array['search_categories'])){
			$search_categories = $post_array['search_categories'];
			
			foreach ($search_categories as $key=>$value){
				$category_array[] = "category_id='$value' ";
			}	
			
			$category_sql = " AND (" . implode("OR ", $category_array) .")";
		}
		
		$final_sql = " $author_sql $search_text_sql $category_sql ";	
		
		//compulsory where to fit in with post class find all params
		$compulsory_where = " parent_post_id=0  AND  status=1";
		$where_sql = $compulsory_where . $final_sql;					
		
		//assign session variables for strore search values that need to reload in the pagiing
		return  array(
						"where_sql"=>$where_sql,
						"select_sort"=>$post_array['select_sort']
					);						
	}
	
	public static function displaySearchForm(&$post_array){
		//values will be assigned to teh smarty template directly from here
		//print_r($post_array);
		//get category list
                $search_form_elements = array();
		$selected_search_categories = array();
		if(isset($post_array['search_categories'])){
			$selected_search_categories = $post_array['search_categories'];
		}
		$search_form_elements['search_categories'] =self::getSearchCategoryList($selected_search_categories);
		
		//get search text
		if(isset($post_array['txtsearch'])){
		$search_form_elements['txtsearch'] = $post_array['txtsearch'];
		}else{
                $search_form_elements['txtsearch'] = "";
                }
		//get author
		if(isset($post_array['txt_author'])){
		$search_form_elements['txt_author'] = $post_array['txt_author'];
		}else{
                $search_form_elements['txt_author'] = "";
                }
		
		//earch selection all post topics users
		$opt_search_select = "";
		if(isset($post_array['opt_search_select'])){
			$opt_search_select = $post_array['opt_search_select'];
		}
		$search_form_elements['search_selection'] = self::getSearchSelectionOptions($opt_search_select);
		
		//earch sort drop down date and rate
		$select_search_sort = "";
		if(isset($post_array['select_sort'])){
			$select_search_sort = $post_array['select_sort'];
		}
		$search_form_elements['search_sort'] = self::getSearchSortSelection($select_search_sort);
		
		//earch display fiels
		$check_search_display_field = array();
		if(isset($post_array['search_display_field'])){
			$check_search_display_field = $post_array['search_display_field'];
		}
		
		$search_form_elements['search_display_field'] = self::getSearchDisplayFiledCheckBoxs($check_search_display_field, $opt_search_select);

                return $search_form_elements;
	}
	public static function autocomplete($text)
		{
			$wh="title LIKE '%".$text."%' AND parent_post_id=0";
	
			$arr =  Post::FindAll($wh);
			return $arr;
		}
}
?>