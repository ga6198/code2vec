<?php 
require_once ('Connections/config.php'); 
require ('includes/authentication_nav.inc.php');  session_start();
include ('includes/db_connect_universal.inc.php');
include ('includes/db_connect_log.inc.php');
include ('includes/abv.inc.php');
include ('includes/color.inc.php');
include ('includes/plug-ins.inc.php');
$imageSrc = "images/";
// -----------------------------------------------------------------------------------------------
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title><?php if ($row_pref['mode'] == "1") echo $row_name['brewerFirstName']." ".$row_name['brewerLastName']."'s BrewBlog &gt; ".$page_title.$page_title_extension; if ($row_pref['mode'] == "2")  echo $row_name['brewerFirstName']." ".$row_name['brewerLogName']." &gt; ".$page_title.$page_title_extension; if (($page == "brewBlogCurrent") || ($page == "brewBlogDetail") || ($page == "recipeDetail")) echo " [".$row_log['brewStyle']."]"; ?></title>
<link href="css/<?php echo $row_pref['theme']; ?>" rel="stylesheet" type="text/css">
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
//-->
</script>
</head>
<body <?php if ($page == "login") echo "onLoad=\"self.focus();document.form1.loginUsername.focus()\""; ?>>
<!-- Begin Main Wrapper -->
<div id="maincontainer">
<!-- Begin Header -->
	<div id="header">
		<div class="titleText"><?php echo $row_name['brewerFirstName']; if ($row_pref['mode'] == "1") echo "'s"; echo "&nbsp;".$row_name['brewerLogName']; ?></div><div class="quoteText"><?php echo $row_name['brewerTagline']; ?></div>
	</div>
	<div id="nav"><?php include ('includes/navigation.inc.php'); ?></div>
<!-- End Header -->
	
