<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
include_once './objects/users.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$data = json_decode(file_get_contents('php://input'), TRUE);
$user->name = $data['name'];
$user->email = $data['email'];
$user->password = $data['password'];



$user->register();
echo "success";
?>