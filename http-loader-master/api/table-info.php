<?php

header("Access-Control-Allow-Methods: GET");

include_once './config/database.php';
session_start();
$userId = $_SESSION['userId'];
$database = new Database();
$db = $database->getConnection();

$tableName = $_GET['tableName'];
$rows = $db->query("select * from users_tables where tableName='$tableName'");
$row = $rows->fetch();
$result = array();
$result['displayedTableName'] = $row['displayedTableName'];
$role = $row['publicRole'];
if ($userId == null) {
	if ($role == 'none') {
		echo '';
		return;
	}

	// get public fields
	$fields = json_decode($row['fields'], true);
	$publicFields = array();
	foreach($fields as $field) {
		if ($field->isVisible == true) {
			array_push($publicFields, $field);
		}
	}
	$result['fields'] = $publicFields;
} else if ($userId == $row['userId']) {
	//get private fields
	$result['fields'] = json_decode($row['fields'], true);
} else {
	// checking if the other user has permissions to read or not
	$rows = $db->query("select userId, tableName, role from guest_permissions where tableName='$tableName'");
	$row = $rows->fetch();
	if (count($rows) == 0) {
		// treat the other user as a public
		if ($role == 'none') {
			echo '';
			return;
		}
		$fields = json_decode($row['fields'], true);
		$publicFields = array();
		foreach($fields as $field) {
			if ($field->isVisible == true) {
				array_push($publicFields, $field);
			}
		}
		$result['fields'] = $publicFields;
	} else {
		$result['fields'] = json_decode($row['fields'], true);
	}
}
echo json_encode($result);
?>