<!-- Begin Content Wrapper -->

	<div id="contentwrapper">
		<!-- Begin Left Section -->
		<div id="<?php if (($page == "brewBlogCurrent") || ($page == "brewBlogDetail") || ($page == "recipeDetail") || ($page == "about")) echo "breadcrumb"; else echo "breadcrumbWide"; ?>">
		<?php echo $breadcrumb; ?>
		</div>
		<div id="<?php if (($page == "brewBlogCurrent") || ($page == "brewBlogDetail") || ($page == "recipeDetail") || ($page == "about")) echo "subtitle"; else echo "subtitleWide"; ?>">
			<?php 
			if ($page == "brewBlogCurrent") echo $row_log['brewName']; 
			elseif ($page == "brewBlogDetail") { if ($row_pref['mode'] == "1") echo $row_log['brewName']; else echo "BrewBlog: ".$row_log['brewName']; }
			elseif ($page == "recipeDetail") { if ($row_pref['mode'] == "1") echo $row_log['brewName']; else echo "Recipe: ".$row_log['brewName']; }
			elseif ($page == "about") echo $page_title.$page_title_extension;
			elseif ($page == "login") echo $page_title.$page_title_extension;
			else echo $page_title; 
			?>
	    </div>
	    <?php if (($page == "brewBlogCurrent") || ($page == "brewBlogDetail")) { ?>
		<div id="contentcolumn">
			 		<?php
					if ($row_pref['allowSpecifics'] == "Y") { include ('sections/specifics.inc.php'); }
					if ($row_pref['allowGeneral'] == "Y") { include ('sections/general.inc.php'); }
					if ($row_pref['allowComments'] == "Y") { include ('sections/comments.inc.php'); }
					if ($row_pref['allowRecipe'] == "Y") { include ('sections/recipe.inc.php'); }
					if ($row_pref['allowMash'] == "Y") { include ('sections/mash.inc.php'); } 
					if ($row_pref['allowWater'] == "Y") { include ('sections/water.inc.php'); } 
					if ($row_pref['allowProcedure'] == "Y") { include ('sections/procedure.inc.php'); } 
					if ($row_pref['allowSpecialProcedure'] == "Y") { include ('sections/special_procedure.inc.php'); } 
					if ($row_pref['allowFermentation'] == "Y") { include ('sections/fermentation.inc.php'); } 
					if ($row_pref['allowReviews'] == "Y") { include ('sections/reviews.inc.php'); } 
				    ?>
		</div>
		<?php } ?>
		
		<?php if ($page == "brewBlogList") { ?>
		
		<?php mysql_select_db($database_brewing, $brewing);
		$query_styles = sprintf("SELECT * FROM styles WHERE brewStyle='%s'", $row_log['brewStyle']);
		$styles = mysql_query($query_styles, $brewing) or die(mysql_error());
		$row_styles = mysql_fetch_assoc($styles);
		$totalRows_styles = mysql_num_rows($styles);
		?>
		<div id="contentwide">
		<?php if ($totalRows_log == 0) { ?>
		<table class="dataTableAltColors">
            <tr>
			<td class="dataHeading">There are currently no BrewBlogs in the database <?php if ($filter != "all") echo "for this member"; ?>.<br><br></td>
			</tr>
		</table>
		</div>
		<?php } else { ?>
		<table class="dataTableAltColors">
            <tr>
              <td nowrap class="dataHeading">Brew Name&nbsp;<a href="?page=brewBlogList&sort=brewName&dir=ASC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_up.gif" border="0" alt="Sort Ascending"></a><a href="?page=brewBlogList&sort=brewName&dir=DESC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_down.gif" border="0" alt="Sort Descending"></a></td>
              <td nowrap class="dataHeading">Date&nbsp;<a href="?page=brewBlogList&sort=brewDate&dir=ASC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_up.gif" border="0" alt="Sort Ascending"></a><a href="?page=brewBlogList&sort=brewDate&dir=DESC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_down.gif" border="0" alt="Sort Descending"></a></td>
              <td nowrap class="dataHeading">Style&nbsp;<a href="?page=brewBlogList&sort=brewStyle&dir=ASC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_up.gif" border="0" alt="Sort Ascending"></a><a href="?page=brewBlogList&sort=brewStyle&dir=DESC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_down.gif" border="0" alt="Sort Descending"></a></td>
              <td nowrap class="dataHeading">Method&nbsp;<a href="?page=brewBlogList&sort=brewMethod&dir=ASC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_up.gif" border="0" alt="Sort Ascending"></a><a href="?page=brewBlogList&sort=brewMethod&dir=DESC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_down.gif" border="0" alt="Sort Descending"></a></td>
              <?php if ($row_pref['measColor'] != "EBC") { ?><td nowrap class="dataHeading">Color&nbsp;<a href="?page=brewBlogList&sort=brewLovibond&dir=ASC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_up.gif" border="0" alt="Sort Ascending"></a><a href="?page=brewBlogList&sort=brewLovibond&dir=DESC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_down.gif" border="0" alt="Sort Descending"></a></td><?php } ?>
              <td nowrap class="dataHeading">IBU&nbsp;<a href="?page=brewBlogList&sort=brewBitterness&dir=ASC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_up.gif" border="0" alt="Sort Ascending"></a><a href="?page=brewBlogList&sort=brewBitterness&dir=DESC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_down.gif" border="0" alt="Sort Descending"></a></td>
              <td nowrap class="dataHeading">ABV&nbsp;<a href="?page=brewBlogList&sort=brewOG&dir=ASC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_up.gif" border="0" alt="Sort Ascending"></a><a href="?page=brewBlogList&sort=brewOG&dir=DESC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_down.gif" border="0" alt="Sort Descending"></a></td>
			  <?php if (($row_pref['mode'] == "2") && ($filter == "all")) { ?><td nowrap class="dataHeading">Brewer&nbsp;<a href="?page=brewBlogList&sort=brewBrewerID&dir=ASC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_up.gif" border="0" alt="Sort Ascending"></a><a href="?page=brewBlogList&sort=brewBrewerID&dir=DESC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_down.gif" border="0" alt="Sort Descending"></a></td><?php } ?>
              <td nowrap class="dataHeading center"><img src="<?php echo $imageSrc; ?>medal_gold_3.png" border="0" alt="Awards/Competition Entires" align="baseline"></td>
			</tr>
            <?php do { ?>
			<?php 
			// Get brew style information for all listed styles
			mysql_select_db($database_brewing, $brewing);
			$query_styles = sprintf("SELECT * FROM styles WHERE brewStyle='%s'", $row_log['brewStyle']);
			$styles = mysql_query($query_styles, $brewing) or die(mysql_error());
			$row_styles = mysql_fetch_assoc($styles);
			$totalRows_styles = mysql_num_rows($styles);
			
			// Get real user names
			mysql_select_db($database_brewing, $brewing);
			$query_user2 = sprintf("SELECT * FROM users WHERE user_name = '%s'", $row_log['brewBrewerID']);
			$user2 = mysql_query($query_user2, $brewing) or die(mysql_error());
			$row_user2 = mysql_fetch_assoc($user2);
			$totalRows_user2 = mysql_num_rows($user2);
			
			// Awards
			$awardNewID = "b".$row_log['id'];
			mysql_select_db($database_brewing, $brewing);
			$query_awards2 = sprintf("SELECT * FROM awards WHERE awardBrewID='%s'", $awardNewID);
			$awards2 = mysql_query($query_awards2, $brewing) or die(mysql_error());
			$row_awards2 = mysql_fetch_assoc($awards2);
			$totalRows_awards2 = mysql_num_rows($awards2);
			?>
            <tr <?php echo "style=\"background-color:$color\""; ?>>
              <td class="dataList"><?php if (isset($_SESSION["loginUsername"])) { if (($row_user['userLevel'] == "1") || ($row_log['brewBrewerID'] == $loginUsername)) echo "<a href=\"admin/index.php?action=edit&dbTable=brewing&id=".$row_log['id']."\"><img src=\"".$imageSrc."pencil.png\" alt=\"Edit ".$row_log['brewName']."\" border=\"0\" align=\"absmiddle\"></a>&nbsp;"; } ?><a href="index.php?page=brewBlogDetail&filter=<?php echo $row_log['brewBrewerID']; ?>&id=<?php echo $row_log['id']; ?>"><?php echo $row_log['brewName']; ?></a></td>
              <td class="dataList" nowrap><?php $date = $row_log['brewDate']; $realdate = dateconvert2($date,3); echo $realdate; ?></td>
			  <td class="dataList">
			  <div id="moreInfo"><a href="#"><?php echo $row_log['brewStyle']; ?>
			  <span>
			  <div id="wideWrapperReference">
			  <?php include ('reference/styles.inc.php'); ?>
			  </div>
			  </span>
			  </a>
			  </div>
			  </td>
			  <td class="dataList"><?php if ($row_log['brewMethod'] != "") { echo $row_log['brewMethod']; } else echo "&nbsp;" ?></td>
              <?php if ($row_pref['measColor'] != "EBC") { ?><td class="dataList">
			  		<?php if ($row_log['brewLovibond'] != "") { ?>
					<table class="colorTable">
						<?php include ('includes/color.inc.php'); ?>
                		<tr>
						<td bgcolor="<?php echo $beercolor; ?>">
                   		<?php   
						  		$SRM = ltrim ($row_log['brewLovibond'], "0"); 
						  		if ($SRM > "15") echo "<font color=\"#ffffff\">"; 
								else echo "<font color=\"#000000\">"; 
								echo "<center>".round ($SRM, 1)."</font>"; 
						  ?>
						</td>
			  
                		</tr>
                   </table>
				   <?php } else echo "&nbsp;"; ?>
			  </td>
			  <?php } ?>
              <td class="dataList"><?php if ($row_log['brewBitterness'] != "") { echo round ($row_log['brewBitterness'], 1); } else echo "&nbsp;" ?></td>
              <td class="dataList"><?php if (($row_log['brewOG'] != "") && ($row_log['brewFG'] != "")) { include ('includes/abv.inc.php'); echo round ($abv, 1)."%"; } else echo "&nbsp;"; ?></td>
              <?php if (($row_pref['mode'] == "2") && ($filter == "all")) { ?><td  class="dataList"><a href="?page=brewBlogList&filter=<?php echo $row_log['brewBrewerID']; ?>&sort=brewDate&dir=DESC"><?php echo $row_user2['realFirstName']."&nbsp;".$row_user2['realLastName']; ?></a></td><?php } ?>
              <td class="dataList center"><?php if ($totalRows_awards2 > 0) echo $totalRows_awards2; else echo "&nbsp;"; ?></td>
            </tr>
            <?php if ($color == $color1) { $color = $color2; } else { $color = $color1; } ?>
            <?php } while ($row_log = mysql_fetch_assoc($log)); ?>
			<tr>
			<td colspan="8"><div id="paginate"><?php echo $total." Total BrewBlogs"; if ($total > $display) echo "&nbsp;&nbsp;&nbsp;&#8226"; if ($view == "all") echo "&nbsp;&nbsp;&nbsp;&#8226;&nbsp;&nbsp;&nbsp;"; if ($total > $display) { echo "&nbsp;&nbsp;&nbsp;"; paginate($display, $pg, $total);  if ($view == "limited") { ?>&nbsp;&nbsp;&nbsp;&#8226&nbsp;&nbsp;&nbsp;<a href="?page=brewBlogList&filter=<?php echo $filter; ?>&view=all">Entire List of <?php if (($row_pref['mode'] == "2") && ($filter != "all")) echo $row_user2['realFirstName']."'s "; if (($row_pref['mode'] == "2") && ($filter == "all")) echo "Club&nbsp;"; ?>BrewBlogs</a><?php } } if  ($view == "all") { ?><a href="?page=brewBlogList&filter=<?php echo $filter; ?>&view=limited">Limited List of <?php if (($row_pref['mode'] == "2")&& ($filter != "all")) echo $row_user2['realFirstName']."'s "; if (($row_pref['mode'] == "2") && ($filter == "all")) echo "Club&nbsp;"; ?>BrewBlogs</a><?php } ?></div></td>
			</tr>			
          </table>
		  </div>
		<?php } ?>
		<?php } ?>
		
		
		<?php if ($page == "recipeList") { ?>
		<?php 
		mysql_select_db($database_brewing, $brewing);
		$query_styles = sprintf("SELECT * FROM styles WHERE brewStyle='%s'", $row_recipeList['brewStyle']);
		$styles = mysql_query($query_styles, $brewing) or die(mysql_error());
		$row_styles = mysql_fetch_assoc($styles);
		$totalRows_styles = mysql_num_rows($styles);
		?>
		<div id="contentwide">
		<?php if ($total == 0) { ?>
		<table class="dataTableAltColors">
            <tr>
			<td class="dataHeading">There are currently no recipes in the database <?php if ($filter != "all") echo "for this member"; ?>.<br><br></td>
			</tr>
		</table>
		</div>
		<?php } else { ?>
		<table class="dataTableAltColors">
            <tr>
              <td nowrap class="dataHeading">Recipe Name&nbsp;<a href="?page=recipeList&sort=brewName&dir=ASC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_up.gif" border="0" alt="Sort Ascending"></a><a href="?page=recipeList&sort=brewName&dir=DESC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_down.gif" border="0" alt="Sort Descending"></a></td>
              <td nowrap class="dataHeading">Style&nbsp;<a href="?page=recipeList&sort=brewStyle&dir=ASC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_up.gif" border="0" alt="Sort Ascending"></a><a href="?page=recipeList&sort=brewStyle&dir=DESC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_down.gif" border="0" alt="Sort Descending"></a></td>
              <td nowrap class="dataHeading">Method&nbsp;<a href="?page=recipeList&sort=brewMethod&dir=ASC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_up.gif" border="0" alt="Sort Ascending"></a><a href="?page=recipeList&sort=brewMethod&dir=DESC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_down.gif" border="0" alt="Sort Descending"></a></td>
              <?php if ($row_pref['measColor'] != "EBC") { ?><td nowrap class="dataHeading">Color&nbsp;<a href="?page=recipeList&sort=brewLovibond&dir=ASC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_up.gif" border="0" alt="Sort Ascending"></a><a href="?page=recipeList&sort=brewLovibond&dir=DESC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_down.gif" border="0" alt="Sort Descending"></a></td><?php } ?>
              <td nowrap class="dataHeading">IBU&nbsp;<a href="?page=recipeList&sort=brewBitterness&dir=ASC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_up.gif" border="0" alt="Sort Ascending"></a><a href="?page=recipeList&sort=brewBitterness&dir=DESC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_down.gif" border="0" alt="Sort Descending"></a></td>
              <td nowrap class="dataHeading">ABV&nbsp;<a href="?page=recipeList&sort=brewOG&dir=ASC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_up.gif" border="0" alt="Sort Ascending"></a><a href="?page=recipeList&sort=brewOG&dir=DESC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_down.gif" border="0" alt="Sort Descending"></a></td>
              <?php if (($row_pref['mode'] == "2") && ($filter == "all")) { ?><td nowrap class="dataHeading">Contributor&nbsp;<a href="?page=recipeList&sort=brewBrewerID&dir=ASC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_up.gif" border="0" alt="Sort Ascending"></a><a href="?page=recipeList&sort=brewBrewerID&dir=DESC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_down.gif" border="0" alt="Sort Descending"></a></td><?php } ?>
			  <td nowrap class="dataHeading center"><img src="<?php echo $imageSrc; ?>medal_gold_3.png" border="0" alt="Awards/Competition Entires" align="baseline"></td>		
            </tr>
            <?php do { ?>
			<?php 
			// Get brew style information for all listed styles
			mysql_select_db($database_brewing, $brewing);
			$query_styles = sprintf("SELECT * FROM styles WHERE brewStyle='%s'", $row_recipeList['brewStyle']);
			$styles = mysql_query($query_styles, $brewing) or die(mysql_error());
			$row_styles = mysql_fetch_assoc($styles);
			$totalRows_styles = mysql_num_rows($styles);
			// Get real user names
			mysql_select_db($database_brewing, $brewing);
			$query_user2 = sprintf("SELECT * FROM users WHERE user_name = '%s'", $row_recipeList['brewBrewerID']);
			$user2 = mysql_query($query_user2, $brewing) or die(mysql_error());
			$row_user2 = mysql_fetch_assoc($user2);
			$totalRows_user2 = mysql_num_rows($user2);
			
			// Awards
			$awardNewID = "r".$row_recipeList['id'];
			mysql_select_db($database_brewing, $brewing);
			$query_awards2 = sprintf("SELECT * FROM awards WHERE awardBrewID='%s'", $awardNewID);
			$awards2 = mysql_query($query_awards2, $brewing) or die(mysql_error());
			$row_awards2 = mysql_fetch_assoc($awards2);
			$totalRows_awards2 = mysql_num_rows($awards2);
			
			?>
            <tr <?php echo "style=\"background-color:$color\""; ?>>
              <td class="dataList"><?php if (isset($_SESSION["loginUsername"])) { if (($row_user['userLevel'] == "1") || ($row_recipeList['brewBrewerID'] == $loginUsername)) echo "<a href=\"admin/index.php?action=edit&dbTable=recipes&id=".$row_recipeList['id']."\"><img src=\"".$imageSrc."pencil.png\" alt=\"Edit ".$row_recipeList['brewName']."\" border=\"0\" align=\"absmiddle\"></a>&nbsp;"; } ?><a href="index.php?page=recipeDetail&filter=<?php echo $row_recipeList['brewBrewerID']; ?>&id=<?php echo $row_recipeList['id']; ?>"><?php echo $row_recipeList['brewName']; ?></a></td>
              
              <td class="dataList">
			  <div id="moreInfo"><a href="#"><?php echo $row_recipeList['brewStyle']; ?>
			  <span>
			  <div id="wideWrapperReference">
			  <?php include ('reference/styles.inc.php'); ?>
			  </div>
			  </span>
			  </a>
			  </div>
			  </td>
			  <td class="dataList"><?php if ($row_recipeList['brewMethod'] != "") { echo $row_recipeList['brewMethod']; } else echo "&nbsp;" ?></td>
              <?php if ($row_pref['measColor'] != "EBC") { ?><td class="dataList">
			  		<?php if ($row_recipeList['brewLovibond'] != "") { ?>
					<table class="colorTable">
						<?php include ('includes/color.inc.php'); ?>
                		<tr>
						<td bgcolor="<?php echo $beercolor; ?>">
                   		<?php   
						  		$SRM = ltrim ($row_recipeList['brewLovibond'], "0"); 
						  		if ($SRM > "15") echo "<font color=\"#ffffff\">"; 
								else echo "<font color=\"#000000\">"; 
								echo "<center>".round ($SRM,1)."</font>"; 
						  ?>
						</td>
			  
                		</tr>
                   </table>
				   <?php } else echo "&nbsp;"; ?>
			  </td>
			  <?php } ?>
              <td class="dataList"><?php if ($row_recipeList['brewBitterness'] != "") { echo round ($row_recipeList['brewBitterness'], 1); } else echo "&nbsp;" ?></td>
              <td class="dataList"><?php if (($row_recipeList['brewOG'] != "") && ($row_recipeList['brewFG'] != "")) { include ('includes/abv.inc.php'); echo round ($abv, 1)."%"; } else echo "&nbsp;"; ?></td>
              <?php if (($row_pref['mode'] == "2") && ($filter == "all")) { ?><td  class="dataList"><a href="?page=recipeList&filter=<?php echo $row_recipeList['brewBrewerID']; ?>"><?php echo $row_user2['realFirstName']."&nbsp;".$row_user2['realLastName']; ?></a></td><?php } ?>
              <td class="dataList center"><?php if ($totalRows_awards2 > 0) echo $totalRows_awards2; else echo "&nbsp;"; ?></td>
            </tr>
            <?php if ($color == $color1) { $color = $color2; } else { $color = $color1; } ?>
            <?php } while ($row_recipeList = mysql_fetch_assoc($recipeList)); ?>
			<tr>
			<td colspan="7"><div id="paginate"><?php echo $total." Total Recipes"; if ($total > $display) echo "&nbsp;&nbsp;&nbsp;&#8226"; if ($view == "all") echo "&nbsp;&nbsp;&nbsp&#8226;&nbsp;&nbsp;&nbsp;"; if ($total > $display) { echo "&nbsp;&nbsp;&nbsp;"; paginate($display, $pg, $total);  if ($view == "limited") { ?>&nbsp;&nbsp;&nbsp;&#8226;&nbsp;&nbsp;&nbsp;<a href="?page=recipeList&filter=<?php echo $filter; ?>&view=all">Entire List of <?php if ($filter != "all") echo $row_user2['realFirstName']."'s "; if (($row_pref['mode'] == "2") && ($filter == "all")) echo "Club&nbsp;"; ?>Recipes</span></a><?php } } if ($view == "all") { ?><a href="?page=recipeList&filter=<?php echo $filter; ?>&view=limited">Limited List of <?php if (($row_pref['mode'] == "2") && ($filter != "all")) echo $row_user2['realFirstName']."'s "; if (($row_pref['mode'] == "2") && ($filter == "all")) echo "Club&nbsp;"; ?>Recipes</a><?php } ?></div></td>
			</tr>
          </table>
		  </div>
		<?php } ?>
		<?php } ?>
        
        <?php if ($page == "awardsList") { ?>
		<?php 
		mysql_select_db($database_brewing, $brewing);
		$query_styles = sprintf("SELECT * FROM styles WHERE brewStyle='%s'", $row_awardsList['brewStyle']);
		$styles = mysql_query($query_styles, $brewing) or die(mysql_error());
		$row_styles = mysql_fetch_assoc($styles);
		$totalRows_styles = mysql_num_rows($styles);
		?>
		<div id="contentwide">
		<?php if ($total == 0) { ?>
		<table class="dataTableAltColors">
            <tr>
			<td class="dataHeading">There are currently no awards/competition entries in the database <?php if ($filter != "all") echo "for this member"; ?>.<br><br></td>
			</tr>
		</table>
		</div>
		<?php } else { ?>
		<table class="dataTableAltColors">
            <tr>
              <td nowrap class="dataHeading">Entered Name&nbsp;<a href="?page=awardsList&sort=awardBrewName&dir=ASC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_up.gif" border="0" alt="Sort Ascending"></a><a href="?page=awardsList&sort=awardBrewName&dir=DESC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_down.gif" border="0" alt="Sort Descending"></a></td>
              <td nowrap class="dataHeading">Style&nbsp;<a href="?page=awardsList&sort=awardStyle&dir=ASC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_up.gif" border="0" alt="Sort Ascending"></a><a href="?page=awardsList&sort=awardStyle&dir=DESC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_down.gif" border="0" alt="Sort Descending"></a></td>
              <td nowrap class="dataHeading">Competition&nbsp;<a href="?page=awardsList&sort=awardContest&dir=ASC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_up.gif" border="0" alt="Sort Ascending"></a><a href="?page=awardsList&sort=awardContest&dir=DESC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_down.gif" border="0" alt="Sort Descending"></a></td>
              <td nowrap class="dataHeading">Date&nbsp;<a href="?page=awardsList&sort=awardDate&dir=ASC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_up.gif" border="0" alt="Sort Ascending"></a><a href="?page=awardsList&sort=awardDate&dir=DESC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_down.gif" border="0" alt="Sort Descending"></a></td>
              <td nowrap class="dataHeading" colspan="2">Place&nbsp;<a href="?page=awardsList&sort=awardPlace&dir=ASC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_up.gif" border="0" alt="Sort Ascending"></a><a href="?page=awardsList&sort=awardPlace&dir=DESC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_down.gif" border="0" alt="Sort Descending"></a></td>
              <?php if (($row_pref['mode'] == "2") && ($filter == "all")) { ?><td nowrap class="dataHeading">Brewer&nbsp;<a href="?page=awardsList&sort=brewBrewerID&dir=ASC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_up.gif" border="0" alt="Sort Ascending"></a><a href="?page=awardsList&sort=brewBrewerID&dir=DESC&filter=<?php echo $filter; ?>&view=<?php echo $view; ?>"><img src="<?php echo $imageSrc; ?>sort_down.gif" border="0" alt="Sort Descending"></a></td><?php } ?>	
            </tr>
            <?php do { ?>
			<?php 
			// Get brew style information for all listed styles
			mysql_select_db($database_brewing, $brewing);
			$query_styles = sprintf("SELECT * FROM styles WHERE brewStyle='%s'", $row_awardsList['awardStyle']);
			$styles = mysql_query($query_styles, $brewing) or die(mysql_error());
			$row_styles = mysql_fetch_assoc($styles);
			$totalRows_styles = mysql_num_rows($styles);
			// Get real user names
			mysql_select_db($database_brewing, $brewing);
			$query_user2 = sprintf("SELECT * FROM users WHERE user_name = '%s'", $row_awardsList['brewBrewerID']);
			$user2 = mysql_query($query_user2, $brewing) or die(mysql_error());
			$row_user2 = mysql_fetch_assoc($user2);
			$totalRows_user2 = mysql_num_rows($user2);
					
			?>
            <tr <?php echo "style=\"background-color:$color\""; ?>>
              <td class="dataList"><?php if (isset($_SESSION["loginUsername"])) { if (($row_user['userLevel'] == "1") || ($row_recipeList['brewBrewerID'] == $loginUsername)) echo "<a href=\"admin/index.php?action=edit&dbTable=awards&id=".$row_awardsList['id']."\"><img src=\"".$imageSrc."pencil.png\" alt=\"Edit ".$row_awardsList['awardBrewName']."\" border=\"0\" align=\"absmiddle\"></a>&nbsp;"; } ?><a href="index.php?page=<?php $dbGo = substr($row_awardsList['awardBrewID'], 0, 1); if ($dbGo == "r") echo "recipe"; else echo "brewBlog";  ?>Detail&filter=<?php echo $row_awardsList['brewBrewerID']; ?>&id=<?php $brewID = ltrim ($row_awardsList['awardBrewID'], "rb"); echo $brewID; ?>"><?php echo $row_awardsList['awardBrewName']; ?></a></td>
              <td class="dataList">
			  <div id="moreInfo"><a href="#"><?php echo $row_awardsList['awardStyle']; ?>
			  <span>
			  <div id="wideWrapperReference">
			  <?php include ('reference/styles.inc.php'); ?>
			  </div>
			  </span>
			  </a>
			  </div>
			  </td>
              <td class="dataList"><?php if ($row_awardsList['awardContestURL'] != "") { ?><a href="<?php echo $row_awardsList['awardContestURL']; ?>" target="_blank"><?php } echo $row_awardsList['awardContest']; if ($row_awardsList['awardContestURL'] != "") { ?></a><?php } ?></td>
              <td class="dataList" nowrap><?php  $date = $row_awardsList['awardDate']; $realdate = dateconvert2($date,3); echo $realdate; ?></td>
			  <td class="dataList" nowrap width="16"><img src="<?php echo $imageSrc; ?><?php if ($row_awardsList['awardPlace'] == "best") echo "award_star_gold_3.png"; elseif ($row_awardsList['awardPlace'] == "1") echo "medal_gold_3.png"; elseif ($row_awardsList['awardPlace'] == "2") echo "medal_silver_3.png"; elseif ($row_awardsList['awardPlace'] == "3") echo "medal_bronze_3.png"; elseif ($row_awardsList['awardPlace'] == "entry") echo "tag_blue.png";  else echo "star.png";  ?>" border="0"/></td>
			  <td class="dataList" nowrap><?php if ($row_awardsList['awardPlace'] == "best") echo "Best In Show"; elseif ($row_awardsList['awardPlace'] == "1") echo "1st (Gold)"; elseif ($row_awardsList['awardPlace'] == "2") echo "2nd (Silver)"; elseif ($row_awardsList['awardPlace'] == "3") echo "3rd (Bronze)"; elseif ($row_awardsList['awardPlace'] == "honMen") echo "Honorable Mention"; else echo "Entry Only"; ?></td>
              <?php if (($row_pref['mode'] == "2") && ($filter == "all")) { ?><td  class="dataList"><a href="?page=awardsList&sort=awardBrewName&dir=ASC&filter=<?php echo $row_awardsList['brewBrewerID']; ?>"><?php echo $row_user2['realFirstName']."&nbsp;".$row_user2['realLastName']; ?></a></td><?php } ?>
            </tr>
            <?php if ($color == $color1) { $color = $color2; } else { $color = $color1; } ?>
            <?php } while ($row_awardsList = mysql_fetch_assoc($awardsList)); ?>
			<tr>
			<td colspan="7"><div id="paginate"><?php echo $total." Total Awards/Competition Entries"; if ($total > $display) echo "&nbsp;&nbsp;&nbsp;&#8226"; if ($view == "all") echo "&nbsp;&nbsp;&nbsp&#8226;&nbsp;&nbsp;&nbsp;"; if ($total > $display) { echo "&nbsp;&nbsp;&nbsp;"; paginate($display, $pg, $total);  if ($view == "limited") { ?>&nbsp;&nbsp;&nbsp;&#8226;&nbsp;&nbsp;&nbsp;<a href="?page=awardsList&filter=<?php echo $filter; ?>&view=all">Entire List of <?php if ($filter != "all") echo $row_user2['realFirstName']."'s "; if (($row_pref['mode'] == "2") && ($filter == "all")) echo "Club&nbsp;"; ?>Awards/Competition Entries</span></a><?php } } if ($view == "all") { ?><a href="?page=awardsList&filter=<?php echo $filter; ?>&view=limited">Limited List of <?php if (($row_pref['mode'] == "2") && ($filter != "all")) echo $row_user2['realFirstName']."'s "; if (($row_pref['mode'] == "2") && ($filter == "all")) echo "Club&nbsp;"; ?>Award/Competition Entries</a><?php } ?></div></td>
			</tr>
          </table>
		  </div>
		<?php }  } ?>
		<?php if ($page == "recipeDetail") { ?>
		  <div id="contentcolumn">
		  <?php // Include sections according to set preferences
			if ($row_pref['allowSpecifics'] == "Y") 	{ include ('sections/specifics.inc.php'); }
			if ($row_pref['allowGeneral'] == "Y") 		{ include ('sections/general.inc.php'); }
			if ($row_pref['allowRecipe'] == "Y") 		{ include ('sections/recipe.inc.php'); }
			if ($row_pref['allowProcedure'] == "Y") 	{ include ('sections/procedure.inc.php'); } 
			if ($row_pref['allowFermentation'] == "Y") 	{ include ('sections/fermentation.inc.php'); } 
			if ($row_pref['allowComments'] == "Y") 		{ include ('sections/notes.inc.php'); }
		  ?>
		</div><!-- End ContentColumn Div for Recipe Detail Page-->
		<?php } 
		if ($page == "login") { ?>
		<div id="contentWide">
				<?php include ('sections/login.inc.php'); ?>
		</div>
		<?php }  
		if ($page == "tools") { ?>
		<div id="contentWide">
				<?php include ('sections/tools.inc.php'); ?>
		</div>
		<?php } 
		if ($page == "about") { ?>
		<div id="contentcolumn">
			  	<?php include ('sections/about.inc.php'); ?>
		</div>
		<?php } 
		if ($page == "reference") { ?>
		<div id="contentWide">
			  	<?php include ('sections/reference.inc.php'); ?>
		</div>
		<?php } 
		if (($row_pref['allowCalendar'] == "Y") && ($page == "calendar")) { ?>
		<div id="contentWide">
			  	<?php include ('sections/calendar.inc.php'); ?>
		</div>
		<?php } 
		if (($row_pref['allowCalendar'] == "N") && ($page == "calendar")) { ?>
        <div id="contentWide">
        		<p class="error">This feature has been disabled by the site administrator.</p>
        </div> 
        <?php } ?>
		
		
		<?php if (($row_pref['mode'] == "2") && ($page == "members")) { ?>
		<div id="contentWide">
		<table class="dataTableAltColors">
		 <tr>
              <td nowrap class="dataHeading">Member Name&nbsp;<a href="?page=members&sort=realLastName&dir=ASC"><img src="<?php echo $imageSrc; ?>sort_up.gif" border="0" alt="Sort Ascending"></a><a href="?page=members&sort=realLastName&dir=DESC"><img src="<?php echo $imageSrc; ?>sort_down.gif" border="0" alt="Sort Descending"></a></td>
			  <td nowrap class="dataHeading" colspan="2">BrewBlogs</td>
			  <td nowrap class="dataHeading" colspan="2">Recipes</td>
			  <td nowrap class="dataHeading" colspan="2">Awards/Comps</td>
			<?php do { 
			mysql_select_db($database_brewing, $brewing);
			$query_count1 = sprintf("SELECT * FROM brewing WHERE brewBrewerID = '%s'", $row_members['user_name']);
			$count1 = mysql_query($query_count1, $brewing) or die(mysql_error());
			$row_count1 = mysql_fetch_assoc($count1);
			$totalRows_count1 = mysql_num_rows($count1);
			
			mysql_select_db($database_brewing, $brewing);
			$query_count2 = sprintf("SELECT * FROM recipes WHERE brewBrewerID = '%s'", $row_members['user_name']);
			$count2 = mysql_query($query_count2, $brewing) or die(mysql_error());
			$row_count2 = mysql_fetch_assoc($count2);
			$totalRows_count2 = mysql_num_rows($count2);
			
			mysql_select_db($database_brewing, $brewing);
			$query_count3 = sprintf("SELECT * FROM awards WHERE brewBrewerID = '%s'", $row_members['user_name']);
			$count3 = mysql_query($query_count3, $brewing) or die(mysql_error());
			$row_count3 = mysql_fetch_assoc($count3);
			$totalRows_count3 = mysql_num_rows($count3);
			?>
            <tr <?php echo "style=\"background-color:$color\""; ?>>
               <td width="35%" nowrap class="dataList"><?php echo $row_members['realFirstName']."&nbsp;".$row_members['realLastName']; ?></td>
			   <td width="5%" nowrap class="dataList"><?php echo $totalRows_count1."&nbsp;&nbsp;"; ?></td>
			   <td width="25%" nowrap class="dataList"><?php if ($totalRows_count1 > 0) { ?><a href="index.php?page=brewBlogList&filter=<?php echo $row_members['user_name']; ?>&sort=brewDate&dir=DESC">View &raquo;</a><?php } else echo "&nbsp;"; ?></td>
			   <td width="5%" nowrap class="dataList"><?php echo $totalRows_count2."&nbsp;&nbsp;"; ?></td>
               <td width="25%" nowrap class="dataList"><?php if ($totalRows_count2 > 0) { ?><a href="index.php?page=recipeList&filter=<?php echo $row_members['user_name']; ?>">View &raquo;</a><?php } else echo "&nbsp;"; ?></td>
			   <td width="5%" nowrap class="dataList"><?php echo $totalRows_count3; ?></td>
               <td width="25%" nowrap class="dataList"><?php if ($totalRows_count3 > 0) { ?><a href="index.php?page=awardsList&sort=awardBrewName&dir&ASC&filter=<?php echo $row_members['user_name']; ?>">View &raquo;</a><?php } else echo "&nbsp;"; ?></td>
			   
			</tr>
			<?php if ($color == $color1) { $color = $color2; } else { $color = $color1; } ?>
            <?php } while ($row_members = mysql_fetch_assoc($members)); ?> 
		</table>
	    </div>
		<?php } ?>
		
		
	</div> <!-- End ContentWrapper Div -->
	<!-- End Left Section -->

	<!-- Begin Right Section -->
	<div id="rightcolumn">
		<?php 
		 if ($page == "about") { include ('sections/list.inc.php'); }
		 if (($page == "brewBlogCurrent") || ($page == "brewBlogDetail")) { 
						if ($row_pref['allowPrintLog'] == "Y") 		{ include ('sections/printLog.inc.php'); }
						if ($row_pref['allowPrintRecipe'] == "Y") 	{ include ('sections/printRecipe.inc.php');  echo "&nbsp;"; }
						if ($row_pref['allowPrintXML'] == "Y") 		{ include ('sections/printXML.inc.php'); }
						if (($row_pref['mode'] == "2") && ($filter != "all")) echo "<br><span class=\"text_9\">&nbsp;<img src = \"".$imageSrc."calendar_view_month.png\" alt=\"Calendar\" border=\"0\" align=\"absmiddle\"><a href=\"index.php?page=calendar&filter=".$filter."\">&nbsp;View ".$row_user2['realFirstName']."'s Brewing Calendar</a></span>";
						if (((isset($_SESSION["loginUsername"])) && ($row_log['brewBrewerID'] == $loginUsername)) || ((isset($_SESSION["loginUsername"])) && ($row_user['userLevel'] == "1"))) { echo "<br><span class=\"text_9\">&nbsp;<img src=\"".$imageSrc."pencil.png\" alt=\"Edit\" border=\"0\" align=\"absmiddle\"><a href=\"admin/index.php?action=edit&dbTable=brewing&id=".$row_log['id']."\">&nbsp;Edit ".$row_log['brewName']."</a></span>"; }
						if ($row_pref['allowLabel'] == "Y") 		{ include ('sections/label.inc.php'); }
						if ($row_pref['allowAwards'] == "Y") 		{ include ('sections/awards.inc.php'); }
						if ($row_pref['allowRelated'] == "Y") 		{ include ('sections/related.inc.php'); } 
						include ('sections/list.inc.php');  
						if ($row_pref['allowStatus'] == "Y") 		{ include ('sections/status.inc.php'); } 
						if ($row_pref['allowUpcoming'] == "Y") 		{ include ('sections/upcoming.inc.php'); }		
		} 
		
		if ($page == "recipeDetail") { 
		// Include sidebar sections according to preferences
		    if ($row_pref['allowPrintRecipe'] == "Y") 	{ include ('sections/printRecipe.inc.php'); echo "&nbsp;"; }
			if ($row_pref['allowPrintXML'] == "Y") 		{ include ('sections/printXML.inc.php'); }
			if (((isset($_SESSION["loginUsername"])) && ($row_log['brewBrewerID'] == $loginUsername)) || ((isset($_SESSION["loginUsername"])) && ($row_user['userLevel'] == "1"))) echo "<br><img src=\"".$imageSrc."pencil.png\" alt=\"Edit\" border=\"0\" align=\"absmiddle\"><span class=\"text_10\"><a href=\"admin/index.php?action=edit&dbTable=recipes&id=".$row_log['id']."\">Edit ".$row_log['brewName']."</a></span>"; 
			if ($row_pref['allowAwards'] == "Y") 		{ include ('sections/awards.inc.php'); }
			if ($row_pref['allowRelated'] == "Y") 		{ include ('sections/related.inc.php'); } 
			if ($row_pref['allowList'] == "Y") 			{ include ('sections/list.inc.php'); } 
			
	    } ?>
	</div>
	
	<!-- End Right Section -->
		
	<!-- End Content Wrapper -->
	
	<div id="footer"><?php include ('includes/footer.inc.php'); ?></div>
	<!-- End Footer -->

<!-- End Overall Wrapper -->
    </div>
</div>	 
</body>
</html>
