<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
include_once './utils.php';
session_start();
$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents('php://input'), TRUE);
$tableName = htmlspecialchars(strip_tags($data['tableName']));
$loggedInUserId = $_SESSION['userId'];

$access = isAllowedToAccessTable($db, $loggedInUserId, $tableName, 'delete');

if (!$access['allowed'] || ($access['allowed'] && $access['loginRequired'] && $loggedInUserId == null)) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}

$id = htmlspecialchars(strip_tags($data['id']));
if ($access['approval']) {
	$rows = null;
	if ($access['loginRequired']) {
		$rows = $db->query("insert into data_requests (userId, tableName, fields, requestType) values ($loggedInUserId, '$tableName', '$id', 'delete')");
	} else {
		$rows = $db->query("insert into data_requests (tableName, fields, requestType) values ('$tableName', '$id', 'delete')");
	}
} else {
	$rows = $db->query("delete from $tableName where primaryKey=$id");
}

if ($rows == true) {
	echo '{"status" : "success"}';
} else {
	echo '{"status" : "failed", "message" : "Unable to remove data, internal server problem"}';
}

?>
