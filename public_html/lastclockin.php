<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title>Last User to Clock In Check</title>
</head>

<body>

<?php
$con = mysql_connect("localhost","root","k4b00mk4b00m");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }

mysql_select_db("timeclock", $con);

$result = mysql_query("SELECT * FROM record ORDER BY id DESC LIMIT 1");

while($row = mysql_fetch_array($result))
	{
		echo "Last clock in or clock out: ";
		echo "<br />";
  		if ($row['clockout'] == 0) {
			echo '<img src="userimg/' . $row['name'] . '.jpg" border="0"></a><br>';
			//echo " at ";
			//echo date("h:i:s",$row['clockout']);
		}
		else {
			echo '<img src="userimg/' . $row['name'] . '.jpg" border="0"></a><br>';
			echo " at ";
			echo date("h:i:s",$row['clockin']);
		}
  }
  


mysql_close($con);
?>

</body>
</html>
