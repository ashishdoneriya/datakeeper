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

$access = isAllowedToAccessTable($db, $loggedInUserId, $tableName, 'update');

if (!$access['allowed']) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}
$oldId=htmlspecialchars(strip_tags($data['oldId']));
if ($access['approval']) {
	$rows = null;
	$encodedFields = htmlspecialchars(strip_tags(json_encode($data['fields'])));
	if ($loggedInUserId == null) {
		$rows = $db->query("insert into data_requests (tableName, fields, requestType, oldId) values ('$tableName', '$encodedFields', 'update', $oldId)");
	} else {
		$rows = $db->query("insert into data_requests (userId, tableName, fields, requestType, oldId) values ($loggedInUserId, '$tableName', '$encodedFields', 'update', $oldId)");
	}
	if ($rows == true) {
		echo '{status : "success"}';
	} else {
		echo '{status : "failed", message : "Unable to create request to add data"}';
	}
	return;
}

$fieldsIdArr = array();
$fields = $data['fields'];
foreach($fields as $field) {
	if ($field['type'] == 'id' && $field['autoIncrement'] == true) {
		continue;
    }
    $temp = $field['id'];
	array_push($fieldsIdArr, $field['id'] . "=");
	if (toAddQuotes($field['type'])) {
		$temp = $temp . "'" . $field['value'] . "'";
	} else {
		$temp = $temp . $field['value'];
	}
}
$fieldsString = join(", " , $fieldsIdArr);

$query = "update $tableName set ($fieldsString) where id=$oldId";
$result = $db->query($query);
if ($rows == true) {
	echo '{status : "success"}';
} else {
	echo '{status : "failed", message : "Unable to add data, internal server problem"}';
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
		case 'Id';
			return false;
		default :
			return true;
	}
}

?>