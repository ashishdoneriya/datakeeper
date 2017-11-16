<?php

header("Access-Control-Allow-Methods: GET");

include_once './config/database.php';
session_start();
$userId = $_SESSION['userId'];
$database = new Database();
$db = $database->getConnection();
$tableName = $_GET['tableName'];
$rows = $db->query("select * from users_tables where tableName='$tableName'");
$row = $rows->fetch();
$publicRole = json_decode($row['publicRole'], true);
$finalFields = null;
if ($userId == null) {
	if ($publicRole['read'] == false) {
		header('HTTP/1.0 401 Unauthorized');
		echo 'You are not authorized.';
		return;
	}

	// get public fields
	$fields = json_decode($row['fields'], true);
	$publicFields = array();
	foreach($fields as $field) {
		if ($field->isVisible == true) {
			array_push($publicFields, $field);
		}
	}
	$finalFields = $publicFields;
} else if ($userId == $row['userId']) {
	//get private fields
	$finalFields = json_decode($row['fields'], true);
} else {
	// checking if the other user has permissions to read or not
	$rows = $db->query("select userId, tableName, role from guest_permissions where tableName='$tableName'");
	$row = $rows->fetch();
	if (count($rows) == 0) {
		// treat the other user as a public
		if ($publicRole['read'] == false) {
			header('HTTP/1.0 401 Unauthorized');
			echo 'You are not authorized.';
			return;
		}
		$fields = json_decode($row['fields'], true);
		$publicFields = array();
		foreach($fields as $field) {
			if ($field->isVisible == true) {
				array_push($publicFields, $field);
			}
		}
		$finalFields = $publicFields;
	} else if (json_decode($row['role'], true)['read'] == false) {
		header('HTTP/1.0 401 Unauthorized');
		echo 'You are not authorized.';
		return;
	} else {
		$finalFields = json_decode($row['fields']);
	}
}

$searchQuery = $_GET['searchQuery'];

$whereAdded = false;

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
				if (is_float($query) == true) {
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