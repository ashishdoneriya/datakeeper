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
    $guestId = htmlspecialchars(strip_tags($data['guestId']));

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
    $json = $data['permissions'];
    if (!isPermissionValid($json)) {
        echo '{ "status" : "failed", "message" : "Invalid Permissions"}';
        return;
    }

// updating guest permissions
    $encodedJson = json_encode($json);
    $ps = $db->prepare(
        "update guest_permissions set permissions=:encodedJson where userId=:guestId)");
    $ps->bindValue(':encodedJson', $encodedJson, PDO::PARAM_STR);
    $ps->bindValue(':guestId', $guestId, PDO::PARAM_INT);
    $result = $ps->execute();
    if ($result) {
        echo '{ "status" : "success"}';
    } else {
        echo '{ "status" : "failed", "Unable to update information, internal server error"}';
    }

    function isPermissionValid($permissions)
    {
        if (!$permissions) {
            return false;
        }
        foreach ($permissions as $key => $value) {
            if ($key != "read" && $key != "add" && $key != "update" &&
                $key != "delete") {
                return false;
            }
            foreach ($value as $subKey => $subValue) {
                if ($subKey != "allowed" && $subKey != "approval" &&
                    $subKey != "loginRequired") {
                    return false;
                }
                if (gettype($subValue) != "boolean") {
                    return false;
                }
            }
        }
        return true;
    }$db->commit();

} catch (Exception $ex) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo '{"status" : "error"}';
}
