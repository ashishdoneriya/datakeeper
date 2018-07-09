<?php

header("Access-Control-Allow-Methods: POST");

include_once './config/database.php';
include_once './utils.php';

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
    $displayedTableName = htmlspecialchars(strip_tags($data['displayedTableName']));
    $tableName = htmlspecialchars(strip_tags($data['tableName']));
    $newFields = $data['fields'];

    if (!$displayedTableName || !$tableName || !$newFields) {
        header('HTTP/1.0 401 Unauthorized');
        echo 'You are not authorized.';
        return;
    }

    if (!doesTableExist($db, $tableName)) {
        header('HTTP/1.0 500 Internal Server Error');
        echo '{"status" : "failed", "message" : "No such table"}';
        return;
    }

    if (!isSuperAdmin($db, $userId, $tableName)) {
        header('HTTP/1.0 401 Unauthorized');
        echo 'You are not authorized.';
        return;
    }

    if (!isFieldsArrayValid($newFields)) {
        echo '{"status" : "failed", "message" : "Invalid json format" }';
        return;
    }

    $idsFound = 0;
    $length = count($newFields);
    for ($x = 0; $x < $length; $x++) {
        $field = (object) $newFields[$x];
        if ($field->type == 'primaryKey') {
            $idsFound++;
        }
        if (!isValidFieldType($field->type)) {
            echo '{"status" : "failed", "message" : "Invalid field type(s)" }';
            return;
        }
    }

    if ($idsFound == 0) {
        echo '{"status" : "failed", "message" : "No Id provided" }';
        return;
    }
    if ($idsFound > 1) {
        echo '{"status" : "failed", "message" : "Multiple Ids provided" }';
        return;
    }

    $oldFields = getFields($db, $userId, $tableName);
    if ($oldFields == null) {
        header('HTTP/1.0 401 Unauthorized');
        echo 'You are not authorized.';
        return;
    }
    $prevCount = count($oldFields) + 1;
    $length = count($newFields);
    for ($x = 0; $x < $length; $x++) {
        $newField = (object) $newFields[$x];
        if ($newField->type == 'primaryKey') {
            continue;
        }
        if (!property_exists($newField, 'fieldId')) {
            $prevCount++;
            $temp = preg_replace("/\W|_/", "", $newField->name);
            if (strlen($temp) > 10) {
                $temp = substr($temp, 0, 10);
            }
            $newField->fieldId = 'col_' . $temp . $prevCount;

            $db->query("alter table " . $tableName . " add " . $newField->fieldId . " " . getMysqlFieldType($newField->type) . getRequired($newField->required));
        } else {
            // Modifying column
            $db->query("alter table " . $tableName . " modify column " . $newField->fieldId . " " . getMysqlFieldType($newField->type) . getRequired($newField->required));
        }
        $newFields[$x] = (array) $newField;
    }

    foreach ($oldFields as $oldField) {
        $isExists = false;
        foreach ($newFields as $newField) {
            if ($oldField['fieldId'] == $newField['fieldId']) {
                $isExists = true;
            }
        }
        if ($isExists == false) {
            // Removing fields
            $db->query("alter table " . $tableName . " drop column " . $oldField['fieldId']);
        }
    }

// updating tables_info
    $encodedFields = json_encode($newFields);

    $ps = $db->prepare("update tables_info set displayedTableName=:displayedTableName, fields=:encodedFields where tableName=:tableName");
    $ps->bindValue(':displayedTableName', $displayedTableName);
    $ps->bindValue(':encodedFields', $encodedFields);
    $ps->bindValue(':tableName', $tableName);
    $result = $ps->execute();

    if ($result) {
        echo '{"status" : "success"}';
    } else {
        echo '{"status" : "failed", "message" : "Unable to add the table, internal error" }';
    }
    $db->commit();

} catch (Exception $ex) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo '{"status" : "error"}';
}

function isFieldsArrayValid($fields)
{
    foreach ($fields as $field) {
        foreach ($field as $key => $value) {
            $type = gettype($value);
            switch ($key) {
                case 'name':
                case 'fieldId':
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
                case 'isVisible':
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

function getRequired($required)
{
    if ($required == true) {
        return ' NOT NULL';
    }
    return '';
}
function getMysqlFieldType($type)
{
    switch ($type) {
        case 'primaryKey':
            return 'BIGINT primary key auto_increment';
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
        default:
            return 'TEXT';
    }
}
