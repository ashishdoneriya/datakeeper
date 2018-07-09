<?php
include_once './config/database.php';
include_once './utils.php';
session_start();
$userId = $_SESSION['userId'];
$database = new Database();
$db = $database->getConnection();
$tableName = htmlspecialchars(strip_tags($_GET['tableName']));
$requestType = htmlspecialchars(strip_tags($_GET['requestType']));

if (!isAdmin($db, $userId, $tableName)) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}
?>
