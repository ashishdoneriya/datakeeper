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
foreach($fields as $field) {
	$field->id = str_replace(' ', '_', $field->name);
}
$encodedFields = json_encode($fields);
$userId = $_SESSION['userId'];
$tableName = $userId . '_' . time();
$query = "insert into users_tables (userId, tableName, displayedTableName, fields, publicRole ) values ($userId, '$tableName', '$displayedTableName', '$encodedFields', 'none')";
$db->query($query);

// Creating table
$tempFields = array();
foreach($fields as $field) {
	array_push($tempFields, '' . $field->id . ' ' . getMysqlFieldType($field->type) . getRequired($field->isCompulsory));
}

$query = 'create table ' . $tableName .  ' ('. join(", ", $tempFields) . ')';
$db->query($query);

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
		default :
			return 'TEXT';
	}
}
?>