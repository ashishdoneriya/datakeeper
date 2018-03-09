<?php

header("Access-Control-Allow-Methods: GET");

include_once './config/database.php';
session_start();
$database = new Database();
$db = $database->getConnection();
$userId = $_SESSION['userId'];
if ($userId == null) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}
$result = array();
$query = "select tables_info.tableName as tableName, tables_info.displayedTableName as displayedTableName";
$query = $query . " from tables_info, table_admins where tables_info.tableName=table_admins.tableName and table_admins.userId=:userId";
$ps = $db->prepare($query);
$ps->bindValue(':userId', $userId, PDO::PARAM_INT);
$ps->execute();
$ddArray = $ps->fetchAll(PDO::FETCH_ASSOC);
$result['personalTables'] = array('label' => 'Personal Tables', 'list'=> $ddArray);

$query = "select tables_info.tableName as tableName, tables_info.displayedTableName as displayedTableName, guest_permissions.permissions as permissions from tables_info, guest_permissions where tables_info.tableName=guest_permissions.tableName and guest_permissions.userId=:userId";
$ps = $db->prepare($query);
$ps->bindValue(':userId', $userId, PDO::PARAM_INT);
$ps->execute();
$rows = $ps->fetchAll(PDO::FETCH_ASSOC);
$ps = $db->prepare($query);
$ps->bindValue(':userId', $userId, PDO::PARAM_INT);
$ps->execute();
$ddArray = $ps->fetchAll(PDO::FETCH_ASSOC);
$result['otherTables'] = array('label' => 'Other Tables', 'list'=> $ddArray);

echo json_encode($result);
?>