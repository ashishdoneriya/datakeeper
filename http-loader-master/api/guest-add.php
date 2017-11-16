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
$tableName = $data['tableName'];

$rows = $db->query("select * from users_tables where tableName='$tableName'");
$row = $rows->fetch();
if ($row['userId'] != $loggedInUserId) {
    header('HTTP/1.0 401 Unauthorized');
    echo 'You are not authorized.';
    return;
}
$guestEmail = $data['guestEmail'];
$rows = $db->query("select userId from users where email='$guestEmail'");
if (gettype($rows) == 'boolean' && $rows == true) {
    echo '{ status : "failed", message : "Email id is not registered"}';
    return;
}
$row = $rows->fetch();
$guestId = $row['userId'];
$encodedJson = json_encode($data['role']);
$rows = $db->query("insert into guest_permissions (userId, tableName, role) values ($loggedInUserId, '$tableName', '$encodedJson')");
echo '{ status : "success"}';
?>