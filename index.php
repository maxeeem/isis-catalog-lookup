<html>
<title>Isis Catalog</title>
<head>
<link href="css/style.css" rel="stylesheet">
</head>
<body>

<?php

{# iNCLUDES

require_once "/data/mysql/mysqlConnect.php";

}

{# FUNCTiONS

function makeSelect($linecard) {

	while ($line = fgetcsv($linecard)) $brands[] = array($line[0], $line[1]);
	
	fclose($linecard);

	foreach ($brands as $brand) {

		$item = $brand[0] . ' ' . $brand[1];
	
		if (!empty($_POST) && $_POST['linecode'] == $brand[0]) @$select .= "<option value='{$brand[0]}' selected>{$item}</option>";
		
		else @$select .= "<option value='{$brand[0]}'>{$item}</option>";
	
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
	
	$query = "SELECT DISTINCT `LineCode`, `Part Number` FROM `current` where ";
	
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
	
	if (array_key_exists($linecode, $variations) && array_key_exists($partnumber, $variations[$linecode])) {
	
		$rows = doSearch($linecode, $variations[$linecode][$partnumber][2]);
	
	}
	
	$search = $mysqli->prepare("SELECT * FROM `current` WHERE `LineCode` = ? AND `Part Number` = ?");
	
	$search->bind_param("ss", $linecode, $partnumber);
	
	$search->execute();
	
	$res = $search->get_result();
	
	$search->close();	
	
	while ($row = $res->fetch_assoc()) $rows[] = $row;
	
	if (!isset($rows)) exit("<center><a class='error-text'>Could not find {$linecode}{$partnumber}. Please check the part number and try again.</a></center>");
	
	return $rows;
	
}

function showResults() {

	$results = doSearch($_POST['linecode'], strtoupper($_POST['partnumber']));
	
	foreach ($results as $res) @$avail += $res['Avail'];

	echo "<center><strong>You searched for " . $results[0]['LineCode'] . $results[0]['Part Number'] . "</strong><br /><br />";

	echo "<table class='table-text'><tr><th align=left>Available</th><td><center>{$avail}</center></td></tr>";
	
	echo "<tr><th align=left>Price 2</th><td>$" . $results[0]['Price 2'] . "</td></tr>";
	
	echo "<tr><th align=left>Price 5</th><td>$" . $results[0]['Price 5'] . "</td></tr>";
	
	echo "<tr><th align=left>Price 9</th><td>$" . $results[0]['Price 9'] . "</td></tr></table></div></center>";
	
}

}

{# VARiABLES

$linecard = fopen("files/linecard.csv", "r") or die("<p>Couldn't find the Linecard file.</p>");

$select = makeSelect($linecard);

$delimiters = array('-', '.');

$var = "files/variations.csv";

$variations = (file_exists($var) && (date('Ymd', filemtime($var)) == date('Ymd'))) ? loadVariations($var) : getVariations($delimiters);

$dbStatus = (empty($_POST)) ? $dbStatus : NULL;

$page = <<<CSS
				<center><div id="orw-container2" align="center">
				<div class='header-images'>
				<a href="{$_SERVER['PHP_SELF']}"><img src="images/orw_logo.png" border="0" width="289" height="124"></a>
				<a href="{$_SERVER['PHP_SELF']}" class="title-catalog">CATALOG</a>
				</div>    
				<form action="{$_SERVER['PHP_SELF']}" method="POST"><p>
				<center>
				<select name="linecode" class="button drop">
				<option value=''>Select Manufacturer</option>
				{$select}
				</select>
				<input type="text" class="button partnum" name="partnumber" placeholder="Enter Part Number"/>
				</center></p>
				<input class="button red" type="submit" value="Search"></p>
				</form>
				{$dbStatus}
				</div>
				</center>
CSS;

}

{# MAiN

echo $page;

if (!empty($_POST)) showResults();
	
}

?>

</body>
</html>
