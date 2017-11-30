<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
include_once './utils.php';

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
$tableName = htmlspecialchars(strip_tags($data['tableName']));
$newFields = $data['fields'];

if (!isSuperAdmin($db, $userId, $tableName)) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}

$idsFound = 0;
$length = count($newFields);
for ($x = 0; $x < $length; $x++) {
	$field = (object) $newFields[$x];
	if ($field->type == 'primaryKey') {
		$idsFound++;
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

$oldFields = getFields($db, $userId, $tableName);
if ($oldFields == null) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}
$length = count($newFields);
for ($x = 0; $x < $length; $x++) {
	$newField = (object) $newFields[$x];
	if (!property_exists($newField, 'fieldId')) {
		$newField->fieldId = str_replace(' ', '_', $newField->name);
		// Adding column
		$db->query("alter table " . $tableName . " add " . $newField->fieldId . " " . getMysqlFieldType($newField->type) . getRequired($newField->required));
	} else {
		// Modifying column
		$db->query("alter table " . $tableName . " modify column " . $newField->fieldId . " " . getMysqlFieldType($newField->type) . getRequired($newField->required));
	}
	$newFields[$x] = (array) $newField;
}

foreach($oldFields as $oldField) {
	$isExists = false;
	foreach ($newFields as $newField) {
		if ($oldField['fieldId'] == $newField['fieldId']) {
			$isExists = true;
		}
	}
	if ($isExists == false) {
		// Removing fields
		$db->query("alter table " . $tableName . " drop column " . $oldField['fieldId']);
	}
}

// updating tables_info
$encodedFields = json_encode($newFields);
$query = "update tables_info set displayedTableName='$displayedTableName',fields='$encodedFields' where tableName='$tableName'";
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
		default :
			return 'TEXT';
	}
}
?>