<?php
// Add an administrator for a table
header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
include_once './utils.php';
session_start();
$database = new Database();
$db = $database->getConnection();
$userId = $_SESSION['userId'];
$data = json_decode(htmlspecialchars(strip_tags(file_get_contents('php://input'))), true);
$tableName = $data['tableName'];
$email = $data['email'];

// Checking if logged in user is admin
if (!isAdmin($db, $userId, $tableName)) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}
// fetching userId of email that is to be added
$userIdToAdd = getUserId($db, $email);
if ($userIdToAdd  == null) {
	echo "{status : 'failed', message : 'Email not registered'}";
	return;
}
// adding user as admin
$rows = $db->query("insert table_admin (userId, tableName) values ('$tableName', '$userIdToAdd')");
if ($rows == false) {
	echo "{'status' : 'failed', message : 'unable to add $email as admin, server error '}";
} else {
	echo "{status : 'success'}";
}
?>