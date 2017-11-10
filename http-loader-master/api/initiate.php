<?php

header("Access-Control-Allow-Methods: GET");

include_once './config/database.php';
include_once './objects/users.php';

$database = new Database();
$db = $database->getConnection();
// Creating table 'users'
$query = "create table if not exists users ( userId int primary key not null auto_increment, firstname varchar(50), lastname varchar(30), email varchar(70) not null, password varchar(15) not null)";
$db->query($query);

// Creating table 'users_tables'
$query = "create table if not exists users_tables ( userId int not null, tableName varchar(30) not null, displayedTableName  varchar(100) not null, tableJson text not null, readPermission tinyint not null, writePermission tinyint not null, foreign key (userId) references users(userId))";
$db->query($query);

// Creating table 'guests_permissions'
$query = "create table if not exists guest_permissions ( userId int not null, tableName varchar(30) not null, canRead tinyint(1) not null, canWrite tinyint(1) not null, foreign key (userId) references users(userId))";
$db->query($query);

// Creating table 'access_requests'
$query = "create table if not exists access_requests ( userId int, refreeId int not null, email varchar(30), tableName varchar(30) not null, canRead tinyint(1) not null, canWrite tinyint(1) not null, foreign key (userId) references users(userId), foreign key (refreeId) references users(userId))";
$db->query($query);

?>