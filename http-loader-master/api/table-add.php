<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
session_start();
$userId = $_SESSION['userId'];
if ($userId == null) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}
$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents('php://input'), TRUE);
$data = json_decode(json_encode($data));
$displayedTableName = htmlspecialchars(strip_tags($data ->displayedTableName));
$fields = $data->fields;
$idsFound = 0;
$count = 0;
foreach($fields as $field) {
	if ($field->type == 'Id') {
		$field->id = str_replace(' ', '_', $field->name);
		$idsFound++;
	} else {
		$count++;
		$field->id = str_replace(' ', '_', $field->name) . $count;
	}
}
if ($idsFound == 0) {
	echo '{"status" : "failed", "message" : "No Id provided" }';
	return;
}
if ($idsFound > 1) {
	echo '{"status" : "failed", "message" : "Multiple Ids provided" }';
	return;
}

$encodedFields = json_encode($fields);

$tableName = $userId . '_' . time();
$roleJson = '{"read" : {"allowed" : false, "approval" : true, "loginRequired" : false},"add" : {"allowed" : false, "approval" : true, "loginRequired" : true},"update" : {"allowed" : false, "approval" : true, "loginRequired" : true},"delete" : {"allowed" : false, "approval" : true, "loginRequired" : true}}';
$query = "insert into tables_info (tableName, displayedTableName, fields, publicRole ) values ('$tableName', '$displayedTableName', '$encodedFields', '$roleJson')";
$rows = $db->query($query);
if ($rows == false) {
	echo '{"status" : "failed", "message" : "Unable to add the table, internal error" }';
	return;
}
$query = "insert into table_admins (userId, tableName, isSuperAdmin) values ($userId, '$tableName', 1)";
$rows = $db->query($query);
if ($rows == false) {
	echo '{"status" : "failed", "message" : "Unable to add the table, internal error" }';
	return;
}

// Creating table
$tempFields = array();
foreach($fields as $field) {
	array_push($tempFields, '' . $field->id . ' ' . getMysqlFieldType($field->type) . getRequired($field->isCompulsory));
}

$query = 'create table ' . $tableName .  ' ('. join(", ", $tempFields) . ')';

$rows = $db->query($query);

if ($rows == false) {
	echo '{"status" : "failed", "message" : "Unable to add the table, internal error" }';
} else {
	echo '{"status" : "success"}';
}

function getRequired($required) {
	if ($required == true) {
		return ' NOT NULL';
	}
	return '';
}
function getMysqlFieldType($type) {
	switch ($type) {
		case 'Text' :
		case 'Select' :
		case 'Checkbox' :
		case 'Radio Button' :
			return 'TEXT';
		case 'Number' :
			return 'BIGINT';
		case 'Decimal Number' :
			return 'DOUBLE(M,D)';
		case 'Date' :
			return 'DATE';
		case 'Time' :
			return 'TIME';
		case 'Date Time' :
			return 'DATETIME';
		case 'Id' :
			return 'BIGINT primary key auto_increment';
		default :
			return 'TEXT';
	}
}
?>