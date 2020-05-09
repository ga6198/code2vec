<?PHP

/*
	------------------------------------------------		
			NewLife Blogging System Version 3		
	------------------------------------------------
		Developed by sevengraff
		Nick Fun <nick@sevengraff.com>
		Jan-March 2004
		Liscensed under the GNU GPL
	------------------------------------------------
*/

/**
 * Interface to registered users.
 *
 * @package NLB3
 * @author Nick F <nick@sevengraff.com>
 * @date 12-19-03
 */
class nlb_user {
	
	var $sql;
	var $data = array();
	var $permissions = array();
	var $friends = array();
	var $id = -1;
	var $extraInfo;
	var $isLogedIn;
	
	/**
	 * @return	void
	 * @param sqldb2
	 * @param [int]
	 * @desc Pass in a working sqldb2 object from driver. setting userid is optional.
	 * @date 12-19-03
	 */
	function nlb_user( &$sqldb, $uid = false ) {
		$this->sql =& $sqldb;
		if( $uid ) {
			$this->id = $uid;
			$this->buildData();
		}
		$this->isLogedIn = false;
	}
	
	/**
	 * @return void
	 * @param string
	 * @param bool
	 * @desc Looks in the database for userid and sets it
	 * @date 01-12-04
	 */
	function setIdByName( $name ) {
		$user = $this->sql->getArray('SELECT user_id FROM ' . db_users . ' WHERE username="' . $name . '";');
		if( empty( $user ) )
			return false;
		$this->setId( $user['user_id'] );
	}
	
	/**
	 * @return bool
	 * @param string
	 * @desc looks for the username in the database
	 * @date 01-12-04
	 */
	function userExists( $name ) {
		$check = $this->sql->getArray("SELECT count(*) as c FROM " . db_users . ' WHERE username="' . $name . '";');
		if( $check['c'] >= 1 ) {
			return true;
		} else {
			return false;
		}
		
	}
	
	/**
	 * @return bool
	 * @param string
	 * @desc Checks a password against hash in database. Assumes you havn't MD5'ed
	 * @date 12-19-03
	 */
	function checkPass( $pw, $name = false ) {
		$pw = md5($pw);
		if( !$name && !empty( $this->data['username'] ) ) 
			$name = $this->data['username'];
		$pass = $this->sql->getArray('SELECT password FROM ' . db_users . " WHERE username = '$name';");
		return $pass['password'] === $pw;
	}
	
	/**
	 * @return void
	 * @param int
	 * @desc Will log in a user by generating cookies. Pass in for how long (in minutes)
	 * @date 12-19-03
	 */
	function login( $t ) {
		if( $this->isLogedIn ) 
			return;
		// $t is time in days.
		$time = time() + 60 * 60 * 24 * $t;
		setcookie('nlb3', $this->id . '::' . $this->data['password'], $time);
		$this->isLogedIn = true;
	}
	
	/**
	 * @return VOID
	 * @desc Delete login cookies.
	 * @date 01-09-04
	 */
	function logout() {
		$past = strtotime("-5 days");
		setcookie('nlb3', '', $past);	// set value to nothing, expire date in past
		$this->isLogedIn = false;
		//unset( $_COOKIE['nlb3'] );
	}
	
	/**
	 * @return BOOL
	 * @desc Looks for cookies, and validates password if exists. Also checks for banned users.
	 * @date 01-10-04
	 */
	function checkLogin( ) {
		// loing check
		if( isset( $_COOKIE['nlb3'] ) ) {
			$data = explode( '::', $_COOKIE['nlb3'] );
			$id = $data[0];
			$pass = $data[1];
			$fromdb = $this->sql->getArray('SELECT password FROM ' . db_users . ' WHERE user_id = ' . $id . ' LIMIT 1;');
			if( $pass === $fromdb['password'] ) {
				$this->setid( $id );
				$this->isLogedIn = true;
			} else {
				$this->isLogedIn = false;
			}
		}
		// we also check for banned users
		$this->checkBanned();
	}
	
	/**
	 * @return void
	 * @desc looks for banned users, and sends them to propper page.
	 * @date 02-24-04
	 */
	function checkBanned() {
		// get rid of old bans
		$this->sql->query('DELETE FROM ' . db_banned . ' WHERE expires < ' . time() . ';');
		$ip = $_SERVER['REMOTE_ADDR'];
		$row = $this->sql->getArray('SELECT banned_id FROM ' . db_banned . ' WHERE ip="' . $ip . '" OR user_id="' . $this->id . '" LIMIT 1;');
		if( empty( $row ) ) {
			// not banned!
			return $this->isLogedIn;
		} else {
			// BANNED USER!
			jsRedirect( script_path . 'banned.php?id=' . $row['banned_id'] );
		}
	}
				
	/**
	 * @return void
	 * @param int
	 * @desc Sets the User ID and calls build_data() to fetch data from DB.
	 * @date 12-19-03
	 */
	function setId( $id ) {
		$this->id = $id;
		$this->builddata();
		return;
	}
	
