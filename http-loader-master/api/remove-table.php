<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
session_start();
$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents('php://input'), TRUE);
$query = "drop table " . $data['tableName'];
$db->query($query);

?>