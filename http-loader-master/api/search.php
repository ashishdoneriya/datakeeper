<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';

$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents('php://input'), TRUE);
$query = "select * from " . $data['tableName'];
$rows = $db->query($query);

$result = array();
while($row=$rows->fetch()) {
	array_push($result, $row);
}
echo json_encode($result);
?>