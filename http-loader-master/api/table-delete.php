<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
session_start();
$userId = $_SESSION['userId'];
if ($userId == null || strlen(trim($userId)) == 0 ) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}
$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents('php://input'), TRUE);
$tableName = htmlspecialchars(strip_tags($data['tableName']));

$rows = $db->query("select fields from users_tables where tableName='$tableName' and userId=$userId");
$row = $rows->fetch();
if (gettype($row) == 'boolean' && $row == false) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}

$db->query("drop table $tableName");
$db->query("delete from users_tables where tableName='$tableName' and userId=$userId");
$db->query("delete from guest_permissions where tableName='$tableName'");
$db->query("delete from data_requests where tableName='$tableName'");
echo '{status : "success"}';
?>