<?php

header("Access-Control-Allow-Methods: GET");

include_once './config/database.php';
include_once './utils.php';

session_start();
$userId = $_SESSION['userId'];
$database = new Database();
$db = $database->getConnection();

$tableName = htmlspecialchars(strip_tags($_GET['tableName']));

if (!isAdmin($db, $userId, $tableName)) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}

$result = array();

// Fetching public roles
$rows = $db->query("select displayedTableName, fields, publicRole from tables_info where tableName='$tableName'");
$row = $rows->fetch();
$result['displayedTableName'] = $row['displayedTableName'];
$result['fields'] = json_decode($row['fields'], true);
$result['publicRoles'] = json_decode($row['publicRole'], true);

// Fetching individual roles
$rows = $db->query("select guest_permissions.userId as userId, users.email as email, guest_permissions.role as role from guest_permissions, users where users.userId=guest_permissions.userId and guest_permissions.tableName='$tableName'");
$guestPermissions = array();
while($row = $rows->fetch()) {
	if (gettype($row) == 'boolean') {
		break;
	}
	array_push($guestPermissions, array('userId'=> $row['userId'], 'email'=> $row['email'], 'role'=> json_decode($row['role'], true)));
}
$result['guestPermissions'] = $guestPermissions;

// Fetching administrators
$rows = $db->query("select users.userId as userId, users.email as email from table_admins, users where users.userId=table_admins.userId and table_admins.tableName='$tableName' and table_admins.isSuperAdmin=0");
$admins = array();
while($row = $rows->fetch()) {
	if (gettype($row) == 'boolean') {
		break;
	}
	array_push($admins, array('userId'=>$row['userId'], 'email'=>$row['email']));
}
$result['admins'] = $admins;

// Sending to client
echo json_encode($result);
?>