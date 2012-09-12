<?php

include 'functions.php';

//Grab the name of the person clocking in, prevent SQL injection
$name = mysql_real_escape_string($_REQUEST['name']);

if ($name != '')	//we need to process a user request
{
	if (clocked_in($name) == 'clocked in')
	{
		$time = time();
		mysql_query("UPDATE record SET clockout = '$time' WHERE name = '$name' AND clockin != '' AND clockout = '0'");
		mysql_query("DELETE FROM latestaction WHERE id > 0");
		mysql_query("INSERT INTO latestaction(id, name, time, action) VALUES('', '$name', '$time', 'clockout')");
	}
	else
	{
		$time = time();
		mysql_query("INSERT INTO record(id, name, clockin, clockout) VALUES('', '$name', '$time', '')");
		mysql_query("DELETE FROM latestaction WHERE id > 0");
		mysql_query("INSERT INTO latestaction(id, name, time, action) VALUES('', '$name', '$time', 'clockin')");
	}
}
?>

<html>
<head><title>Team RUSH 27 Time Clock</title>

<script type="text/javascript" src="ajax.js"></script>

</head>

<span id="ajax">Loading...</span>

<?php mysql_close($dblink); ?>

</html>
