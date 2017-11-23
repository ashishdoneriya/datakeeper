<?php

header("Access-Control-Allow-Methods: GET");

include_once './config/database.php';
include_once './utils.php';
session_start();
$userId = $_SESSION['userId'];
$database = new Database();
$db = $database->getConnection();
$tableName = htmlspecialchars(strip_tags($_GET['tableName']));
$searchQuery = htmlspecialchars(strip_tags($_GET['searchQuery']));

$finalFields = getFields($db, $userId, $tableName);
if ($finalFields == null){
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}


$fieldsArr = array();
foreach($finalFields as $field) {
	array_push($fieldsArr, $field['id']);
}

$fieldsString = join(',', $fieldsArr);
$searchQuery = htmlspecialchars(strip_tags($_GET['searchQuery']));
$pageNumber = htmlspecialchars(strip_tags($_GET['pageNumber']));
$maximumResults = htmlspecialchars(strip_tags($_GET['maximumResults']));
$sortBy = htmlspecialchars(strip_tags($_GET['sortBy']));
$order = htmlspecialchars(strip_tags($_GET['order']));

$whereAdded = false;

if ($sortBy == null) {
	$sortBy = $finalFields[0]['id'];
}
if ($order == null) {
	$order = 'asc';
}
$searchQuery = getSearchQuery($searchQuery, $finalFields);
$query = "select $fieldsString from $tableName $searchQuery order by $sortBy $order";
if ($pageNumber != null && $maximumResults != null) {
	$max = (int) $maximumResults;
	$firstRow = ((int) $pageNumber - 1)  * $max;
	$query = $query . " limit " . $firstRow . "," . $max;
}

$rows = $db->query($query);
$result = array();
while($row = $rows->fetch()) {
	$temp = array();
	foreach ($finalFields as $field) {
		$temp[$field['id']] = $row[$field['id']];
	}
	array_push($result, $temp);
}
echo json_encode($result);

function getSearchQuery($query, $fields) {

	if ($query == null) {
		return "";
	}
	$query = strval($query);
	$query = trim($query . "");

	if (strlen($query) == 0) {
		return "";
	}
	$searchArr = array();
	foreach($fields as $field) {
		switch ($field['type']) {
			case 'Text' :
			case 'Select' :
			case 'Checkbox' :
			case 'Radio Button' :
				array_push($searchArr, $field['id'] . " like '%". $query . "%'");
				continue;
			case 'Number' :
			case 'Id' :
				if (is_numeric($query) == true) {
					array_push($searchArr, $field['id'] . "=". $query);
				}
				continue;
			case 'Decimal Number' :
				if (is_float($query) == true || is_numeric($query) == true) {
					array_push($searchArr, $field['id'] . "=". $query);
				}
				continue;
			case 'Date' :
			case 'Time' :
			case 'Date Time' :
				array_push($searchArr, $field['id'] . "='". $query . "'");
		}
	}
	return " where " . join(" or ", $searchArr) . " ";
}
?>