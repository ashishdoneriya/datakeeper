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
if (!isAdmin($db, $userId, $tableName)) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}

// fetching userId of email that is to be added
$guestId = getUserId($guestEmail);
if ($guestId == null) {
	echo '{ status : "failed", message : "Email id is not registered"}';
    return;
}
// Adding guest
$encodedJson = json_encode($data['role']);
$rows = $db->query("insert into guest_permissions (userId, tableName, role) values ($loggedInUserId, '$tableName', '$encodedJson')");
echo '{ status : "success"}';
?>