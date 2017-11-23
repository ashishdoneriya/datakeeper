<?php

function isSuperAdmin($db, $userId, $tableName) {
	if ($userId == null || $tableName == null) {
		return false;
	}
	$rows = $db->query("select isSuperAdmin from table_admins where userId=$userId and tableName='$tableName'");
	$row = $rows->fetch();
	if (gettype($row) == 'boolean') {
		return false;
	}
	return $row['isSuperAdmin'] == 1 ? true : false;
}

function isAdmin($db, $userId, $tableName) {
	if ($userId == null || $tableName == null) {
		return false;
	}
	$rows = $db->query("select count(*) from table_admins where userId=$userId and tableName='$tableName'");
	$row = $rows->fetch();
	return (int) $row[0] == 1;
}

// add /update / delete / read
function isAllowedToAccessTable($db, $userId, $tableName, $accessType) {
	$result = array();
	if (isAdmin($db, $userId, $tableName)) {
		$result['allowed'] = true;
		$result['approval'] = false;
		$result['loginRequired'] = true;
		$result['admin'] = true;
		return $result;
	}
	if ($userId != null) {
		$result['admin'] = false;
		$rows = $db->query("select role from guest_permissions where userId=$userId and tableName='$tableName'");
		$row = $rows->fetch();
		if (gettype($row) != 'boolean') {
			$row = json_decode($row['role'], true);
			$row = $row[$accessType];
			$result['allowed'] = $row['allowed'];
			$result['approval'] = $row['approval'];
			$result['loginRequired'] = $row['loginRequired'];
			return $result;
		}
	}
	$rows = $db->query("select publicRole from tables_info where tableName='$tableName'");
	$row = $rows->fetch();
	$row = json_decode($row['publicRole'], true);
	$row = $row[$accessType];
	if ($row['loginRequired'] == true && $userId == null) {
		$result['allowed'] = false;
	} else {
		$result['allowed'] = $row['allowed'];
		$result['approval'] = $row['approval'];
		$result['loginRequired'] = $row['loginRequired'];
	}
	return $result;
}

//TODO in case of a unique person is allowed to read table
function getFields($db, $userId, $tableName) {
	if ($tableName == null) {
		return null;
	}

	if (isAdmin($db, $userId, $tableName)) {
		$rows = $db->query("select fields from tables_info where tableName='$tableName'");
		$row = $rows->fetch();
		return json_decode($row['fields'], true);
	}

	$access = isAllowedToAccessTable($db, $userId, $tableName, 'read');

	if (!$access['allowed']) {
		$access = isAllowedToAccessTable($db, $userId, $tableName, 'add');
		if (!$access['allowed']) {
			$access = isAllowedToAccessTable($db, $userId, $tableName, 'update');
			if (!$access['allowed']) {
				$access = isAllowedToAccessTable($db, $userId, $tableName, 'delete');
				return null;
			}
		}
	}
	$rows = $db->query("select fields from tables_info where tableName='$tableName'");
	$row = $rows->fetch();
	$fields = json_decode($row['fields'], true);

	$finalFields = array();
	foreach ($fields as $field) {
		if ($userId == null && ! $field['isVisible']) {
			continue;
		}
		array_push($finalFields, $field);
	}
	$finalFields;
}

function getUserId($db, $email) {
	$rows = $db->query("select userId from users where email=$email");
	$row = $rows->fetch();
	if (gettype($row) == 'boolean') {
		return null;
	}
	return $row['userId'];
}

function getRolesJson($db, $userId, $tableName) {

	if (isAdmin($db, $userId, $tableName)) {
		$roles = array('add', 'update', 'delete', 'read');
		$result = array();
		$result['allowed'] = true;
		$result['approval'] = false;
		$result['loginRequired'] = true;
		$result['admin'] = true;
		$ddArr = array();
		foreach ($roles as $role) {
			$ddArr[$role] = $result;
		}
		return $ddArr;
	}
	if ($userId != null) {
		$rows = $db->query("select role from guest_permissions where userId=$userId and tableName='$tableName'");
		$row = $rows->fetch();
		if (gettype($row) != 'boolean') {
			return json_decode($row['role'], true);
		}
	}
	$rows = $db->query("select role from tables_info where tableName='$tableName'");
	$row = $rows->fetch();
	return json_decode($row['publicRole'], true);
}

?>