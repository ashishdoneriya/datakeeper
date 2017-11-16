<?php
header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
session_start();
$loggedInUserId = $_SESSION['userId'];
if ($loggedInUserId == null) {
    header('HTTP/1.0 401 Unauthorized');
    echo 'You are not authorized.';
    return;
}
$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents('php://input'), TRUE);
$tableName = htmlspecialchars(strip_tags($data['tableName']));

$rows = $db->query("select * from users_tables where tableName='$tableName'");
$row = $rows->fetch();
if ($row['userId'] != $loggedInUserId) {
    header('HTTP/1.0 401 Unauthorized');
    echo 'You are not authorized.';
    return;
}
$guestId = htmlspecialchars(strip_tags($data['guestId']));
if ($guestId == null || is_numeric($guestId) == false) {
    echo '{ "status" : "failed", "message" : "Guest Id not available"}';
}
$encodedJson = htmlspecialchars(strip_tags(json_encode($data['role'])));
$rows = $db->query("update guest_permissions set role='$encodedJson' where userId=$guestId)");
echo '{ status : "success"}';
?>