	/**
	 * @return void
	 * @desc Will fetch all needed data from DB.
	 * @date 12-19-03
	 */
	function buildData() {
		if( is_null($this->id) ) return;
		$this->data = $this->sql->getArray('SELECT * FROM ' . db_users . ' WHERE user_id=' . $this->id . ' LIMIT 1;');
		// kill slashes
		foreach( $this->data as $key => $val ) {
			$this->data[$key] = stripslashes($val);
		}
		// build permissions
		$this->permissions = explode(':', $this->data['access']);
		// for use with nlb_blogs
		$this->extraInfo = array(
			'author' 		=> $this->data['username'],
			'date_format' 	=> $this->data['date_format'],
			'custom_title' 	=> $this->data['custom']
		);
	}
	
	/**
	 * @return void
	 * @desc Will update the user's settings to the database for storage.
	 * @date 12-19-03
	 */
	function updateDB() {
		$this->data['access'] = implode($this->permissions, ':');
		$query = 'UPDATE `' . db_users . '` SET ';
		foreach( $this->data as $key => $val ) {
			$val = addslashes($val);
			$query .= "\n$key='$val', ";
		}
		$query = substr($query, 0, -2);		// remove last comma
		$query .= "\nWHERE user_id='$this->id';";
		$this->sql->query( $query, false );
		//echo $query;
	}
	
	/**
	 * @return void
	 * @desc used only when testing NLB3
	 * @date 12-19-03
	 */
	function debugInfo() {
		echo 'User <b>' . $this->data['username'] . '</b> (' . $this->id . ')<br>';
		echo 'Is allowed: <br><i>';
		echo implode($this->permissions, '<br>');
		echo '</i><br>Other Data:';
		echo '<pre>';
		print_r($this->data);
		echo '</pre>';
	}
	
	/**
	 * @return bool
	 * @param string
	 * @desc will return true if $right is something the user has permission to do.
	 * @date 12-19-03
	 */
	function isAllowed( $right ) {
		return in_array( $right, $this->permissions );
	}
	
	/**
	 * @return void
	 * @param array $rights
	 * @desc sets ALL permissions for this user.
	 * @date 02-07-04
	 */
	function setPermissions( $rights ) {
		$this->permissions = $rights;
	}
	
	/**
	 * @return void
	 * @param string
	 * @param string
	 * @desc Set a users config setting to a value. Be sure to run updateDB afterwords.
	 * @date 12-19-03
	 */
	function set( $item, $value ) {
		if( isset($this->data[$item]) ) {
			$this->data[$item] = $value;
		}
	}
	
	/**
	 * @return string
	 * @param string $item
	 * @desc pretty obvious
	 * @date 02-02-04
	 */
	function get( $item ) {
		return $this->data[$item];
	}
	
	/**
	 * @return bool
	 * @param string
	 * @desc Will MD5 a new password and put it into the database if it is valid.
	 * @date 12-19-03
	 */
	function setPassword( $new ) {
		if( strlen($new) < 5 ) {
			return false;
		} else {
			$this->set('password', md5($new));
			return true;
		}
	}
	
