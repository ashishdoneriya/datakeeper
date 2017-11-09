<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
include_once './objects/users.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$data = json_decode(file_get_contents('php://input'), TRUE);
$user->email = $data['email'];
$user->password = $data['password'];
$user->fetchUserInfo();
if ($user->id != null) {
	session_start();
	$_SESSION["user.id"] = $user->id;
	$_SESSION["user.name"] = $user->name;
	$_SESSION["user.email"] = $user->email;
	echo "success";
} else {
	echo "failed";
}

?>