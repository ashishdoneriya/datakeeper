<?php

header("Access-Control-Allow-Methods: GET");

include_once './config/database.php';
include_once './utils.php';
session_start();
$userId = $_SESSION['userId'];
$database = new Database();
$db = $database->getConnection();
$tableName = htmlspecialchars(strip_tags($_GET['tableName']));

if (!doesTableExist($db, $tableName)) {
	header('HTTP/1.0 500 Internal Server Error');
	echo '{"status" : "failed", "message" : "No such table"}';
	return;
}
$finalFields = getFields($db, $userId, $tableName);
if ($finalFields == null){
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}
$ps = $db->prepare("select displayedTableName from tables_info where tableName=:tableName");
$ps->bindValue(':tableName', $tableName, PDO::PARAM_STR);
$ps->execute();

$result = array();
$result['displayedTableName'] = $ps->fetch(PDO::FETCH_COLUMN);
$result['fields'] = $finalFields;
$result['permissions'] = getPermissionsJson($db, $userId, $tableName);
echo json_encode($result);
?>