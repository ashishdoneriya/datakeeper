<?php

header("Access-Control-Allow-Methods: GET");

include_once './config/database.php';

$database = new Database();
$db = $database->getConnection();
$db->beginTransaction();
try {
// Creating table 'users'
    $query = "create table if not exists users ( userId int primary key not null auto_increment, name varchar(80), email varchar(70) not null, password varchar(50) not null)";
    $db->query($query);

/*
permissions json format = {
"read" : {"allow" : false, "approval" : true},
"add" : {"allow" : false, "approval" : true, "loginRequired" : true},
"update" : {"allow" : false, "approval" : true, "loginRequired" : true},
"delete" : {"allow" : false, "approval" : true, "loginRequired" : true}
}
 */

// Creating table 'tables_info'
    $query = "create table if not exists tables_info ( tableName varchar(100) primary key not null, displayedTableName varchar(100) not null, fields text not null, publicPermissions text not null)";
    $db->query($query);

// Creating table 'table_admins'
    $query = "create table if not exists table_admins (userId int not null, tableName varchar(100) not null, isSuperAdmin int(1) not null, constraint foreign key (userId) references users(userId), constraint foreign key (tableName) references tables_info(tableName) ON DELETE CASCADE)";
    $db->query($query);

// Creating table 'guests_permissions'
    $query = "create table if not exists guest_permissions ( userId int not null, tableName varchar(100) not null, permissions text not null, constraint foreign key (userId) references users(userId) ON DELETE CASCADE, constraint foreign key (tableName) references tables_info(tableName) ON DELETE CASCADE)";
    $db->query($query);

// Creating table 'data_requests'
    // requestType = add / update /delete
    $query = "create table if not exists data_requests (reqId int primary key not null auto_increment, userId int, tableName varchar(100) not null, fields text not null, oldPrimaryKey text, requestType varchar(6) not null, constraint foreign key (userId) references users(userId) ON DELETE CASCADE, constraint foreign key (userId) references users(userId) ON DELETE CASCADE, constraint foreign key (tableName) references tables_info(tableName) ON DELETE CASCADE)";
    $db->query($query);

    echo "success";
    $db->commit();

} catch (Exception $ex) {
	echo 'Error: ' .$ex->getMessage();
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo '{"status" : "error"}';
}
