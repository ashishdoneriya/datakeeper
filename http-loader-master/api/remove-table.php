<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
session_start();
$userId = $_SESSION['userId'];
$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents('php://input'), TRUE);
$tableName = $data['tableName'];
$db->query("drop table $tableName");
$db->query("delete from users_tables where tableName='$tableName' and userId=$userId");
echo 'success';
?>