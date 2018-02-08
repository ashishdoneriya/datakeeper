<?php
header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
include_once './utils.php';
session_start();
$loggedInUserId = $_SESSION['userId'];
$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents('php://input'), TRUE);
$tableName = htmlspecialchars(strip_tags($data['tableName']));
$guestEmail = htmlspecialchars(strip_tags($data['email']));

// Checking if logged in user is admin
if (!isAdmin($db, $loggedInUserId, $tableName)) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}

// fetching userId of email that is to be added
$guestId = getUserId($db, $guestEmail);
if ($guestId == null) {
	echo '{ "status" : "failed", "message" : "Email id is not registered"}';
    return;
}
// Adding guest
$encodedJson = $data['permissions'];
$rows = $db->query("insert into guest_permissions (userId, tableName, permissions) values ($loggedInUserId, '$tableName', '$encodedJson')");
echo '{ "status" : "success"}';
?>