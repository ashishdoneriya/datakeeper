<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
session_start();
$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents('php://input'), TRUE);
$data = json_decode(json_encode($data));
$displayedTableName = $data ->displayedTableName;
$fields = $data->fields;
$idsFound = 0;
foreach($fields as $field) {
	if ($field->type == 'Id') {
		$idsFound++;
	}
	$field->id = str_replace(' ', '_', $field->name);
}
if ($idsFound != 1) {
	echo "failed";
	return;
}
$encodedFields = json_encode($fields);
$userId = $_SESSION['userId'];
$tableName = $userId . '_' . time();
$query = "insert into users_tables (userId, tableName, displayedTableName, fields, publicRole ) values ($userId, '$tableName', '$displayedTableName', '$encodedFields', 'none')";
$rows = $db->query($query);
if ($rows == false) {
	echo "failed";
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
	echo "failed";
} else {
	echo "success";
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