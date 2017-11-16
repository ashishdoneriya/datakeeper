<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
session_start();
$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents('php://input'), TRUE);
$tableName = htmlspecialchars(strip_tags($data['tableName']));
$rows = $db->query("select * from users_tables where tableName='$tableName'");
$row = $rows->fetch();
$loggedInUserId = $_SESSION['userId'];
$approvalRequired = false;
if ($loggedInUserId == null) {
	$publicRole = json_decode($row['publicRole'], true);
	if ( $publicRole['update']['allow'] == false 
			|| ($publicRole['update']['allow'] == true
			&& $publicRole['update']['loginRequired'] == true)) {
		header('HTTP/1.0 401 Unauthorized');
		echo 'You are not authorized.';
		return;
	} else {
		$approvalRequired = true;
	}
} else if ($row['userId'] != $loggedInUserId) {
	$rows = $db->query("select role from guests_permissions where userId='$loggedInUserId' and tableName='$tableName'");
	$row = $rows->fetch();
	if (gettype($row) == 'boolean' && $row == false) {
		if ($publicRole['update']['allow'] == false) {
			header('HTTP/1.0 401 Unauthorized');
			echo 'You are not authorized.';
			return;
		} else {
			$approvalRequired = true;
		}
	} else {
		$role = $row['role'];
		if ($role['update']['allow'] == false) {
			header('HTTP/1.0 401 Unauthorized');
			echo 'You are not authorized.';
			return;
		} else if ($role['update']['loginRequired'] == true) {
			$approvalRequired = true;
		}
	}
}
$oldId=$data['oldId'];
if ($approvalRequired == true) {
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