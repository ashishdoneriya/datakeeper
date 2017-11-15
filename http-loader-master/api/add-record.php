<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
session_start();
$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents('php://input'), TRUE);
$tableName = $data['tableName'];
$rows = $db->query("select * from users_tables where tableName='$tableName'");
$row = $rows->fetch();
$loggedInUserId = $_SESSION['userId'];
if ($loggedInUserId == null) {
	$publicRole = $row['publicRole'];
	if ($publicRole == 'none' || $publicRole == 'contributorR') {
		header('HTTP/1.0 401 Unauthorized');
		echo 'You are not authorized.';
		return;
	}
}

if ($row['userId'] != $loggedInUserId) {
	$rows = $db->query("select role from guests_permissions where userId='$loggedInUserId' and tableName='$tableName'");
	if (gettype($rows) == 'boolean' && $rows == false) {
		header('HTTP/1.0 401 Unauthorized');
		echo 'You are not authorized.';
		return;
	}
	$row = $rows->fetch();
	$role = $row['role'];
	if ($role == 'contributorR') {
		header('HTTP/1.0 401 Unauthorized');
		echo 'You are not authorized.';
		return;
	}
}

$fieldsIdArr = array();
$valuesArr = array();
$fields = $data['fields'];
foreach($fields as $field) {
	if ($field['type'] == 'id' && $field['autoIncrement'] == true) {
		continue;
	}
	array_push($fieldsIdArr, $field['id']);
	if (toAddQuotes($field['type'])) {
		array_push($valuesArr, "'" . $field['value'] . "'");
	} else {
		array_push($valuesArr, $field['value']);
	}
}
$fieldsString = join("," , $fieldsIdArr);
$valuesString = join(",", $valuesArr);

$query = "insert into $tableName ($fieldsString) values ($valuesString)";
$result = $db->query($query);
if ($result == true) {
	echo 'success';
} else {
	echo 'failed';
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