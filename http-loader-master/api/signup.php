<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$data = json_decode(file_get_contents('php://input'), TRUE);

$name = htmlspecialchars(strip_tags($data['name']));
$email = htmlspecialchars(strip_tags($data['email']));
$password = htmlspecialchars(strip_tags($data['password']));

$rows = $db->query("select count(*) from users where email='$email'");
$result = array();
$row = $rows->fetch();
if ((int) $row[0] > 0) {
	$result['status'] = 'failed';
	$result['message'] = "Email Id already registered";
	echo json_encode($result);
	return;
}

$query = "insert into users (name, email, password) value ('$name', '$email', '$password')";
$rows = $db->query($query);
if ($rows == true) {
	$result['status'] = 'success';
	$result['message'] = "Registration Successful";
} else {
	$result['status'] = 'failed';
	$result['message'] = "Problem in server, unable to register.";
}
echo json_encode($result);
?>