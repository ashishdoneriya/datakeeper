<?php

header("Access-Control-Allow-Methods: GET");

include_once './config/database.php';

$database = new Database();
$db = $database->getConnection();
// Creating table 'users'
$query = "create table if not exists users ( userId int primary key not null auto_increment, name varchar(80), email varchar(70) not null, password varchar(15) not null)";
$db->query($query);

/*
roles json format = {
    "read" : {"allow" : false, "approval" : true},
    "add" : {"allow" : false, "approval" : true, "loginRequired" : true},
    "update" : {"allow" : false, "approval" : true, "loginRequired" : true},
    "delete" : {"allow" : false, "approval" : true, "loginRequired" : true}
}
*/

// Creating table 'tables_info'
$query = "create table if not exists tables_info ( tableName varchar(100) primary key not null, displayedTableName varchar(100) not null, fields text not null, publicRole text not null)";
$db->query($query);

// Creating table 'table_admins'
$query = "create table if not exists table_admins (userId int not null, tableName varchar(100) not null, isSuperAdmin int(1) not null, foreign key (userId) references users(userId), foreign key (tableName) references tables_info(tableName))";
$db->query($query);

// Creating table 'guests_permissions'
$query = "create table if not exists guest_permissions ( userId int not null, tableName varchar(100) not null, role text not null, foreign key (userId) references users(userId), foreign key (tableName) references tables_info(tableName))";
$db->query($query);

// Creating table 'data_requests'
// requestType = add / update /delete
$query = "create table if not exists data_requests (reqId int primary key not null auto_increment, userId int, tableName varchar(100) not null, fields text not null, oldId int, requestType varchar(6) not null, foreign key (userId) references users(userId), foreign key (userId) references users(userId), foreign key (tableName) references tables_info(tableName))";
$db->query($query);

echo "success";

?>