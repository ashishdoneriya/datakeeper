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

if (!doesTableExist($db, $tableName)) {
	echo '{"status" : "failed", "message" : "No such table"}';
	return;
}

$db->query("delete from tables_info where tableName='$tableName'");
$db->query("drop table $tableName");
echo '{"status" : "success"}';
?>