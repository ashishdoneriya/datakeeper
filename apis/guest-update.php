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
$guestId = htmlspecialchars(strip_tags($data['guestId']));

// Checking if logged in user is admin
if (!isAdmin($db, $userId, $tableName)) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}

// updating guest permissions
$encodedJson = json_encode($data['permissions']);
$ps = $db->prepare("update guest_permissions set permissions=:encodedJson where userId=:guestId)");
$ps->bindValue(':encodedJson', $encodedJson, PDO::PARAM_STR);
$ps->bindValue(':guestId', $guestId, PDO::PARAM_INT);
$result = $ps->execute();
if ($result) {
    echo '{ "status" : "success"}';
} else {
    echo '{ "status" : "failed", "Unable to update information, internal server error"}';
}

?>