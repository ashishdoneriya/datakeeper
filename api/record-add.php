<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
include_once './utils.php';
session_start();
$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents('php://input'), TRUE);
$tableName = htmlspecialchars(strip_tags($data['tableName']));
$loggedInUserId = htmlspecialchars(strip_tags($_SESSION['userId']));

$access = isAllowedToAccessTable($db, $loggedInUserId, $tableName, 'add');

if (!$access['allowed']) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}

if ($access['approval']) {
	$rows = null;
	$encodedFields = json_encode($data['fields']);
	if ($loggedInUserId == null) {
		$rows = $db->query("insert into data_requests (tableName, fields, requestType) values ('$tableName', '$encodedFields', 'add')");
	} else {
		$rows = $db->query("insert into data_requests (userId, tableName, fields, requestType) values ($loggedInUserId, '$tableName', '$encodedFields', 'add')");
	}
	if ($rows == true) {
		echo '{"status" : "success"}';
	} else {
		echo '{"status" : "failed", "message" : "Unable to create request to add data"}';
	}
	return;
}

$fieldsIdArr = array();
$valuesArr = array();
$fields = $data['fields'];

foreach($fields as $field) {
	if ($field['type'] == 'primaryKey' && $field['autoIncrement'] == true) {
		continue;
	}
	array_push($fieldsIdArr, $field['fieldId']);
	if (toAddQuotes($field['type'])) {
		array_push($valuesArr, "'" . $field['value'] . "'");
	} else {
		array_push($valuesArr, $field['value']);
	}
}
$fieldsString = join("," , $fieldsIdArr);
$valuesString = join(",", $valuesArr);

$query = "insert into $tableName ($fieldsString) values ($valuesString)";

$rows = $db->query($query);
if ($rows == true) {
	echo '{"status" : "success"}';
} else {
	echo '{"status" : "failed", "message" : "Unable to add data, internal server problem"}';
}

function toAddQuotes ($type) {
	switch ($type) {
		case 'Text' :
		case 'Select' :
		case 'Checkbox' :
		case 'Radio Button' :
		case 'Date' :
		case 'Time' :
		case 'Date Time' :
			return true;
		case 'Number' :
		case 'Decimal Number' :
		case 'primaryKey';
			return false;
		default :
			return true;
	}
}

?>