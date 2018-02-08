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
$displayedTableName = htmlspecialchars(strip_tags($data['displayedTableName']));
$fields = $data['fields'];
$length = count($fields);

$idsFound = 0;
$count = 0;
for ($x = 0; $x < $length; $x++) {
	$field = (object) $fields[$x];
	if ($field->type == 'primaryKey') {
		$field->fieldId = "primaryKey";
		$idsFound++;
	} else {
		$count++;
		$field->fieldId = str_replace(' ', '_', $field->name) . $count;
	}
	$fields[$x] = (array) $field;
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
$permissionsJson = '{"read" : {"allowed" : false, "approval" : true, "loginRequired" : false},"add" : {"allowed" : false, "approval" : true, "loginRequired" : true},"update" : {"allowed" : false, "approval" : true, "loginRequired" : true},"delete" : {"allowed" : false, "approval" : true, "loginRequired" : true}}';
$query = "insert into tables_info (tableName, displayedTableName, fields, publicPermissions ) values ('$tableName', '$displayedTableName', '$encodedFields', '$permissionsJson')";
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
	array_push($tempFields, '' . $field['fieldId'] . ' ' . getMysqlFieldType($field['type']) . getRequired($field['required']));
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
		case 'primaryKey' :
			return 'BIGINT primary key auto_increment';
		default :
			return 'TEXT';
	}
}
?>