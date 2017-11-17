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

// removing user from guest
$rows = $db->query("delete from guest_permissions where userId=$guestId and tableName=$tableName)");
if ($rows == false) {
	echo '{ status : "failed", message : "Unable to revoke permissions from user"}';
	return;
}
// removing data requests created by user
$rows = $db->query("delete from data_requests where userId=$guestId and tableName=$tableName)");
if ($rows == false) {
	echo '{ status : "failed", message : "Unable to remove table requests created by user"}';
	return;
}
echo '{ status : "success"}';

?>