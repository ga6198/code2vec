<!--This file is part of mBlogger.-->

<!--mBlogger - A lightweight, simplistic blog engine.-->
<!--Copyright (C) 2008 Ryan Morrison-->

<!--mBlogger is free software: you can redistribute it and/or modify-->
<!--it under the terms of the GNU General Public License as published by-->
<!--the Free Software Foundation, either version 3 of the License, or-->
<!--(at your option) any later version.-->

<!--mBlogger is distributed in the hope that it will be useful,-->
<!--but WITHOUT ANY WARRANTY; without even the implied warranty of-->
<!--MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the-->
<!--GNU General Public License for more details.-->

<!--You should have received a copy of the GNU General Public License-->
<!--along with mBlogger.  If not, see <http://www.gnu.org/licenses/>.-->

<html>
<head>
<link rel="stylesheet" type="text/css"
href="style.css" />
<?php include('config.php'); echo "<title>$mblogger_title - View Post</title>"; ?>
</head>
<?php echo "<body bgcolor='$mblogger_bgcolor'>"?>
<?php include("header.php"); ?>
<center><h3>View Post</h3></center>
<?php
include("db-connect.php");
$query = "SELECT id, name, subject, message, posted FROM posts WHERE id = '$_GET[postID]'";
$result = mysql_query($query) or die(mysql_error());
while($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
	echo "<div class='posttitle'>";
	echo "<h3>" . $row['subject'] . "</h3>";
	echo "</div>";
	echo "<div class='postbody'>";
	echo "<p> Posted by: " . $row['name'] . " on " . $row['posted'] . "</p>";
	echo "<p>" . $row['message'] . "</p>";
	echo "</div>";
	$postID = $row['id'];
}
$query = "SELECT user, comment FROM comments WHERE postid = '$_GET[postID]'";
$result = mysql_query($query) or die(mysql_error());
echo "<div class='posttitle'>";
echo "<h3>Comments</h3>";
echo "</div>";
$numcomments = 0;
while($row = mysql_fetch_array($result, MYSQL_ASSOC))
{	
	echo "<div class='postbody'>";
	$numcomments = $numcomments + 1;
	echo "<h3 align='right'>Comment by: " . $row['user'] . "</h3><br>";
	echo "<p>" . $row['comment'] . "</p>";
	echo "</div>";
}
if($numcomments == 0)
{
	echo "<div class='postbody'>";
	echo "<p>This post has no comments.</p>";
	echo "</div>";
}
mysql_free_result($result);
echo "</p>";
mysql_close($connection);
echo '<form method="post" action="addcomment.php?postID=' . $postID . '">';
?>
<div class='posttitle'>
<h3>Post a Comment</h3><br>
</div>
<div class='postbody'>
<p><b>Comment Author: </b><input name="commentAuthor" type="text"></p>
<p><b>Comment:</b><br><textarea name="commentText" rows="10" cols="40"></textarea></p>
<input name="submitcomment" type="submit" value="Submit">
</div>
</form>
<?php include("footer.php"); ?>
</body>
</html>