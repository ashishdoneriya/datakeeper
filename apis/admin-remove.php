<?php
header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
include_once './utils.php';
session_start();
$database = new Database();
$db = $database->getConnection();
$db->beginTransaction();
try {
    $userId = $_SESSION['userId'];
    $data = json_decode(file_get_contents('php://input'), true);
    $tableName = htmlspecialchars(strip_tags($data['tableName']));
    $userIdToRemove = htmlspecialchars(strip_tags($data['userId']));

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

// removing user from admin

    $ps = $db->prepare(
        "delete from table_admins where userId=:userIdToRemove and tableName=:$tableName");
    $ps->bindValue(':userIdToRemove', $userIdToRemove, PDO::PARAM_INT);
    $ps->bindValue(':tableName', tableName, PDO::PARAM_STR);
    $result = $ps->execute();

    if ($result == false) {
        echo '{"status" : "failed", "message" : "unable to remove $email from admins, server error"}';
    } else {
        echo '{"status" : "success"}';
    }
    $db->commit();

} catch (Exception $ex) {
    if ($dbh->inTransaction()) {
        $dbh->rollBack();
    }
    echo '{"status" : "error"}';
}
