<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
include_once './utils.php';
session_start();

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents('php://input'), TRUE);
$tableName = htmlspecialchars(strip_tags($data['tableName']));
$loggedInUserId = $_SESSION['userId'];
$oldId=htmlspecialchars(strip_tags($data['oldPrimaryKey']));
$row = json_decode($data['row'], true);

$access = isAllowedToAccessTable($db, $loggedInUserId, $tableName, 'update');

if (!$access['allowed'] || ($access['allowed'] && $access['loginRequired'] && $loggedInUserId == null)) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}

$finalFields = getFields($db, $loggedInUserId, $tableName);
$finalFields = json_decode(json_encode($finalFields));
foreach($finalFields as $field) {
	$field->value = htmlspecialchars(strip_tags($row[$field->fieldId]));
}
$finalFields = json_decode(json_encode($finalFields), true);

if ($access['approval']) {
	$rows = null;
	$encodedFields = json_encode($finalFields);
	if ($access['loginRequired']) {
		$rows = $db->query("insert into data_requests (userId, tableName, fields, requestType, oldId) values ($loggedInUserId, '$tableName', '$encodedFields', 'update', $oldId)");
	} else {
		$rows = $db->query("insert into data_requests (tableName, fields, requestType, oldId) values ('$tableName', '$encodedFields', 'update', $oldId)");
	}
	if ($rows == true) {
		echo '{"status" : "success"}';
	} else {
		echo '{"status" : "failed", "message" : "Unable to create request to add data"}';
	}
} else {
	$fieldsIdArr = array();
	foreach($finalFields as $field) {
		$temp = $field['fieldId'];
		array_push($fieldsIdArr, $field['fieldId'] . "=" . (toAddQuotes($field['type']) ? "'" . $field['value'] . "'" : $field['value'] ));
	}
	$fieldsString = join(", " , $fieldsIdArr);

	$rows = $db->query("update $tableName set $fieldsString where primaryKey=$oldId");
	if ($rows == true) {
		echo '{"status" : "success"}';
	} else {
		echo '{"status" : "failed", "message" : "Unable to add data, internal server problem"}';
	}
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
