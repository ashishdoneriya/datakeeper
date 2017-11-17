<?php

header("Access-Control-Allow-Methods: GET");

include_once './config/database.php';
session_start();
$database = new Database();
$db = $database->getConnection();
$userId = $_SESSION['userId'];
if ($userId == null) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}

$query = "select ut.tableName as tableName, ut.displayedTableName as displayedTableName from tables_info ut, table_admins ta where ut.tableName=ta.tableName and ta.userId=$userId";
$rows = $db->query($query);
$result = array();
$ddArray = array();
while($row = $rows->fetch()) {
	$arr = array();
	$arr['tableName'] = $row['tableName'];
	$arr['displayedTableName'] = $row['displayedTableName'];
	array_push($ddArray, $arr);
}
$result['personalTables'] = $ddArray;

$query = "select tables_info.tableName as tableName, tables_info.displayedTableName as displayedTableName, guest_permissions.role as role from tables_info, guest_permissions where tables_info.tableName=guest_permissions.tableName and tables_info.userId=$userId";

$rows = $db->query($query);
$ddArray = array();
while($row = $rows->fetch()) {
	if (gettype($row) == 'boolean') {
		break;
	}
	$arr = array();
	$arr['tableName'] = $row['tableName'];
	$arr['displayedTableName'] = $row['displayedTableName'];
	$arr['role'] = $row['role'];
	array_push($ddArray, $arr);
}

$result['otherTables'] = $ddArray;

echo json_encode($result);
?>