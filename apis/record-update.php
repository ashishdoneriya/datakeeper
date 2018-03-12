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
$oldPrimaryKey = htmlspecialchars(strip_tags($data['oldPrimaryKey']));
$row = $data['row'];

if (!$tableName || !$oldPrimaryKey || !$row) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}

if (!doesTableExist($db, $tableName)) {
	echo '{"status" : "failed", "message" : "No such table"}';
	return;
}

$access = isAllowedToAccessTable($db, $loggedInUserId, $tableName, 'update');

if (! $access['allowed'] ||
		 ($access['allowed'] && $access['loginRequired'] &&
		 $loggedInUserId == null)) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}

$finalFields = getFields($db, $loggedInUserId, $tableName);
$finalFields = json_decode(json_encode($finalFields));
foreach ($finalFields as $field) {
	$field->value = $row[$field->fieldId];
}
$finalFields = json_decode(json_encode($finalFields), true);

if ($access['approval']) {
	$result = null;
	$encodedFields = json_encode($finalFields);
	if ($access['loginRequired']) {
		$ps = $db->prepare(
				"insert into data_requests (userId, tableName, fields, requestType, oldPrimaryKey) values (:loggedInUserId, :tableName, :encodedFields, 'update', :oldPrimaryKey)");
		$ps->bindValue(':loggedInUserId', $loggedInUserId, PDO::PARAM_INT);
		$ps->bindValue(':tableName', $tableName, PDO::PARAM_STR);
		$ps->bindValue(':encodedFields', $encodedFields, PDO::PARAM_STR);
		$ps->bindValue(':oldId', $oldPrimaryKey, PDO::PARAM_INT);
		$result = $ps->execute();
	} else {
		$ps = $db->prepare(
				"insert into data_requests (tableName, fields, requestType, oldId) values (:tableName, :encodedFields, 'update', :oldId)");
		$ps->bindValue(':tableName', $tableName, PDO::PARAM_STR);
		$ps->bindValue(':encodedFields', $encodedFields, PDO::PARAM_STR);
		$ps->bindValue(':oldId', $oldId, PDO::PARAM_INT);
		$result = $ps->execute();
	}
	if ($result) {
		echo '{"status" : "success"}';
	} else {
		echo '{"status" : "failed", "message" : "Unable to create request to add data"}';
	}
} else {
	$fieldsIdArr = array();
	foreach ($finalFields as $field) {
		$temp = $field['fieldId'];
		array_push($fieldsIdArr, $field['fieldId'] . '=:' . $field['fieldId'] );
	}
	$fieldsString = join(", ", $fieldsIdArr);
	
	$ps = $db->prepare("update $tableName set $fieldsString where primaryKey=:oldPrimaryKey");
	$varType = null;
	foreach ($finalFields as $field) {
		if ($field['type'] == 'primaryKey') {
			if ($field['autoIncrement']) {
				$varType = PDO::PARAM_INT;
			} else {
				$varType = PDO::PARAM_STR;
			}
			break;
		}
	}
	$ps->bindValue(':oldPrimaryKey', $oldPrimaryKey, $varType);
	foreach ($finalFields as $field) {
		$ps->bindValue(':' . $field['fieldId'], $field['value'], getPdoParamType($field['type']));
	}
	$result = $ps->execute();
	if ($result) {
		echo '{"status" : "success"}';
	} else {
		echo '{"status" : "failed", "message" : "Unable to add data, internal server problem"}';
	}
}

function getPdoParamType ($type)
{
	switch ($type) {
		case 'Text':
		case 'Select':
		case 'Checkbox':
		case 'Radio Button':
		case 'Date':
		case 'Time':
		case 'Date Time':
			return PDO::PARAM_STR;
		case 'Number':
		case 'Decimal Number':
		case 'primaryKey':
			return PDO::PARAM_INT;
		default:
			return PDO::PARAM_STR;
	}
}

?>
