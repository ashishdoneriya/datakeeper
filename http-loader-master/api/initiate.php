<?php

header("Access-Control-Allow-Methods: GET");

include_once './config/database.php';
include_once './objects/users.php';

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

// Creating table 'users_tables'
$query = "create table if not exists users_tables ( userId int not null, tableName varchar(30) not null, displayedTableName varchar(100) not null, fields text not null, publicRole text not null, foreign key (userId) references users(userId))";
$db->query($query);

// Creating table 'guests_permissions'
$query = "create table if not exists guest_permissions ( userId int not null, tableName varchar(30) not null, role text not null, foreign key (userId) references users(userId))";
$db->query($query);

// Creating table 'data_requests'
// requestType = add / update /delete
$query = "create table if not exists data_requests (reqId int primary key not null auto_increment, userId int, tableName varchar(30) not null, fields text not null, requestType varchar(6) not null, foreign key (userId) references users(userId), foreign key (refreeId) references users(userId))";
$db->query($query);

echo "success";

?>