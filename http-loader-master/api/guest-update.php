<?php
header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
include_once './utils.php';
session_start();
$loggedInUserId = $_SESSION['userId'];
$database = new Database();
$db = $database->getConnection();
$data = json_decode(htmlspecialchars(strip_tags(file_get_contents('php://input'))), TRUE);
$tableName = $data['tableName'];
$guestId = $data['guestId'];

// Checking if logged in user is admin
if (!isAdmin($db, $userId, $tableName)) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}

// updating guest permissions
$encodedJson = json_encode($data['role']);
$rows = $db->query("update guest_permissions set role='$encodedJson' where userId=$guestId)");
echo '{ status : "success"}';
?>