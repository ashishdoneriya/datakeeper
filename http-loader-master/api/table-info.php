<?php

header("Access-Control-Allow-Methods: GET");

include_once './config/database.php';
session_start();
$database = new Database();
$db = $database->getConnection();
$tableName = $_GET['tableName'];
$query = "select displayedTableName, fields from users_tables where tableName='$tableName'";
$rows = $db->query($query);
$row = $rows->fetch();
$result = array();
$result['displayedTableName'] = $row['displayedTableName'];
$result['fields'] = json_decode($row['fields']);
echo json_encode($result);
?>