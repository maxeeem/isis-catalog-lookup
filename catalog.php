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

	while ($line = fgetcsv($linecard)) $brands[] = array($line[0], $line[1]);
	
	fclose($linecard);

	foreach ($brands as $brand) {
	
		if (!empty($_POST) && $_POST['linecode'] == $brand[0]) @$select .= "<option value='{$brand[0]}' selected>{$brand[1]}</option>";
		
		else @$select .= "<option value='{$brand[0]}'>{$brand[1]}</option>";
	
	}
	
	return $select;

}

function loadVariations($var) {

	$file = fopen($var, "r");
	
	while ($row = fgetcsv($file)) $rows[$row[0]][$row[1]] = $row[2];

	fclose($file);
	
	return $rows;

}

function getVariations($delimiters) {
	
	global $mysqli, $var;
	
	$query = "SELECT DISTINCT `LineCode`, `Part Number` FROM `table 1` where ";
	
	foreach ($delimiters as $del) $query .= "`Part Number` like '%{$del}%' or ";
	
	$query = rtrim($query, ' or ');
	
	$res = mysqli_query($mysqli, $query);
	
	$res->data_seek(0);	
	
	while ($row = $res->fetch_assoc()) {
	
		$key = str_replace($delimiters, '', $row['Part Number']);

		$rows[$row['LineCode']][$key] = array($row['LineCode'], $key, $row['Part Number']);

	}
	
	if (empty($rows)) exit("<center>Could not retrieve SKU variations.</center>");	
	
	$output = fopen($var, "w");
	
	foreach ($rows as $r) foreach ($r as $data) fputcsv($output, $data);
	
	fclose($output);
	
	return $rows;

}

function doSearch($linecode, $partnumber) {

	global $mysqli, $variations;
	
	if (array_key_exists($partnumber, $variations[$linecode])) $rows = doSearch($linecode, $variations[$linecode][$partnumber]);
	
	$search = mysqli_prepare($mysqli, "SELECT * FROM `table 1` WHERE `LineCode` = ? AND `Part Number` = ?");
	
	mysqli_stmt_bind_param($search, "ss", $linecode, $partnumber);
	
	mysqli_stmt_execute($search);
	
	$res = mysqli_stmt_get_result($search);

	mysqli_stmt_close($search);
	
	$res->data_seek(0);	
	
	while ($row = $res->fetch_assoc()) $rows[] = $row;
	
	if (!isset($rows)) exit("<center>Could not find {$linecode}{$partnumber}. Please check the part number and try again.</center>");
	
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
	
}

}

{# VARiABLES

$linecard = fopen("linecard.csv", "r") or die("<p>Couldn't find the Linecard file.</p>");

$select = makeSelect($linecard);

$delimiters = array('-', '.');

$var = "variations.csv";

$variations = (file_exists($var) && (date('Ymd', filemtime($var)) == date('Ymd'))) ? loadVariations($var) : getVariations($delimiters);

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