<?php

header("Access-Control-Allow-Methods: GET");

include_once './config/database.php';
include_once './objects/users.php';

$database = new Database();
$db = $database->getConnection();
// Creating table 'users'
$query = "create table if not exists users ( userId int primary key not null auto_increment, name varchar(80), email varchar(70) not null, password varchar(15) not null)";
$db->query($query);

// Creating table 'users_tables'
//public role = none/user/contributor
$query = "create table if not exists users_tables ( userId int not null, tableName varchar(30) not null, displayedTableName varchar(100) not null, fields text not null, publicRole varchar(15) not null, foreign key (userId) references users(userId))";
$db->query($query);

// Creating table 'guests_permissions'
//role = administrator / contributor / user
$query = "create table if not exists guest_permissions ( userId int not null, tableName varchar(30) not null, role varchar(30) not null, foreign key (userId) references users(userId))";
$db->query($query);

// Creating table 'access_requests'
$query = "create table if not exists write_requests ( userId int not null, tableName varchar(30) not null, fields text not null, foreign key (userId) references users(userId), foreign key (refreeId) references users(userId))";
$db->query($query);

echo "success";

?>