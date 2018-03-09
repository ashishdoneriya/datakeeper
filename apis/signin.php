<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents('php://input'), TRUE);
$email = htmlspecialchars(strip_tags($data['email']));
$password = htmlspecialchars(strip_tags($data['password']));
$ps = $db->prepare("select userId, name from users where email=:email");
$ps->execute([':email' => $email]);

if($ps->rowCount() == 0) {
	echo '{"status" : "failed", "message" : "Email not registered"}';
	return;
} else {
	$arr = $ps->fetchAll(PDO::FETCH_ASSOC );
	$row = $arr[0];
	session_start();
	$_SESSION["userId"] = $row["userId"];
	$_SESSION["name"] = $row["name"];
	$_SESSION["email"] = $email;
	echo '{"status" : "success", "email" : "' . $email . '"}';
}

?>
