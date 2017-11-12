<?php

header("Access-Control-Allow-Methods: GET");
session_start();
$arr = array('name' => $_SESSION['name'], 'email' => $_SESSION['email']);
echo json_encode($arr);
?>