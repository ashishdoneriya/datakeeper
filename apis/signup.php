<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';

$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents('php://input'), TRUE);

$name = htmlspecialchars(strip_tags($data['name']));
$email = htmlspecialchars(strip_tags($data['email']));
$password = htmlspecialchars(strip_tags($data['password']));

$data = json_decode(file_get_contents('php://input'), TRUE);
$email = htmlspecialchars(strip_tags($data['email']));
$password = htmlspecialchars(strip_tags($data['password']));
$ps = $db->prepare("select userId from users where email=:email");
$ps->fetchAll([':email' => $email]);

if ($ps->rowCount() != 0) {
	$result['status'] = 'failed';
	$result['message'] = "Email Id already registered";
	echo json_encode($result);
	return;
}

$ps = $db->prepare("insert into users (name, email, password) value (:name, :email, :password)");
$ps->bindValue(':name', $name, PDO::PARAM_STR);
$ps->bindValue(':email', $email, PDO::PARAM_STR);
$ps->bindValue(':password', $password, PDO::PARAM_STR);
$succeed = $ps->execute();
$result = array();
if ($succeed) {
	$result['status'] = 'success';
	$result['message'] = "Registration Successful";
} else {
	$result['status'] = 'failed';
	$result['message'] = "Problem in server, unable to register.";
}
echo json_encode($result);
?>
