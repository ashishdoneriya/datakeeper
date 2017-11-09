<?php

header("Access-Control-Allow-Methods: GET");
session_start();
$arr = array('name' => $_SESSION['user.name'], 'email' => $_SESSION['user.email']);
echo json_encode($arr);
?>