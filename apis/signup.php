<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';

$database = new Database();
$db = $database->getConnection();
$db->beginTransaction();
try {
    $data = json_decode(file_get_contents('php://input'), true);

    $name = htmlspecialchars(strip_tags($data['name']));
    $email = htmlspecialchars(strip_tags($data['email']));
    $password = htmlspecialchars(strip_tags($data['password']));

    if (!$name || !$email || !$password) {
        echo '{"status" : "failed", "message" : "Please enter valid information"}';
        return;
    }
    $ps = $db->prepare("select userId from users where email=:email");
    $ps->bindValue(':email', $email, PDO::PARAM_STR);
    $ps->execute();

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
    $db->commit();

} catch (Exception $ex) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo '{"status" : "error"}';
}
