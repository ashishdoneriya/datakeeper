<?php
include_once './config/database.php';
include_once './utils.php';

header("Access-Control-Allow-Methods: POST");

session_start();
$userId = $_SESSION['userId'];
$database = new Database();
$db = $database->getConnection();
$db->beginTransaction();
try {
    $data = json_decode(file_get_contents('php://input'), true);
    $tableName = htmlspecialchars(strip_tags($data['tableName']));

    if (!doesTableExist($db, $tableName)) {
        echo '{"status" : "failed", "message" : "No such table"}';
        return;
    }

    if (!isSuperAdmin($db, $userId, $tableName)) {
        header('HTTP/1.0 401 Unauthorized');
        echo 'You are not authorized.';
        return;
    }

    $result = $db->exec("delete from tables_info where tableName='$tableName'");
    if (!$result) {
        echo '{"status" : "failed", "message" : "Unable to delete the table, internal server error" }';
        return;
    }
    $result = $db->exec("drop table $tableName");
    if (!$result) {
        echo '{"status" : "failed", "message" : "Unable to delete the table, internal server error" }';
        return;
    }
    echo '{"status" : "success"}';
    $db->commit();

} catch (Exception $ex) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo '{"status" : "error"}';
}
