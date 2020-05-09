<?php
/**
 * WikidForum - Forum + Wiki
 * Copyright 2008-2011 Cenango Financial LLC
 *
 * includes/search.php
 *
 * Search API define here.
 *
 */


/**
 *Validate search form
 * 
 * @return array
 * error messages returns as an array
 *
 */

function validate_search_form(){
        $message_errors = array();
        //user can not request this page without a post

        if(!isset($_POST['opt_search_select'])){
                $message_errors[] = "Incorrect Action found...!";
                unset($_SESSION['search_params']);
        }
        // need to be filled teh serch text in order to process
        if(!isset($_POST['txtsearch']) || trim($_POST['txtsearch'])==""){
                $message_errors[] = "Enter Search Keyword..!";
                unset($_SESSION['search_params']);
        }
        return $message_errors;

}

/**
 *Get the search parameters based on post and article
 *
 * @param string $opt_search_select. Value should be forum of article
 *
 * @return array
 * array based on submitted data.
 *
 */

function get_search_parameters($opt_search_select){

        switch ($opt_search_select){
                case 'forum':

                       return $search_params = Search::getSearchCriteria('forum', $_POST);
                        break;

                case 'article':
                        return $search_params = Search::getSearchCriteria('article', $_POST);

                        break;
        }

}

/**
 *Get search results based on search criteria.
 * 
 * @param array $search_params.
 * Array elements are;
 *
 * @return array
 * 1. num_of_records
 *  2.pagging – pagination details array
 * 3. results – search results
 * 4. search_category
 *
 */

function get_search_results($search_params){
    $search_result = array();
    $num_records =	14;
    $page	=	isset($_GET['page'])?$_GET['page']:1;
    $start	=	(($page-1)*$num_records);

    if($search_params['search_table'] == 'article'){
            $search_result['search_category'] = 'article';
            $sql_sel_all = $search_params['where_sql'];
            $full_article = Article::search_articles($sql_sel_all);
            //$full_article = Article::FindAll($search_params['where_sql'], "*", array($search_params['select_sort']));
            $tot_articles	=	count($full_article);
            $search_result['num_of_records'] = $tot_articles;

            $pagging = getPaggings(array(
                                                            'num_records'=>$num_records,
                                                            'page'=>$page,
                                                            'start'=>$start,
                                                            'tot_posts'=>$tot_articles
                                                            ));
            $search_result['pagging']  = $pagging;
            $search_sel  = $search_params['where_sql']." ORDER BY ".implode(',',array($search_params['select_sort']))." LIMIT  $start,$num_records";
           // $articles = Article::FindAll_AS_Array($search_params['where_sql'], "*", array($search_params['select_sort']),"A",$start, $num_records);
            $articles = Article::search_articles($search_sel);
            $search_result['results'] = $articles;
           
    }else{
             $search_result['search_category'] = 'post';
            $full_post = Post::FindAll($search_params['where_sql'], "*", array($search_params['select_sort']));
            $tot_posts	=	count($full_post);
            $search_result['num_of_records'] = $tot_posts;
           
            $pagging = getPaggings(array(
                                                            'num_records'=>$num_records,
                                                            'page'=>$page,
                                                            'start'=>$start,
                                                            'tot_posts'=>$tot_posts
                                                            ));
            $search_result['pagging']  = $pagging;
            $posts = Post::FindAll_As_Array($search_params['where_sql'], "*", array($search_params['select_sort']),"A",$start, $num_records);
            $search_result['results'] = $posts;
    }

    return $search_result;
}

/**
 *Get the search result content.
 * 
 * @param array $search_result
 * Array elements are;
 * 1. num_of_records
 *  2.pagging – pagination details array
 * 3. results – search results
 * 4. search_category
 *
 * @return string
 *search content.
 * 
 */

function get_search_results_content($search_result){
    if($search_result['search_category'] == 'article'):
        $results_content = "";

    foreach($search_result['results'] as $result):
        $results_content .='<div class="SearchResultsBox">
			<a href="'.$result['article_link'].'" title="'.get_clean_text($result['article_title']).'" class="BoldLinkDarkLarge">';
     if(isset($_SESSION['search_params']['search_display_field']) && is_array( ($_SESSION['search_params']['search_display_field'])) ):
         $results_content .= get_clean_text($result['article_title']);
     endif;
          $results_content .= '</a> -

		'.get_clean_text($result['article_content'],400).'<a href="'.$result['article_link'].'" class="BlueLink" >more&raquo;</a>
    </div>';
    endforeach;
else:
        $results_content = "";
    foreach($search_result['results'] as $result):
        $results_content .='<div class="SearchResultsBox">
			<a href="'.$result['post_link'].'" title="'.get_clean_text($result['title']).'" class="BoldLinkDarkLarge">';
    if(isset ($_SESSION['search_params']['search_display_field']) && in_array('title', $_SESSION['search_params']['search_display_field'])):
        $results_content .= get_clean_text($result['title']);
    endif;
        $results_content .='</a> -
		'.get_clean_text($result['post'],400).' <a href="'.$result['post_link'].'" class="BlueLink" >more&raquo;</a>';
     if(isset ($_SESSION['search_params']['search_display_field']) && in_array('post_rate', $_SESSION['search_params']['search_display_field'])):
        $results_content .= genarate_rating($result['post_id']);
    endif;
	$results_content .='</div>';
     endforeach;
endif;

    return $results_content;
}

/**
 *Search posts based on condition
 * 
 * @param string $cond. Where condition for the SQL posts search query
 * 
 * @return array
 * array elements are
 * 1.post_id
 * 2.title
 * 3.post
 * 4.post_date
 * 5.user_id
 * 6.category_id
 * 7.parent_post_id
 * 8.order_id
 * 9.no_views
 * 10.posted_ip
 * 11.status
 * 12.post_rate
 * 13.post_icon
 * 14.post_link
 *
 */

function search_post($cond){
    return Post::searchPost($cond);

}
?>
