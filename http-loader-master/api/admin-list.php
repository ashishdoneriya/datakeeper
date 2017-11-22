<?php

header("Access-Control-Allow-Methods: GET");

include_once './config/database.php';
include_once './utils.php';
session_start();
$database = new Database();
$db = $database->getConnection();
$userId = $_SESSION['userId'];
$data = json_decode(file_get_contents('php://input'), true);
$tableName = htmlspecialchars(strip_tags($data['tableName']));
$email = htmlspecialchars(strip_tags($data['email']));

// Checking if logged in user is admin
if (!isAdmin($db, $userId, $tableName)) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}

// Fetching admins
$rows = $db->query("select u.userId as userId, u.email as email from users u, table_admins ta where ta.userId=u.userId and ta.tableName='$tableName'");
$ddArray = array();

while ($row = $rows->fetch()) {
	$arr = array();
	$arr['userId'] = $row['userId'];
	$arr['email'] = $row['email'];
	array_push($ddArray, $arr);
}

echo json_encode($ddArray);
?>