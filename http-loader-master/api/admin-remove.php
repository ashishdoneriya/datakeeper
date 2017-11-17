<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
include_once './utils.php';
session_start();
$database = new Database();
$db = $database->getConnection();
$userId = $_SESSION['userId'];
$data = json_decode(htmlspecialchars(strip_tags(file_get_contents('php://input'))), true);
$tableName = $data['tableName'];
$userIdToRemove = $data['userId'];

// Checking if logged in user is admin
if (!isAdmin($db, $userId, $tableName)) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}

// removing user from admin
$rows = $db->query("delete from table_admins where userId=$userIdToRemove and tableName='$tableName'");
if ($rows == false) {
	echo "{'status' : 'failed', message : 'unable to remove $email from admins, server error '}";
} else {
	echo "{status : 'success'}";
}
?>