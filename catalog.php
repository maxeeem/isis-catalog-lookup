<html>
<title>Isis Catalog</title>
<head></head>
<body>

<?php

{# iNCLUDES

require "../parser/mysqlConnect.php";

}

{# FUNCTiONS

function makeSelect($linecard) {

	while ($line = fgetcsv($linecard)) $brands[] = array($line[0], $line[1]); fclose($linecard);

	foreach ($brands as $brand) @$select .= "<option value='{$brand[0]}'>{$brand[1]}</option>";
	
	return $select;

}

function doSearch($linecode, $partnumber) {

	global $mysqli;

	$search = "SELECT * FROM `table 1` WHERE `LineCode` = '{$linecode}' AND `Part Number` = '{$partnumber}'";
	
	$res = mysqli_query($mysqli, $search);

	$res->data_seek(0);	
	
	while ($row = $res->fetch_assoc()) $rows[] = $row;
	
	if (empty($rows)) exit("<center>Could not find {$linecode}{$partnumber}. Please check the part number and try again.</center>");	
	
	return $rows;
	
}

function showResults() {

	$results = doSearch($_POST['linecode'], $_POST['partnumber']);
	
	foreach ($results as $res) @$avail += $res['Avail'];

	echo "<center><div><strong>You searched for " . $results[0]['LineCode'] . $results[0]['Part Number'] . "</strong><br />";

	echo "<table border='1' cellpadding='5'><tr><th align=left>Avail</th><td><center>{$avail}</center></td></tr>";
	
	echo "<tr><th align=left>Price 2</th><td>$" . $results[0]['Price 2'] . "</td></tr>";
	
	echo "<tr><th align=left>Price 5</th><td>$" . $results[0]['Price 5'] . "</td></tr>";
	
	echo "<tr><th align=left>Price 9</th><td>$" . $results[0]['Price 9'] . "</td></tr></table></div></center>";
	
	// var_dump($results);
	
}

}

{# VARiABLES

$linecard = fopen("linecard.csv", "r") or die("<p>Couldn't find the Linecard file.</p>");

$select = makeSelect($linecard);

$form = <<<EOT
				<br />
				<center><h2>Select <u>manufacturer</u> and then enter <u>part number</u> below:</h2>
				<br />
				<form action="{$_SERVER['PHP_SELF']}" method="POST">
				<select name="linecode">
				{$select}
				</select>
				<input name="partnumber" type="text"/>
				<input type="submit" value="Search" />
				</form>
				</center>
EOT;

}

{# MAiN

echo $form;

if (!empty($_POST)) showResults();

}

?>

</body>
</html>