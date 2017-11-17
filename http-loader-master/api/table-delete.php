<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
session_start();
$userId = $_SESSION['userId'];
$database = new Database();
$db = $database->getConnection();
$data = json_decode(htmlspecialchars(strip_tags(file_get_contents('php://input'))), TRUE);
$tableName = $data['tableName'];

if (!isAdmin($db, $userId, $tableName)) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}

$db->query("delete from users_tables where tableName='$tableName' and userId=$userId");
$db->query("drop table $tableName");
$db->query("delete from guest_permissions where tableName='$tableName'");
$db->query("delete from data_requests where tableName='$tableName'");
echo '{status : "success"}';
?>