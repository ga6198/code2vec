<?php

$mode = isset($_GET['mode']) ? $_GET['mode'] : "";
$mode = isset($_POST['mode']) ? $_POST['mode'] : $mode;

$post_id = isset($_GET['post_id']) ? $_GET['post_id'] : "";
$post_id = isset($_POST['post_id']) ? $_POST['post_id'] : $post_id;


switch ($mode) {


    case 'post_rpc':

        if (isset($_GET['parent_post_id']))
            $post_id = $_GET['parent_post_id'];
        if (isset($_GET['num_records']))
            $_SESSION['num_records'] = $_GET['num_records'];
        else
            $_SESSION['num_records'] = _NO_POST_IN_PAGE;

        $pagin['num_records'] = $_SESSION['num_records'];
        $pagin['page'] = isset($_GET['page']) ? $_GET['page'] : 1;
        $pagin['tot_posts'] = count(get_replies($post_id));
        $pagin['start'] = ( $pagin['page'] - 1) * $_SESSION['num_records'];


        $pagination = getPaggings($pagin);
        $post_info = get_postinfo($post_id);
        $posted_user = get_userinfo($post_info['user_id']); 
        header('Content-type: text/xml');


        echo '<taconite>
            <replaceContent  select="#post_content">
                <eval><![CDATA[';
        if($pagin['page'] == '1'){
            
          echo '<div id="parent_post">
            <div class="divb">
                <div class="divReplyBoxCover">
                    <div class="show_member_details">
                        <div class="AuthorName">
                            <span class="TextBoldDark">'.$posted_user['username'].'</span>
                        </div>
                        <div class="MainAvatartDisplay">
                            <div class="AvatarBox">
                                <a href="#" class="Avatar"><img src="'.get_foruminfo('forum_url').'resimgh.php?file=uploads/'.$posted_user['avatar'].'&width=80&height=80" title="'.$posted_user['username'].'" alt="'.$posted_user['username'].'" border="0" ></a>
                            </div>';
                            if ($posted_user['group_id'] == _ADMIN_GR_ID): 
                                echo '<div class="AdminIcon" title="admin"><span>Admin</span></div>';
                            elseif ($posted_user['group_id'] == _MODERATOR_GR_ID): 
                                echo '<div class="ModeratorIcon" title="Moderator"><span>Moderator</span></div>';
                            elseif ($posted_user['group_id'] == _USER_GR_ID):
                                echo '<div class="ReviewerIcon" title="Reviewer"><span>Reviewer</span></div>';
                            endif;
                            echo '<br>
                             '.get_num_of_user_posts($posted_user['user_id'], false).
			'Post';
                            if (get_num_of_user_posts($posted_user['user_id'], false) > 1):echo 's';
                            endif;
                            echo '<br>
                            '.get_num_of_receve_gifts($posted_user['user_id']).'
			Gift';
                            if (get_num_of_receve_gifts($posted_user['user_id']) > 1):echo 's';
                            endif;
                        echo '</div>
                    </div>
                    <div class="PostListOwner">
                        <div class="MainPostBox">
                            <div class="Collapse" onmouseover="this.className=\'Expand\'" onmouseout="this.className=\'Collapse\'" style="cursor:pointer;">
                                <span class="LastPosted">
                                    <span style="font-size:16px;font-weight:bold;">'.$post_info['title'].'</span>- <span style="font-size:13px;">'.gmt_date($post_info['post_date']).'</span>
                                </span>
                            </div>
                            <div>
                                '.$post_info['post'].'
                                <div class="PostControlPanel" style="float:none;">';

                                     get_post_control($post_info);

                                echo '</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div></div>';
            
            
            
        }
        echo '<div id="reply_sec">';
        foreach (get_replies($post_id, $pagin['start'], $_SESSION['num_records']) as $key_reply => $reply):

            echo '<div class="';
            if ($key_reply % 2 == 0) {
                echo "diva";
            } else {
                echo "divb";
            }
            echo '">';

            echo get_reply_template($reply);

            echo  '</div> <br  />';
        endforeach;
        echo '</div>';

        echo '  ]]>
            </eval></replaceContent >
            <replaceContent select="#pagination">
            <eval><![CDATA[';
        echo $pagination;
        echo ']]></eval></replaceContent >
            <replaceContent select="#perPagePaggin">
            <eval><![CDATA[';
        echo perPagePaggin();
        echo ']]></eval></replaceContent >
            </taconite>';

        exit();


        break;

    case 'report_bad_post':

        extract($_GET);
        $now_date_time = date('Y-m-d H:i:s');
        $user_info = logged_user_info();
        $bad_post['user_id'] = $user_info['user_id'];
        $bad_post['post_id'] = $post_id;
        $bad_post['report_date'] = $now_date_time;
        $bad_post['status'] = _ACTIVE;
        $badClick = BadPost::FindAll("post_id='" . $post_id . "' AND user_id='" . $user_info['user_id'] . "'");
        if (sizeof($badClick) == 0) {
            add_bad_post($bad_post);
            echo "1";
        } else {
            echo "0";
        }

        $content = '';
        break;

    case 'submit_reply':

        $content_arr = array();
        $user_ids = array();
        $new_reply = array();
        extract($_POST);
        $now_date_time = date('Y-m-d H:i:s');
        $order_id = $last_order_id + 1;
        $logged_user = get_logged_user();

        $new_reply['title'] = $title;
        $new_reply['post'] = str_replace("<a href=", "<a target=\"_blank\" rel=\"nofollow\"  href=", $reply_text);
        $new_reply['post_date'] = $now_date_time;
        $new_reply['user_id'] = $logged_user['user_id'];
        $new_reply['category_id'] = $category_id;
        $new_reply['parent_post_id'] = $parent_post_id;
        $new_reply['order_id'] = $order_id;
        $new_reply['no_views'] = 0;
        $new_reply['posted_ip'] = $_SERVER['REMOTE_ADDR'];
        $new_reply['status'] = _ACTIVE;

        $last_reply = add_post($new_reply);

        // send email  to replied user
        $reply = get_postinfo($last_reply);
        $parent_post = get_postinfo($parent_post_id);


        $subject = "Topic Notification - " . $parent_post['title'];

        //send mail to posted user
        $posted_user = get_userinfo($parent_post['user_id']);
        $user_email = $posted_user['email'];
        $user_name = $posted_user['username'];
        $attribute_category = get_user_attributes($posted_user['user_id']);

        if (isset($attribute_category['notify_email']) && $attribute_category['notify_email'] == '1') {
            $body = read_template_file("applications/post/reply_email.html");
            $body = str_replace("#forum_name#", get_foruminfo('forum_name'), $body);
            $body = str_replace("#forum_url#", get_foruminfo('forum_url'), $body);
            $body = str_replace("#post_url#", $parent_post["post_link"], $body);
            $body = str_replace("#post_title#", $parent_post['title'], $body);
            $body = str_replace("#parent_post_id#", $reply['parent_post_id'], $body);
            $body = str_replace("#user_name#", $user_name, $body);
            $body = str_replace("#userid#", md5($parent_post['user_id']), $body);
            $is_mail_send = send_forum_email($subject, $body, $user_email, $user_name);
            $user_ids[] = $parent_post['user_id'];
        }

        //send mail to another replied users
        $replies_to_post = get_replies($parent_post_id);
        foreach ($replies_to_post as $replied) {

            if (!in_array($replied['user_id'], $user_ids)) {

                $replied_user = get_userinfo($replied['user_id']);
                $user_email = $replied_user['email'];
                $user_name = $replied_user['username'];
                $attribute_category = get_user_attributes($replied_user['user_id']);


                if (isset($attribute_category['notify_email']) && $attribute_category['notify_email'] == '1') {
                    $body = read_template_file("applications/post/reply_email.html");
                    $body = str_replace("#forum_name#", get_foruminfo('forum_name'), $body);
                    $body = str_replace("#forum_url#", get_foruminfo('forum_url'), $body);
                    $body = str_replace("#post_url#", $parent_post["post_link"], $body);
                    $body = str_replace("#post_title#", $parent_post['title'], $body);
                    $body = str_replace("#parent_post_id#", $reply['parent_post_id'], $body);
                    $body = str_replace("#user_name#", $user_name, $body);
                    $body = str_replace("#userid#", md5($replied_user['user_id']), $body);
                    $is_mail_send = send_forum_email($subject, $body, $user_email, $user_name);
                    $user_ids[] = $replied_user['user_id'];
                }
            }
        }



        $content = "Reply added.";

        $content = json_encode(array('msg' => $content));


        break;

    case 'submit_new_post':

        $content_arr = array();
        extract($_POST);
        $now_date_time = date('Y-m-d H:i:s');
        $order_id = $last_order_id + 1;
        $logged_user = get_logged_user();
        //$post_text              =	str_replace("../uploads","../../uploads",$post_text);
        $new_post['title'] = $title;
        $new_post['post'] = str_replace("<a href=", "<a target=\"_blank\" rel=\"nofollow\"  href=", $post_text);
        $new_post['post_date'] = $now_date_time;
        $new_post['user_id'] = $logged_user['user_id'];
        $new_post['category_id'] = $category_id;
        $new_post['parent_post_id'] = null;
        $new_post['order_id'] = $order_id;
        $new_post['no_views'] = 0;
        $new_post['posted_ip'] = $_SERVER['REMOTE_ADDR'];
        $new_post['status'] = _ACTIVE;

        $added_post_id = add_post($new_post);

        $added_post = get_postinfo($added_post_id);

        $user_email = $logged_user['email'];
        $user_name = $logged_user['username'];
        $attribute_category = get_user_attributes($logged_user['user_id']);
//
//		$category_obj = Category::Find($category_id);
//
        if (isset($attribute_category['notify_email']) && $attribute_category['notify_email'] == 1) {

            $subject = "Topic Notification - " . $title;
            $body = read_template_file("applications/post/new_post_email.html");
            $body = str_replace("#forum_name#", get_foruminfo('forum_name'), $body);
            $body = str_replace("#user_name#", $user_name, $body);
            $body = str_replace("#forum_url#", get_foruminfo('forum_url'), $body);
            $body = str_replace("#post_url#", $added_post["post_link"], $body);
            $body = str_replace("#post_title#", $title, $body);

            $is_mail_send = send_forum_email($subject, $body, $logged_user['email'], $logged_user['username']);


            if ($is_mail_send == true) {
                $content1 = "New Post Added successfully.";
            } else {

                $content1 = "Error in mailing Topic email.. please try again later";
            }
        } else {
            $content1 = "New Post Added successfully.";
        }


        $content_arr['content1'] = $content1;
        $content = json_encode($content_arr);


        break;

    case 'sec_code_check':
        echo ($_SESSION["SEC_CODE"] == $_GET['sce_code']) ? 'true' : 'false';
        $content = '';

        break;

    case 'msg_to_author':

        extract($_GET);
        $now_date_time = date('Y-m-d H:i:s');
        $user_info = logged_user_info();

        $message['parent_message_id'] = 0;
        $message['title'] = $title;
        $message['message'] = $msg;
        $message['from_user_id'] = $user_info['user_id'];
        $message['to_user_id'] = $user_id;
        $message['post_date'] = $now_date_time;
        $message['is_read'] = 0;
        $message['status'] = _ACTIVE;
        add_user_message($message);

        $content = '';

        break;

    case 'save_post':

        extract($_GET);
        $now_date_time = date('Y-m-d H:i:s');
        $logged_user = logged_user_info();

        $post = array();
        $post['user_id'] = $logged_user['user_id'];
        $post['post_id'] = $post_id;
        $post['date_time'] = $now_date_time;
        $post['status'] = 1;
        $bokmrk = SavedPost::FindAll("post_id='" . $post_id . "' AND user_id='" . $logged_user['user_id'] . "'");
        if (sizeof($bokmrk) == 0) {
            save_post($post);
            echo "1";
        } else {
            echo "0";
        }
        $content = '';
        break;

    case 'sugges_pin':

        extract($_GET);
        $now_date_time = date('Y-m-d H:i:s');
        $logged_user = logged_user_info();

        $pin = array();
        // $pin['user_id'] = $logged_user['user_id'];
        $pin['post_id'] = $post_id;
        $pin['date_time'] = $now_date_time;
        $pin['status'] = _INACTIVE;

        $pin_info = get_suggest_pininfo($post_id);
        if ($pin_info != false) {
            $suggest_users = array();
            $suggest_users = unserialize($pin_info['suggest_users']);
            if (in_array($logged_user['user_id'], $suggest_users)) {
                $msg = "You alredy suggest this thread";
            } else {
                $suggest_users[] = $logged_user['user_id'];
                $pin['suggest_users'] = serialize($suggest_users);
                if (suggest_a_pin($pin)) {
                    $msg = "Your suggesion added";
                } else {
                    $msg = "Error in system. Try again later";
                }
            }
        } else {
            $pin['suggest_users'] = $logged_user['user_id'];
            if (suggest_a_pin($pin)) {
                $msg = "Your suggesion added";
            } else {
                $msg = "Error in system. Try again later";
            }
        }

        $content = $msg;
        break;

    case 'pin_a_thread':
        extract($_GET);
        $now_date_time = date('Y-m-d H:i:s');
        $logged_user = logged_user_info();

        if ($logged_user['group_id'] == _ADMIN_GR_ID || $logged_user['group_id'] == _MODERATOR_GR_ID) {

            $pin_info = get_suggest_pininfo($post_id);
            if ($pin_info) {
                $pin['pin_id'] = $pin_info['pin_id'];
                $pin['post_id'] = $post_id;
                $pin['approve_user'] = $logged_user['user_id'];
                $pin['approved_date'] = $now_date_time;
                $pin['status'] = _ACTIVE;
                if (suggest_a_pin($pin)) {
                    $msg = "true";
                } else {
                    $msg = "Error in system. Try again later";
                }
            } else {

                $pin['post_id'] = $post_id;
                $pin['suggest_users'] = $logged_user['user_id'];
                $pin['approve_user'] = $logged_user['user_id'];
                $pin['approved_date'] = $now_date_time;
                $pin['status'] = _ACTIVE;
                if (suggest_a_pin($pin)) {
                    $msg = "true";
                } else {
                    $msg = "Error in system. Try again later";
                }
            }
        } else {
            $msg = "You don't have a permission to pin this thread";
        }
        $content = $msg;
        break;

    case 'un_pin_a_thread':

        extract($_GET);
        $logged_user = logged_user_info();

        if ($logged_user['group_id'] == _ADMIN_GR_ID || $logged_user['group_id'] == _MODERATOR_GR_ID) {

            $pin_info = get_suggest_pininfo($post_id);
            if ($pin_info) {
                $pin_id = $pin_info['pin_id'];
                if (remove_a_pin($pin_id)) {
                    $msg = "true";
                } else {
                    $msg = "Error in system. Try again later";
                }
            } else {
                
            }
        } else {
            $msg = "You don't have a permission to un pin this thread";
        }
        $content = $msg;

        break;


    case 'rate_post':
        extract($_GET);
        //$rate_value | $post_id
        $rate_id = 0;
        if (is_user_logged()) {
            $user_id = $_SESSION['LOGGED_USER'];
            if ($post_id != 0) {
                $rate_id = get_rate_for_post($post_id);
                if ($rate_id == 0) {
                    $rate_id = rate_post($post_id, $user_id, $rate_value);
                    if ($rate_id == 0) {
                        $msg = 'Post can not be rated...';
                    } else {
                        $msg = 'Post rated successfully..';
                    }
                } else {
                    $msg = 'You have rated this post already...';
                }
            } else {
                $msg = 'You can not rate this post...';
            }
        } else {
            $msg = 'You can not rate this post...';
        }

        $content = json_encode(array(
            'msg' => $msg,
            'rate_id' => $rate_id
                )
        );
        break;

    case 'rate_delete':
        extract($_GET);
        //$rate_id
        if ($post_id != 0) {
            //rate id will be 0 for not rated post by teh user
            $rate_id = Post::getRateIdforPost($post_id);
            if ($rate_id != 0) {
                $rate = Rate::Find($rate_id);
                $rate_value = $rate->getRate();
                if (Rate::Delete($rate_id)) {
                    $msg = 'Rate deleted...';
                    //diduct the rate value
                    $post = Post::Find($rate->getPostId());
                    $post_rate = $post->getPostRate();
                    $post_rate = $post_rate - $rate_value;
                    $post->setPostRate($post_rate);
                    $post->Update();
                } else {
                    $msg = 'Rate can not delete...';
                }
            } else {
                $msg = 'You can not delete rate for this post...';
            }
        } else {
            $msg = 'You can not delete rate for this post...';
        }
        $content = json_encode(array(
            'msg' => $msg,
            'rate_id' => 0
                )
        );
        break;


    case 'is_saved_post':

        extract($_GET);

        return SavedPost::isSavedPost($post_id);
        $content = '';
        break;

    case 'send_gift':

        extract($_GET);
        $now_date_time = date('Y-m-d H:i:s');
        $user_info = logged_user_info();

        $gift['gift_id'] = $gift_id;
        $gift['message'] = $msg;
        $gift['from_user_id'] = $user_info['user_id'];
        $gift['to_user_id'] = $to;
        $gift['post_date'] = $now_date_time;
        $gift['status'] = _ACTIVE;


        send_gift($gift);

        $content = '';
        break;

    case 'forward':

        extract($_GET);

        $subject = $subject;
        $email = $email;
        $user_info = logged_user_info();

        $post_info = get_postinfo($post_id);
        if ($post_info['parent_post_id'] != null) {
            $post_info = get_postinfo($post_info['parent_post_id']);
        }
        $post_title = $post_info['title'];
        $post_url = $post_info['post_link'];

        $body = read_template_file("applications/post/forward_post_email.html");
        $body = str_replace("#username#", $user_info['username'], $body);
        $body = str_replace("#forum_name#", get_foruminfo('forum_name'), $body);
        $body = str_replace("#forum_url#", get_foruminfo('forum_url'), $body);
        $body = str_replace("#post_url#", $post_url, $body);
        $body = str_replace("#message#", $msg, $body);

        $is_mail_send = send_forum_email($subject, $body, $user_info['email'], $user_info['username']);


        if ($is_mail_send == true) {
            $content1 = "Mail has been sent:";
        } else {

            $content1 = "Mail send error.Please try again later.";
        }
        $content = json_encode(array('msg' => $content1));
        break;

    case 'edit_post':

        $post = Post::Find($post_id);
        if ($post->getUserId() != User::getLoggedUserId()) {
            $common_obj = new Common();
            $category_obj = $post->getCategory();
            $forum_url_ = Common::getForumAddress();
            $return_url = _FORUM_MOD_URL . $common_obj->getSeoFriendlyUrl($category_obj->getMainCategoryName()) . '_' . $category_obj->getParentCategoryId() . '/' . $common_obj->getSeoFriendlyUrl($post->getCategoryName()) . '_' . $post->getCategoryId() . '/' . $common_obj->getSeoFriendlyUrl($post->getTitle()) . '_' . $post->getPostId() . '.html';
            $content = header('Location:' . $return_url);
        } else {
            $_smarty->assign('post', $post);
            $_smarty->assign('type', 'edit');

            if (Common::getBrowser($_SERVER['HTTP_USER_AGENT']) == 'Internet Explorer 6' || Common::getBrowser(Common::getBrowser($_SERVER['HTTP_USER_AGENT']) == 'Internet Explorer 7' || $_SERVER['HTTP_USER_AGENT']) == 'Internet Explorer 5' || Common::getBrowser($_SERVER['HTTP_USER_AGENT']) == 'Internet Explorer 4') {
                $_smarty->assign('add_br_tag', '<br />');
            }
            $content = $_smarty->fetch('post_tac.tpl');
        }
        break;

    case 'submit_edit_post':

        $content_arr = array();
        extract($_POST);
        $now_date_time = date('Y-m-d H:i:s');

        $edit_post_info = get_postinfo($post_id);
        $edit_post_info['title'] = $title;
        $edit_post_info['post'] = str_replace("<a href=", "<a target=\"_blank\" rel=\"nofollow\"  href=", $edit_text);
        $edit_post_info['post_date'] = $now_date_time;
        $edit_post_info['posted_ip'] = $_SERVER['REMOTE_ADDR'];

        update_post($edit_post_info);


        $post = get_postinfo($post_id);


        if ($post['parent_post_id'] != null) {
            $parent_post = get_postinfo($post['parent_post_id']);
            $redirect_url = $parent_post['post_link'];
        } else {
            $redirect_url = $post['post_link'];
        }
        $content = json_encode(array('redirect_url' => $redirect_url));

        break;
}
?>