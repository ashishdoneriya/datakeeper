<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents('php://input'), TRUE);
$email = htmlspecialchars(strip_tags($data['email']));
$password = htmlspecialchars(strip_tags($data['password']));
$query = "select userId, name from users where email='$email'";
$rows = $db->query($query);
$row = $rows->fetch();
if(gettype($row) == 'boolean') {
	echo "{status : 'failed', message : 'Email not registered'}";
	return;
}
session_start();

$_SESSION["userId"] = $row["userId"];
$_SESSION["name"] = $row["name"];
$_SESSION["email"] = $email;

echo "{status : 'success'}";
?>