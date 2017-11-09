<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
include_once './objects/users.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$user->name = $_POST['name'];
$user->email = $_POST['email'];
$user->password = $_POST['password'];
$user->register();
echo "success";
?>