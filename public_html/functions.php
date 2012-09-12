<?php

/*MYSQL LINK*/
$dblink = mysql_connect('localhost', 'root', 'k4b00mk4b00m') or die('Could not connect to the database.');
mysql_select_db('timeclock') or die('Could not select the database.');
/*END MYSQL LINK*/

function user_status($name)
{
	$userclockedin = mysql_query("SELECT name,clockin FROM record WHERE name = '$name' AND clockin != '' AND clockout = '0'") or die('DB query failed.');

	if (mysql_num_rows($userclockedin) != 0)	//If this is true, the user is clocked in
	{
		$row = mysql_fetch_assoc($userclockedin);
		$clockedintime = time() - $row['clockin'];
		$clockedintime = round((($clockedintime/60)/60),2);
		$returnstring = '<font color="green">' . $clockedintime . 'h</font>';
		return($returnstring);
	}
	else
	{
		$returnstring = '<font color="red">OUT</font>';
		return($returnstring);
	}

	mysql_free_result($userclockedin);
}

function clocked_in($name)
{
	$userclockedin = mysql_query("SELECT name FROM record WHERE name = '$name' AND clockin != '' AND clockout = '0'") or die('DB query failed.');

	if (mysql_num_rows($userclockedin) != 0)	//If this is true, the user is clocked in
	{
		return("clocked in");
	}
	else
	{
		return("clocked out");
	}
		
	mysql_free_result($userclockedin);
}

function draw_user_grid()
{

	echo '<table cellpadding="5"><tr><td><a href="?"><img src="rush.jpg" border="0"></a></td><td align="bottom"><b>Team Time Clock</b> - ' . date("h:i:sa.") . '<br>Place ID on reader or click your name to clock in/out.<br></td><td>';
	echo count_total_hours();
	echo '</td></tr></table>';
	echo "<table border='1'><tr>\n";

	//SQL Query
	$sqldraw = mysql_query("SELECT name FROM users WHERE id > 0 ORDER BY name") or die('DB query failed.');

	$counter = 1;

	//Echo out user data
	while ($row = mysql_fetch_assoc($sqldraw))
	{
		echo '<td><center><a href="?name=' . $row['name'] . '"><img src="userimg/' . $row['name'] . '.jpg" border="0" width="80" height="54"></a><br>' . user_status($row['name']) . '</center></td>';
		echo "\n";
		if (($counter % 11) == 0)
		{
			echo '</tr><tr>';
		}
		$counter++;
	}
	mysql_free_result($sqldraw);
	echo "</tr></table>";
}

function count_total_hours()
{
	//SQL Query
	$sqldraw = mysql_query("SELECT clockin,clockout FROM record WHERE id > 0 ORDER BY id") or die('DB query failed.');

	$totalhours = 0;
	$currentlyaccumulating = 0;

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
		}
	}
	mysql_free_result($sqldraw);
	$totalhours = round((($totalhours/60)/60),2);
	$currentlyaccumulating = round((($currentlyaccumulating/60)/60),2);
	echo "Total Hours Logged: " . $totalhours . "h<br>";
	echo "Currently Accumulating: " . $currentlyaccumulating . "h<br>";
}

function last_clock_in()
{
	echo '<table width="950"><tr>';
	mysql_select_db("timeclock");

	$result = mysql_query("SELECT * FROM latestaction ORDER BY action");
	
	while($row = mysql_fetch_array($result))
  	{
  		if ($row['action'] != 'clockin') 
		{
			echo '<td width="1">';
			echo '<img src="userimg/' . $row['name'] . '.jpg" border="0" width="80" height="54"></a>';
			echo '</td>';
			echo '<td>';
			echo "<br /><h2>" . $row['name'] . " clocked out at: ";
			echo date("h:i:s A",$row['time']);
			echo '</h2></td>';
		}
		else 
		{
			echo '<td width="1">';
			echo '<img src="userimg/' . $row['name'] . '.jpg" border="0" width="80" height="54"></a>';
			echo '</td>';
			echo '<td>';
			echo "<br /><h2>" . $row['name'] . " clocked in at: ";
		    	echo date("h:i:s A",$row['time']);
		  	echo '</h2></td>';
		}
  	}

	echo '</tr></table>';
}

?>
