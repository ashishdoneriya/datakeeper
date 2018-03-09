<?php
header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
include_once './utils.php';
session_start();
$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents('php://input'), TRUE);
$tableName = htmlspecialchars(strip_tags($data['tableName']));
$loggedInUserId = htmlspecialchars(strip_tags($_SESSION['userId']));

if (!doesTableExist($db, $tableName)) {
	echo '{"status" : "failed", "message" : "No such table"}';
	return;
}

$access = isAllowedToAccessTable($db, $loggedInUserId, $tableName, 'add');

if (! $access['allowed']) {
    header('HTTP/1.0 401 Unauthorized');
    echo 'You are not authorized.';
    return;
}

$fields = $data['fields'];
$fields = json_decode(json_encode($fields));
foreach ($fields as $field) {
    $field->value = htmlspecialchars(strip_tags($field->value));
}
$fields = json_decode(json_encode($fields), true);

if ($access['approval']) {
    $result = null;
    $encodedFields = json_encode($fields);
    if ($loggedInUserId == null) {
        $ps = $db->query(
                "insert into data_requests (tableName, fields, requestType) values (:tableName, :encodedFields, 'add')");
        $ps->bindValue(':tableName', $tableName, PDO::PARAM_STR);
        $ps->bindValue(':encodedFields', $encodedFields, PDO::PARAM_STR);
        $result = $ps->execute();
    } else {
        $rows = $db->query(
                "insert into data_requests (userId, tableName, fields, requestType) values (:loggedInUserId, :tableName, :encodedFields, 'add')");
        $ps->bindValue(':loggedInUserId', $loggedInUserId, PDO::PARAM_INT);
        $ps->bindValue(':tableName', $tableName, PDO::PARAM_STR);
        $ps->bindValue(':encodedFields', $encodedFields, PDO::PARAM_STR);
        $result = $ps->execute();
    }
    if ($result) {
        echo '{"status" : "success"}';
    } else {
        echo '{"status" : "failed", "message" : "Unable to create request to add data"}';
    }
    return;
}

$fieldsIdArr = array();
$fieldsIdColonArr = array();
foreach ($fields as $field) {
    if ($field['type'] == 'primaryKey' && $field['autoIncrement'] == true) {
        continue;
    }
    array_push($fieldsIdArr, $field['fieldId']);
    array_push($fieldsIdColonArr, ':' . $field['fieldId']);
}
$fieldsString = join(",", $fieldsIdArr);
$valuesString = join(",", $fieldsIdColonArr);

$ps = $db->prepare( "insert into $tableName ($fieldsString) values ($valuesString)");

foreach ($fields as $field) {
    if ($field['type'] == 'primaryKey' && $field['autoIncrement'] == true) {
        continue;
    }
    $ps->bindValue(':' + $field['fieldId'], $field['value'], getPdoParamType($field['type']));
}

$result = $ps->execute();
if ($result) {
    echo '{"status" : "success"}';
} else {
    echo '{"status" : "failed", "message" : "Unable to add data, internal server problem"}';
}

function getPdoParamType ($type)
{
    switch ($type) {
        case 'Text':
        case 'Select':
        case 'Checkbox':
        case 'Radio Button':
        case 'Date':
        case 'Time':
        case 'Date Time':
            return PDO::PARAM_STR;
        case 'Number':
        case 'Decimal Number':
        case 'primaryKey':
            return PDO::PARAM_NUM;
        default:
            return PDO::PARAM_STR;
    }
}

?>
