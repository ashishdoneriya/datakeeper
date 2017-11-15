<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
session_start();
$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents('php://input'), TRUE);
$fields = $data['fields'];
$tableName = $data['tableName'];

$fieldsIdArr = array();
$valuesArr = array();
foreach($fields as $field) {
	array_push($fieldsIdArr, $field['id']);
	if (toAddQuotes($field['type'])) {
		array_push($valuesArr, "'" . $field['value'] . "'");
	} else {
		array_push($valuesArr, $field['value']);
	}
}
$fieldsString = join("," , $fieldsIdArr);
$valuesString = join(",", $valuesArr);

$query = "insert into $tableName ($fieldsString) values ($valuesString)";
$result = $db->query($query);
if ($result == true) {
	echo 'success';
} else {
	echo 'failed';
}

function toAddQuotes ($type) {
	switch ($type) {
		case 'Text' :
		case 'Select' :
		case 'Checkbox' :
		case 'Radio Button' :
		case 'Date' :
		case 'Time' :
		case 'Date Time' :
			return true;
		case 'Number' :
		case 'Decimal Number' :
			return false;
		default :
			return true;
	}
}

?>