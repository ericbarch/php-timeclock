<html>
<head><title>Team RUSH 27 Time Clock [Administration Panel]</title></head>

<a href="?"><img src="../rush.jpg" border="0"></a><br><b>Team Time Clock - System Administration</b><br>

<?php


/*MYSQL LINK*/
$dblink = mysql_connect('localhost', 'root', 'k4b00mk4b00m') or die('Could not connect to the database.');
mysql_select_db('timeclock') or die('Could not select the database.');
/*END MYSQL LINK*/


//Grab our variables and make sure SQL can't be injected (prevents hacking)
$name = mysql_real_escape_string($_REQUEST['name']);
$adduser = mysql_real_escape_string($_REQUEST['adduser']);
$deluser = mysql_real_escape_string($_REQUEST['deluser']);
$action = mysql_real_escape_string($_REQUEST['action']);
$delid = mysql_real_escape_string($_REQUEST['delid']);

//Add user routine
if ($adduser != '')
{
	mysql_query("INSERT INTO users(id, name) VALUES('', '$adduser')");
	echo '<font color="green">User Added.</font><br>';
}

//Delete user routine
if ($deluser != '')
{
	mysql_query("DELETE FROM users WHERE name = '$deluser'") or die('DB query failed.');
	echo '<font color="red">User deleted.</font><br>';
}

//Delete timeclock entry routine
if ($delid != '')
{
	mysql_query("DELETE FROM record WHERE id = '$delid'") or die('DB query failed.');
	echo '<font color="red">Record deleted.</font><br>';
}

//Clock out all users routine
if ($action == 'clockout')
{
	//SQL query
	$sqldraw = mysql_query("SELECT clockout,id FROM record WHERE id > 0 ORDER BY id") or die('DB query failed.');

	//Clock out users routine
	while ($row = mysql_fetch_assoc($sqldraw))
	{
		if ($row['clockout'] == '0')
		{
			$totalhours = $totalhours + ($row['clockout'] - $row['clockin']);
			$time = time();
			$id = $row['id'];
			mysql_query("UPDATE record SET clockout = '$time' WHERE id = '$id'");
		}
	}
	mysql_free_result($sqldraw);
}

function count_total_hours()
{
	//SQL query
	$sqldraw = mysql_query("SELECT clockin,clockout FROM record WHERE id > 0 ORDER BY id") or die('DB query failed.');

	$totalhours = 0;
	$currentlyaccumulating = 0;
	$totalusersclocked = 0;

	//Counting routine
	while ($row = mysql_fetch_assoc($sqldraw))
	{
		if ($row['clockout'] != '0')
		{
			$totalhours = $totalhours + ($row['clockout'] - $row['clockin']);
		}
		else
		{
			$totalhours = $totalhours + (time() - $row['clockin']);
			$currentlyaccumulating = $currentlyaccumulating + (time() - $row['clockin']);
			$totalusersclocked++;
		}
	}

	mysql_free_result($sqldraw);
	$totalhours = round((($totalhours/60)/60),2);
	$currentlyaccumulating = round((($currentlyaccumulating/60)/60),2);
	echo "Total Hours Logged: " . $totalhours . "h<br>";
	echo "Currently Accumulating: " . $currentlyaccumulating . "h<br>";
	echo 'Users Clocked In: ' . $totalusersclocked . ' <a href="?action=clockout">[clock out all]</a><br>';
}

function draw_user_grid()
{
	//SQL Query
	$sqldraw = mysql_query("SELECT name FROM users WHERE id > 0 ORDER BY name") or die('DB query failed.');

	$usercount = 0;
	$average_contributed = 0;

	//This loop grabs each user and outputs their data
	while ($row = mysql_fetch_assoc($sqldraw))
	{
		//SQL Query for fetching hours
		$sqldraw2 = mysql_query("SELECT clockin,clockout,id FROM record WHERE name = '$row[name]' ORDER BY id") or die('DB query failed.');

		$totalhours = 0;

		//This loop grabs hour data for each user
		while ($row2 = mysql_fetch_assoc($sqldraw2))
		{
			if ($row2['clockout'] != '0')
			{
				$totalhours = $totalhours + ($row2['clockout'] - $row2['clockin']);
			}
			else
			{
				$totalhours = $totalhours + (time() - $row2['clockin']);
			}
		}

		$totalhours = round((($totalhours/60)/60),2);

		$usercount++;
		$average_contributed += $totalhours;

		$user = $row['name'];
		echo '<a href="?name=' . $user . '">' . $user . '</a> - ' . $totalhours . 'h<br />';
	}
	mysql_free_result($sqldraw);
	$average_contributed = $average_contributed / $usercount;
	echo '<br /><b>Average Hours Committed</b> ' . round($average_contributed,2);
}

function user_logged_records($name)
{
	//SQL Query
	$sqldraw = mysql_query("SELECT clockin,clockout,id FROM record WHERE name = '$name' ORDER BY id") or die('DB query failed.');

	echo '<table border="1"><tr><td>Clock In</td><td>Clock Out</td><td>Hours</td><td>Delete Record</td></tr>';

	$totalhours = 0;

	//Parse out data
	while ($row = mysql_fetch_assoc($sqldraw))
	{
		if ($row['clockout'] != '0')
		{
			$totalhours = $totalhours + ($row['clockout'] - $row['clockin']);
			$currenttime = ($row['clockout'] - $row['clockin']);
			$currenttime = round((($currenttime/60)/60),2);
			$currentid = $row['id'];
			echo '<tr><td>' . date("M j, Y - h:ia",$row['clockin']) . '</td><td>' . date("M j, Y - h:ia",$row['clockout']) . '</td><td>' . $currenttime . '</td><td><a href="?delid=' . $currentid . '&name=' . $name . '">[delete]</a></td></tr>';
		}
		else
		{
			$totalhours = $totalhours + (time() - $row['clockin']);
			$currenttime = (time() - $row['clockin']);
			$currenttime = round((($currenttime/60)/60),2);
			$currentid = $row['id'];
			echo '<tr><td>' . date("M j, Y - h:ia",$row['clockin']) . '</td><td><font color="green">Clocked In</font></td><td>' . $currenttime . '</td><td><a href="?delid=' . $currentid . '&name=' . $name . '">[delete]</a></td></tr>';
		}
	}

	$totalhours = round((($totalhours/60)/60),2);

	echo '</table><b>Total Hours Committed:</b> ' . $totalhours . '<br>';
	mysql_free_result($sqldraw);
}

//Query to search for name
$sqlauth = mysql_query("SELECT name FROM users WHERE name = '$name'") or die('DB query failed.');

if (mysql_num_rows($sqlauth) != 0)	//If the user exists, we can process their request
{
	echo 'User admin for: ' . $name . ' <a href="?deluser=' . $name . '">[delete user]</a><br><img src="../userimg/' . $name . '.jpg"><br><br><b>User Log:</b><br>';

	user_logged_records($name);

	echo '<br><a href="?">[Return to Admin Panel]</a>';

	die();
}


mysql_free_result($sqlauth);

echo "Please click a name to manage that account<br><br>\n";
echo '<FORM action="?" method="post">
<input type="text" name="adduser" size="10" value=""></input>
<INPUT type="submit" value="Add User"></FORM><table border="1"><tr><td width="200">';
draw_user_grid();
echo '</td><td valign="top">';
count_total_hours();
echo '<br><a href="../index.php">[Return to Timeclock]</a></td></tr></table>';

mysql_close($dblink);

?>
</html>
