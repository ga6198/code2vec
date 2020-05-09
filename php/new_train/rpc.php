<?php

$mode		=	isset($_GET['mode'])?$_GET['mode']:"";
$mode		=	isset($_POST['mode'])?$_POST['mode']:$mode;

$post_id	=	isset($_GET['post_id'])?$_GET['post_id']:"";
$post_id	=	isset($_POST['post_id'])?$_POST['post_id']:$post_id;

////// fro Tag Clouds///////////
if(isset($_GET['txtsearch'])){

	$_POST['txtsearch'] = $_GET['txtsearch'];
	$_POST['select_sort'] = 'post_id';
	$_POST['txt_author']	= "";
	$_POST['search_display_field'][] = 'post_rate';
	$_POST['search_display_field'][] = 'title';
	$_POST['opt_search_select'] = $_GET['opt_search'];

}
//////////////////////////////
switch ($mode)
{
	case '':

		break;

    case 'search_rpc':
            //pagination
             $search_params = $_SESSION['search_params'];

            $search_results = get_search_results($search_params);

            $results_content = get_search_results_content($search_results);
            $return_array = array(
                                    'content'=>$results_content,
                                    'pagging'=>$search_results['pagging']
                                    );
        header('Content-type: text/xml');
        echo '<taconite>
            <replaceContent  select="#pagin_info">
                <eval><![CDATA[
                '.$search_results['pagging'].'
            ]]>
            </eval></replaceContent >
            <replaceContent  select="#pagin_info2">
                <eval><![CDATA[
                '.$search_results['pagging'].'
            ]]>
            </eval></replaceContent >
             <replaceContent  select="#main_container">
                <eval><![CDATA[
                '.$results_content.'
            ]]>
            </eval></replaceContent >
            <before select="#main_container">
            <script src="'.get_foruminfo('forum_url').'js/jquery/plugins/jquery.rating.js" type="text/javascript"></script>
<script src="'.get_foruminfo('forum_url').'js/rating.js" type="text/javascript"></script>
            </before>
            </taconite>';

                //echo(json_encode($return_array));
            exit();
    break;
}


?>