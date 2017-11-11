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
	$_SESSION["userId"] = $user->id;
	$_SESSION["name"] = $user->name;
	$_SESSION["email"] = $user->email;
	echo "success";
} else {
	echo "failed";
}

?>