	/**
	 * @return void
	 * @desc Goes through the database and counts all public blogs by this user. Driver _must_ call updateDB() after this.
	 * @date 12-19-03
	 */
	function recountBlogs() {
		if( $this->id ) {
			$count = $this->sql->getArray('
			SELECT COUNT(blog_id) as c 
			FROM ' . db_blogs . ' 
			WHERE access=' . access_public . ' AND author_id=' . $this->id . ';');
			$this->set('blog_count', $count['c']);
		}
	}
	
	/**
	 * @return void
	 * @param string $file
	 * @desc Updates users template in the database
	 * @date 02-05-04
	 */
	function setTemplateSource( $file, $hometext ) {
		if( !$this->templateExists( $file ) ) {
			return;
		}
		$pre = "./templates/";
		$style_blog 	= 'templates/' . $file . '.blog.ets';
		$style_friends 	= 'templates/' . $file . '.friends.ets';
		$style_profile 	= 'templates/' . $file . '.profile.ets';
		
		// get content from file.
		$fp = fopen( $style_blog, 'rb' );
		$tpl_blog = fread( $fp, filesize( $style_blog ) );
		fclose($fp);
		
		$fp = fopen( $style_friends, 'rb' );
		$friends = fread( $fp, filesize( $style_friends ) );
		fclose( $fp );
		
		$fp = fopen( $style_profile, 'rb' );
		$profile = fread( $fp, filesize( $style_profile ) );
		fclose( $fp );
		
		// make replacements
		$tpl_blog 	= str_replace( "%HOME%", $hometext, addslashes( $tpl_blog ) );
		$friends 	= str_replace( "%HOME%", $hometext, addslashes( $friends  ) );
		$profile 	= str_replace( "%HOME%", $hometext, addslashes( $profile  ) );
		
		$linkurl 	= script_path . 'index.php';
		$tpl_blog 	= str_replace( "%HOME_LINK%", $linkurl, $tpl_blog );
		$friends 	= str_replace( "%HOME_LINK%", $linkurl, $friends  );
		$profile 	= str_replace( "%HOME_LINK%", $linkurl, $profile  );
		
		// insert it into the database
		$now = time();
		
		$this->sql->query( 'UPDATE ' . db_source . '
		SET blog = "' . $tpl_blog . '",
		blog_updated = "' . $now . '",
		friends = "' . $friends . '",
		friends_updated = "' . $now . '",
		profile = "' . $profile . '",
		profile_updated = "' . $now . '"
		WHERE owner_id = "' . $this->id . '"
		LIMIT 1;');
		
		// done.
	}
	
	/**
	 * @return bool
	 * @param string $template
	 * @desc Looks in /template to see if standard user template source files exist
	 * @date 02-06-04
	 */
	function templateExists( $template ) {
		$style_blog 	= 'templates/' . $template . ".blog.ets";
		$style_friends 	= 'templates/' . $template . ".friends.ets";
		$style_profile 	= 'templates/' . $template . ".profile.ets";
		
		if( is_readable( $style_blog ) && is_readable( $style_friends ) && is_readable( $style_profile ) ) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * @return bool
	 * @param object nlb_config $config
	 * @desc Force a user to validate his email address. Returns true if sent, false otherwise
	 * @date 02-08-04
	 */
	function validateEmail( & $config ) {
		global $l;
		$mail = new nlb_mail( $this->sql );
		if( $mail->Active ) {
			// make code.
			$code = md5(uniqid(rand(), true));
			$link = full_url . script_path . 'index.php/action/validate/code/' . $code;
			$body = $l['validation_email'];
			$body = str_replace( "%LINK%", $link, $body );
			$body = str_replace( "%SITE%", $config->get('site_name'), $body );
			$body = str_replace( "%USER%", $this->get('username'), $body );
			
			$mail->AddAddress( $this->get('email'), $this->get('username') );
			$mail->Subject = $l['validation_subject'];
			$mail->Body = $body;
			
			if( !$mail->Send() ) {
				// mail was not sent, must set user to valid.
				$this->set('valid', 1);
				$this->updateDB();
				return false;
			}
			// add record in validation table.
			$this->sql->query(' # Add validation row
			INSERT INTO `nlb3_validate` ( `validate_id` , `owner_id` , `code` , `date` )
			VALUES (
			"", "' . $this->id . '", "' . $code . '", "' . time() . '"
			);');
			$this->set('valid', 0);
			$this->updateDB();
			return true;
		}
		// Server is not sending email's, assume user gave a good email.
		$this->set('valid', 1);
		$this->updateDB();
		return false;
	}
	
	/**
	 * @return void
	 * @desc Gets a list of users friends from the database. ASSUMES user is loged in.
	 * @date 02-17-04
	 */
	function fetchFriends() {
		$this->friends = $this->sql->getAllArray('SELECT u.username, f.friend_id, f.date 
		FROM ' . db_friends . ' as f, ' . db_users . ' as u
		WHERE u.user_id = f.friend_id AND f.owner_id = ' . $this->id . '
		ORDER BY f.date DESC;');
	}
	
	/**
	 * @return bool
	 * @param int
	 * @desc Returns true if user lists the passed user id as a friend
	 * @date 02-17-04
	 */
	function areFriends( $fid ) {
		if( empty( $this->friends ) ) {
			$this->fetchFriends();
		}
		foreach( $this->friends as $f ) {
			if( $f['friend_id'] == $fid ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @return int
	 * @param ARRAY
	 * @desc Registeres a new user. Returns users id.
	 * @date 12-20-03
	 */
	function newUser( $data ) {
		$query = "INSERT INTO " . db_users . " (
user_id, 
username, 
password, 
email, 
access, 
registered, 
last_login, 
ip, 
blog_count, 
timezone, 
bio, 
custom, 
date_format, 
birthday, 
perpage, 
gender, 
valid 
) 
VALUES 
( 
'',
'{$data['username']}', 
'{$data['password']}', 
'{$data['email']}', 
'{$data['access']}', 
'{$data['registered']}', 
'{$data['last_login']}', 
'{$data['ip']}', 
'{$data['blog_count']}', 
'{$data['timezone']}', 
'{$data['bio']}', 
'{$data['custom']}', 
'{$data['date_format']}', 
'{$data['birthday']}', 
'{$data['perpage']}', 
'{$data['gender']}', 
'{$data['valid']}'
);";
		$this->sql->query( $query );
		return $this->sql->getLastId();
	}
}

/**
 * =======================================
 * 		U S E R   P E R M I S S I O N S
 * =======================================
 */
$USER_PERMISSIONS = array(
	'blog',
	'comment',
	'av_use',
	'av_up',
	'friends',
	'tpl_change',
	'tpl_custom',
	'admin'
);
?>