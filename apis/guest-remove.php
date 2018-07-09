<?php
header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
include_once './utils.php';
session_start();
$loggedInUserId = $_SESSION['userId'];
$database = new Database();
$db = $database->getConnection();
$db->beginTransaction();
try {
    $data = json_decode(file_get_contents('php://input'), true);
    $tableName = htmlspecialchars(strip_tags($data['tableName']));
    $guestId = htmlspecialchars(strip_tags($data['userId']));

    if (!doesTableExist($db, $tableName)) {
        echo '{"status" : "failed", "message" : "No such table"}';
        return;
    }

// Checking if logged in user is admin
    if (!isAdmin($db, $userId, $tableName)) {
        header('HTTP/1.0 401 Unauthorized');
        echo 'You are not authorized.';
        return;
    }

// removing user from guest
    $ps = $db->prepare(
        "delete from guest_permissions where userId=:guestId and tableName=:tableName)");
    $ps->bindValue(':guestId', $guestId, PDO::PARAM_INT);
    $ps->bindValue(':tableName', $guestId, PDO::PARAM_STR);
    $result = $ps->execute();
    if ($result == false) {
        echo '{ "status" : "failed", "message" : "Unable to revoke permissions from user"}';
        return;
    }
// removing data requests created by user
    $ps = $db->prepare(
        "delete from data_requests where userId=:guestId and tableName=:tableName)");
    $ps->bindValue(':guestId', $guestId, PDO::PARAM_INT);
    $ps->bindValue(':tableName', $guestId, PDO::PARAM_STR);
    $result = $ps->execute();
    if ($result == false) {
        echo '{ "status" : "failed", "message" : "Unable to remove table requests created by user"}';
        return;
    }
    echo '{ "status" : "success"}';
    $db->commit();

} catch (Exception $ex) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo '{"status" : "error"}';
}
