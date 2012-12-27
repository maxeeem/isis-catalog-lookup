<html>
<title>Isis Catalog</title>
<head></head>
<body>

<?php

require "../parser/mysqlConnect.php";


if (1) { // FUNCTiONS

function wrap($item) {

	$result = "<option value='{$item}'>{$item}</option>";
	
	return $result;

}
	
function doSearch($linecode, $partnumber) {

	global $mysqli;

	$search = "SELECT * FROM `table 1` WHERE `LineCode` = '{$linecode}' AND `Part Number` = '{$partnumber}'";
	
	$res = mysqli_query($mysqli, $search);

	$res->data_seek(0);
	while ($row = $res->fetch_assoc()) {
		$rows[] = $row;
	}
	
	return $rows;
	
}	

function getBrands() {

	global $mysqli;

	$search = "SELECT DISTINCT `LineCode` FROM `table 1`";
	
	$res = mysqli_query($mysqli, $search);

	$res->data_seek(0);
	while ($row = $res->fetch_assoc()) {
		$brands[] = $row['LineCode'];
	}

	return $brands;

}
	
function display($results) {

	foreach ($results as $res) @$avail += $res['Avail'];

	echo "<center><div><strong>You searched for " . $results[0]['LineCode'] . $results[0]['Part Number'] . "</strong><br />";

	echo "<table border='1' cellpadding='5'><tr><th align=left>Available:</th><td>{$avail}</td></tr>";
	
	echo "<tr><th align=left>Price 2:</th><td>$" . $results[0]['Price 2'] . "</td></tr>";
	
	echo "<tr><th align=left>Price 5:</th><td>$" . $results[0]['Price 5'] . "</td></tr>";
	
	echo "<tr><th align=left>Price 9:</th><td>$" . $results[0]['Price 9'] . "</td></tr></table></div></center>";
	
	// var_dump($results);
	
}

}

if (1) { // VARiABLES

foreach (getBrands() as $brand) @$select .= wrap($brand);

@$results = doSearch($_POST['linecode'], $_POST['partnumber'])
						or die("<center><h3>Could not find <u>{$_POST['linecode']}{$_POST['partnumber']}</u>. Please check the part number and <a href='{$_SERVER['PHP_SELF']}'>try again</a>.</h3></center>");

$form = <<<EOT
				<br />
				<center><h2>Select <u>line code</u> and then type in a <u>part number</u> below:</h2>
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


if (empty($_POST)) echo $form;

else {

echo $form;

display($results);

}

?>

</body>
</html>