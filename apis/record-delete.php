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

if (!doesTableExist($db, $tableName)) {
	echo '{"status" : "failed", "message" : "No such table"}';
	return;
}

$access = isAllowedToAccessTable($db, $loggedInUserId, $tableName, 'delete');

if (! $access['allowed'] || ($access['allowed'] && $access['loginRequired'] &&
		 $loggedInUserId == null)) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}

$recordId = htmlspecialchars(strip_tags($data['id']));
if ($access['approval']) {
	$result = null;
	if ($access['loginRequired']) {
		$ps = $db->prepare(
				"insert into data_requests (userId, tableName, fields, requestType) values (:loggedInUserId, :tableName, :id, 'delete')");
		$ps->bindValue(':loggedInUserId', $loggedInUserId, PDO::PARAM_INT);
		$ps->bindValue(':tableName', $tableName, PDO::PARAM_INT);
		$ps->bindValue(':id', $recordId, PDO::PARAM_INT);
		$result = $ps->execute();
	} else {
		$ps = $db->prepare(
				"insert into data_requests (tableName, fields, requestType) values (:tableName, :id, 'delete')");
		$ps->bindValue(':tableName', $tableName, PDO::PARAM_INT);
		$ps->bindValue(':id', $loggedInUserId, PDO::PARAM_INT);
		$result = $ps->execute();
	}
} else {
	$ps = $db->prepare("delete from $tableName where primaryKey=:id");
	$ps->bindValue(':id', $loggedInUserId, PDO::PARAM_INT);
	$result = $ps->execute();
}

if ($result) {
	echo '{"status" : "success"}';
} else {
	echo '{"status" : "failed", "message" : "Unable to remove data, internal server problem"}';
}

?>
