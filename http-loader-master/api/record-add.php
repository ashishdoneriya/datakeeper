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
	if ( $publicRole['add']['allow'] == false 
			|| ($publicRole['add']['allow'] == true
			&& $publicRole['add']['loginRequired'] == true)) {
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
		if ($publicRole['add']['allow'] == false) {
			header('HTTP/1.0 401 Unauthorized');
			echo 'You are not authorized.';
			return;
		} else {
			$approvalRequired = true;
		}
	} else {
		$role = $row['role'];
		if ($role['add']['allow'] == false) {
			header('HTTP/1.0 401 Unauthorized');
			echo 'You are not authorized.';
			return;
		} else if ($role['add']['loginRequired'] == true) {
			$approvalRequired = true;
		}
	}
}
if ($approvalRequired == true) {
	$rows = null;
	$encodedFields = htmlspecialchars(strip_tags(json_encode($data['fields'])));
	if ($loggedInUserId == null) {
		$rows = $db->query("insert into data_requests (tableName, fields, requestType) values ('$tableName', '$encodedFields', 'add')");	
	} else {
		$rows = $db->query("insert into data_requests (userId, tableName, fields, requestType) values ($loggedInUserId, '$tableName', '$encodedFields', 'add')");			
	}
	if ($rows == true) {
		echo 'success';
	} else {
		echo 'failed';
	}
	return;
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