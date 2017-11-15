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
$query = "select tableName, displayedTableName from users_tables where userId=$userId";
$rows = $db->query($query);
$ddArray = array();
while($row = $rows->fetch()) {
	$arr = array();
	$arr['tableName'] = $row['tableName'];
	$arr['displayedTableName'] = $row['displayedTableName'];
	array_push($ddArray, $arr);
}
echo json_encode($ddArray);
?>