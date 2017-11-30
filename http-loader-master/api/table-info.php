<?php

header("Access-Control-Allow-Methods: GET");

include_once './config/database.php';
include_once './utils.php';
session_start();
$userId = $_SESSION['userId'];
$database = new Database();
$db = $database->getConnection();

$tableName = htmlspecialchars(strip_tags($_GET['tableName']));
$rows = $db->query("select displayedTableName from tables_info where tableName='$tableName'");
$row = $rows->fetch();
$result = array();
$result['displayedTableName'] = $row['displayedTableName'];
$finalFields = getFields($db, $userId, $tableName);
if ($finalFields == null){
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}
$result['fields'] = $finalFields;
$result['permissions'] = getPermissionsJson($db, $userId, $tableName);
echo json_encode($result);
?>