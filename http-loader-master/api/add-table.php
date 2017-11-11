<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
session_start();
$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents('php://input'), TRUE);
$displayedTableName = $data['displayedTableName'];
$fields = $data['fields'];
$encodedFields = json_encode($fields);
$userId = $_SESSION['userId'];
$tableName = $userId . '_' . time();
$query = "insert into users_tables (userId, tableName, displayedTableName, fields, readPermission , writePermission ) values ($userId, '$tableName', '$displayedTableName', '$encodedFields', 0, 0)";
$db->query($query);

// Creating table
$tempFields = array();
foreach($fields as $field) {
	array_push($tempFields, '' . $field['name'] . ' ' . getMysqlFieldType($field['type']) . getRequired($fields['isCompulsory']));
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