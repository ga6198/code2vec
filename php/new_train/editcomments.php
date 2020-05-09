<?
ob_start();
session_start();
include ("includes/config.php");
include ("theme.php");
$page->settitle("Edit Comment");
$page->output();
if($edit)
    {
	$result = mysql_query("UPDATE blog_comments SET name='$name', email='$email', url='$url', comment='$comment' WHERE id=$id") or print (mysql_error());
	if ($result != false) 
		{
		echo "<p>The comment has successfully been edited!</p>";
		}
	}


if($delete)
    {
	$result = mysql_query("DELETE FROM blog_comments WHERE id=$id") or print (mysql_error());
	if ($result != false) 
		{
		echo "<p>The comment has successfully been deleted!</p>";
		}
	}

if ($_GET["id"])
    {

	$result = mysql_query ("SELECT * FROM blog_comments WHERE id=$_GET[id]") or print(mysql_error());
	while ($row = mysql_fetch_array($result))
		{
			$old_name = $row["name"];
			$old_email = $row["email"];
			$old_url = $row["url"];
			$old_comment = $row["comment"];
		}

	echo "<form method=\"post\" action=\"$PHP_SELF\">
	<input type=\"hidden\" name=\"id\" value=\"$_GET[id]\">
		<table>
			<tr>
				<td>
					<b>name:</b> <input type=\"text\" name=\"name\" size=\"40\" value=\"$old_name\">
				</td>
			</tr>
			<tr>
				<td>
					<b>email:</b> <input type=\"text\" name=\"email\" size=\"40\" value=\"$old_email\">
				</td>
			</tr>
			<tr>
				<td>
					<b>url:</b> <input type=\"text\" name=\"url\" size=\"40\" value=\"$old_url\">
				</td>
			</tr>
			<tr>
				<td>
					<textarea cols=\"80\" rows=\"20\" name=\"comment\">$old_comment</textarea>
				</td>
			</tr>
			<tr>
				<td>
					<input type=\"submit\" name=\"edit\" value=\"Edit Comment\"> <input type=\"submit\" name=\"delete\" value=\"Delete Comment\"> <input type=\"submit\" value=\"Never Mind\">
				</td>
			</tr>
		</table>
	</form>";

	}
else
	{

	$result = mysql_query("SELECT entry AS get_group FROM blog_comments GROUP BY get_group DESC LIMIT 10") or print (mysql_error());

	while($row = mysql_fetch_array($result))
		{
		$get_group = $row["get_group"];
		
		echo "<p>";

		$result2 = mysql_query("SELECT timestamp, title FROM blog WHERE id='$get_group'");
		while($row2 = mysql_fetch_array($result2))
			{
			$date = date("l F d Y",$row2["timestamp"]);
			$title = $row2["title"];
			echo "<b>$date - $title</b><br />";
			}

		$result3 = mysql_query("SELECT * FROM blog_comments WHERE entry='$get_group' ORDER BY timestamp DESC");
		while($row3 = mysql_fetch_array($result3))
			{
			$id = $row3["id"];
			$name = $row3["name"];
			$comment = $row3["comment"];
			$date = date("l F d Y",$row3["timestamp"]);

			if (strlen($comment) > 75)
				{
				$comment = substr($comment,0,75);
				$comment = "$comment...";
				}

			echo "<a href=\"editcomments.php?id=$id\">$comment</a><br />Commented by $name @ $date<br /><br />";
			}

		echo "</p>";

		}
	}

mysql_close();
$content->output();
$close->output();
?>