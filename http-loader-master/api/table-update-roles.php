<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
include_once './utils.php';

session_start();
$userId = $_SESSION['userId'];
$database = new Database();
$db = $database->getConnection();
if (!isAdmin($db, $userId, $tableName)) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}
$data = json_decode(file_get_contents('php://input'), TRUE);
$tableName = htmlspecialchars(strip_tags($data['tableName']));
$role = json_encode($data['role']);
$userId = $data['userId'];
$rows = null;
if ($userId == null) {
	$rows = $db->query("update tables_info set publicRole='$role' where tableName='$tableName'");
} else {
	$rows = $db->query("update guest_permissions set role='$role' where userId=$userId and tableName='$tableName'");
}
if ($rows) {
	echo '{"status" : "success"}';
} else {
	echo '{"status" : "failed", "message" : "Problem while updating permissions"}';
}

?>