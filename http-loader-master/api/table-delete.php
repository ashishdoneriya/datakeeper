<?php
include_once './config/database.php';
include_once './utils.php';

header("Access-Control-Allow-Methods: POST");

session_start();
$userId = $_SESSION['userId'];
$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents('php://input'), TRUE);
$tableName = htmlspecialchars(strip_tags($data['tableName']));

if (!isSuperAdmin($db, $userId, $tableName)) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}

$db->query("delete from tables_info where tableName='$tableName' and userId=$userId");
$db->query("drop table $tableName");
$db->query("delete from guest_permissions where tableName='$tableName'");
$db->query("delete from data_requests where tableName='$tableName'");
echo '{status : "success"}';
?>