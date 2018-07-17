<?php
header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
session_start();
$userId = $_SESSION['userId'];
if ($userId == null) {
    header('HTTP/1.0 401 Unauthorized');
    echo 'You are not authorized.';
    return;
}
$database = new Database();
$db = $database->getConnection();
$db->beginTransaction();
try {
    $data = json_decode(file_get_contents('php://input'), true);
    $displayedTableName = $data['displayedTableName'];
    $fields = $data['fields'];

    if (!$fields || !$displayedTableName) {
        header('HTTP/1.0 401 Unauthorized');
        echo 'You are not authorized.';
        return;
    }

    $idsFound = 0;
    $count = 0;

    if (!isFieldsArrayValid($fields)) {
        echo '{"status" : "failed", "message" : "Invalid json format" }';
        return;
    }
    $length = count($fields);
    for ($x = 0; $x < $length; $x++) {
        $field = (object) $fields[$x];
        if ($field->type == 'primaryKey') {
            $field->fieldId = "primaryKey";
            $idsFound++;
        } else {
            $count++;
            $temp = preg_replace("/\W|_/", "", $field->name);
            if (strlen($temp) > 10) {
                $temp = substr($temp, 0, 10);
            }
            $field->fieldId = 'col_' . $temp . $count;
        }
        $fields[$x] = (array) $field;
    }

    if ($idsFound == 0) {
        echo '{"status" : "failed", "message" : "No Id provided" }';
        return;
    }
    if ($idsFound > 1) {
        echo '{"status" : "failed", "message" : "Multiple Ids provided" }';
        return;
    }

    $encodedFields = json_encode($fields);

    $tableName = $userId . '_' . time();
    $permissionsJson = '{"read" : {"allowed" : false, "approval" : true, "loginRequired" : false},"add" : {"allowed" : false, "approval" : true, "loginRequired" : true},"update" : {"allowed" : false, "approval" : true, "loginRequired" : true},"delete" : {"allowed" : false, "approval" : true, "loginRequired" : true}}';
    $query = "insert into tables_info (tableName, displayedTableName, fields, publicPermissions ) values (:tableName, :displayedTableName, :encodedFields, :permissionsJson)";
    $ps = $db->prepare($query);
    $ps->bindValue(':tableName', $tableName, PDO::PARAM_STR);
    $ps->bindValue(':displayedTableName', $displayedTableName, PDO::PARAM_STR);
    $ps->bindValue(':encodedFields', $encodedFields, PDO::PARAM_STR);
    $ps->bindValue(':permissionsJson', $permissionsJson, PDO::PARAM_STR);
    $result = $ps->execute();
    if (!$result) {
        echo '{"status" : "failed", "message" : "Unable to add the table, internal error" }';
        return;
    }
    $ps = $db->prepare(
        "insert into table_admins (userId, tableName, isSuperAdmin) values (:userId, :tableName, 1)");
    $ps->bindValue(':userId', $userId, PDO::PARAM_INT);
    $ps->bindValue(':tableName', $tableName, PDO::PARAM_STR);
    $result = $ps->execute();
    if (!$result) {
        echo '{"status" : "failed", "message" : "Unable to add the table, internal error" }';
        return;
    }

// Creating table
    $tempFields = array();
    foreach ($fields as $field) {
        if ($field['type'] == 'primaryKey') {
            if ($field['autoIncrement']) {
                array_push($tempFields, 'primaryKey INT primary key auto_increment not null');
            } else {
                array_push($tempFields, 'primaryKey varchar(100) primary key not null');
            }

        } else {
            array_push($tempFields, $field['fieldId'] . ' ' . getMysqlFieldType($field['type']) . getRequired($field['required']));
        }
    }

    $query = 'create table ' . $tableName . ' (' . join(", ", $tempFields) . ')';

    $result = $db->query($query);

    if ($result) {
        echo '{"status" : "success"}';
    } else {
        echo '{"status" : "failed", "message" : "Unable to add the table, internal error" }';
    }
    $db->commit();

} catch (Exception $ex) {
	echo 'Error: ' .$ex->getMessage();
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo '{"status" : "error"}';
}

function getRequired($required)
{
    return $required ? 'NOT NULL' : '';
}

function isFieldsArrayValid($fields)
{
    if (!$fields) {
        return false;
    }
    foreach ($fields as $field) {
        foreach ($field as $key => $value) {
            $type = gettype($value);
            switch ($key) {
                case 'name':
                    if ($type != 'string') {
                        return false;
                    }
                    break;
                case 'type':
                    if ($type != 'string' || !isValidFieldType($value)) {
                        return false;
                    }
                    break;
                case 'required':
                    if ($type != 'boolean') {
                        return false;
                    }
                    break;
                case 'isVisible':
                    if ($type != 'boolean') {
                        return false;
                    }

                    break;
                case 'autoIncrement':
                    if ($type != 'boolean') {
                        return false;
                    }
                    break;
                case 'options':
                    if ($type != 'array') {
                        return false;
                    }
                    break;
                case 'value':
                    continue;
                default:
                    return false;
            }
        }
    }
    return true;
}

function isValidFieldType($type)
{
    switch ($type) {
        case 'Text':
        case 'Select':
        case 'Checkbox':
        case 'Radio Button':
        case 'Number':
        case 'Decimal Number':
        case 'Date':
        case 'Time':
        case 'Date Time':
        case 'primaryKey':
            return true;
        default:
            return false;
    }
}

function getMysqlFieldType($type)
{
    switch ($type) {
        case 'Text':
        case 'Select':
        case 'Checkbox':
        case 'Radio Button':
            return 'TEXT';
        case 'Number':
            return 'BIGINT';
        case 'Decimal Number':
            return 'DOUBLE(M,D)';
        case 'Date':
            return 'DATE';
        case 'Time':
            return 'TIME';
        case 'Date Time':
            return 'DATETIME';
        case 'primaryKey':
            return 'INT primary key auto_increment';
        default:
            return 'TEXT';
    }
}
