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
if ($finalFields == null) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}

$query = "select count(*) from " . $tableName . getSearchQuery($searchQuery, $finalFields);
$rows = $db->query($query);
$row = $rows->fetch();
echo $row[0];

function getSearchQuery($query, $fields) {
	if ($query == null) {
		return "";
	}
	$query = trim($query);
	if (empty($query)) {
		return "";
	}
	$searchArr = array();
	foreach ($fields as $field) {
		switch ($field['type']) {
		case 'Text':
		case 'Select':
		case 'Checkbox':
		case 'Radio Button':
			array_push($searchArr, $field['fieldId'] . " like '%" . $query . "%'");
			continue;
		case 'Number':
		case 'Id':
			if (is_numeric($query) == true) {
				array_push($searchArr, $field['fieldId'] . "=" . $query);
			}
			continue;
		case 'Decimal Number':
			if (is_float($query) == true) {
				array_push($searchArr, $field['fieldId'] . "=" . $query);
			}
			continue;
		case 'Date':
		case 'Time':
		case 'Date Time':
			array_push($searchArr, $field['fieldId'] . "='" . $query . "'");
		}
	}
	return " where " . join(" or ", $searchArr) . " ";
}
?>