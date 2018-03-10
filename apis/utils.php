<?php

/**
 * Checks if the user is super admin or not
 * @param PDO $db
 * @param int $userId
 * @param String $tableName
 * @return boolean
 */
function isSuperAdmin (PDO $db, int $userId, String $tableName)
{
	if ($userId == null || $tableName == null) {
		return false;
	}
	$ps = $db->prepare(
			"select isSuperAdmin from table_admins where userId=:userId and tableName=:tableName");
	$ps->bindValue(':userId', $userId, PDO::PARAM_INT);
	$ps->bindValue(':tableName', $tableName, PDO::PARAM_STR);
	$ps->execute();
	$row = $ps->fetch(PDO::FETCH_COLUMN);
	return $row != null && $row == 1;
}

function isAdmin (PDO $db, int $userId, String $tableName)
{
	if ($userId == null || $tableName == null) {
		return false;
	}
	$ps = $db->prepare(
			"select count(*) from table_admins where userId=:userId and tableName=:tableName");
	$ps->bindValue(':userId', $userId, PDO::PARAM_INT);
	$ps->bindValue(':tableName', $tableName, PDO::PARAM_STR);
	$ps->execute();
	$row = $ps->fetch(PDO::FETCH_COLUMN);
	return $row != null && $row == 1;
}

// add /update / delete / read
function isAllowedToAccessTable (PDO $db, int $userId, String $tableName,
		String $accessType)
{
	if (!$tableName){
		return false;
	}
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
		$ps = $db->prepare(
				"select permissions from guest_permissions where userId=:userId and tableName=:tableName");
		$ps->bindValue(':userId', $userId, PDO::PARAM_INT);
		$ps->bindValue(':tableName', $tableName, PDO::PARAM_STR);
		$ps->execute();
		$row = $ps->fetch(PDO::FETCH_COLUMN);
		if ($row != null) {
			$row = json_decode($row['permissions'], true);
			$row = $row[$accessType];
			$result['allowed'] = $row['allowed'];
			$result['approval'] = $row['approval'];
			$result['loginRequired'] = $row['loginRequired'];
			return $result;
		}
	}
	$ps = $db->prepare(
			"select publicPermissions from tables_info where tableName=:tableName");
	$ps->bindValue(':tableName', $tableName, PDO::PARAM_STR);
	$ps->execute();
	$row = $ps->fetch(PDO::FETCH_COLUMN);
	$row = json_decode($row, true);
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

// TODO in case of a unique person is allowed to read table
function getFields (PDO $db, int $userId, String $tableName)
{
	if ($tableName == null) {
		return null;
	}
	
	if (isAdmin($db, $userId, $tableName)) {
		$ps = $db->prepare(
				"select fields from tables_info where tableName=:tableName");
		$ps->bindValue(':tableName', $tableName, PDO::PARAM_STR);
		$ps->execute();
		$row = $ps->fetch(PDO::FETCH_COLUMN);
		return json_decode($row, true);
	}
	
	$access = isAllowedToAccessTable($db, $userId, $tableName, 'read');
	
	if (! $access['allowed']) {
		$access = isAllowedToAccessTable($db, $userId, $tableName, 'add');
		if (! $access['allowed']) {
			$access = isAllowedToAccessTable($db, $userId, $tableName, 'update');
			if (! $access['allowed']) {
				$access = isAllowedToAccessTable($db, $userId, $tableName,
						'delete');
				return null;
			}
		}
	}
	
	$ps = $db->prepare(
			"select fields from tables_info where tableName=:tableName");
	$ps->bindValue(':tableName', $tableName, PDO::PARAM_STR);
	$ps->execute();
	$row = $ps->fetch(PDO::FETCH_COLUMN);
	$fields = json_decode($row, true);
	
	$finalFields = array();
	foreach ($fields as $field) {
		if ($userId == null && ! $field['isVisible']) {
			continue;
		}
		array_push($finalFields, $field);
	}
	return $finalFields;
}

function getUserId (PDO $db, String $email)
{
	$ps = $db->prepare("select userId from users where email=:email");
	$ps->bindValue(':email', $email, PDO::PARAM_STR);
	$ps->execute();
	$row = $ps->fetch(PDO::FETCH_COLUMN);
	return $row;
}

function getPermissionsJson (PDO $db, int $userId, String $tableName)
{
	if (isAdmin($db, $userId, $tableName)) {
		$permissions = array(
				'add',
				'update',
				'delete',
				'read'
		);
		$result = array();
		$result['allowed'] = true;
		$result['approval'] = false;
		$result['loginRequired'] = true;
		$result['admin'] = true;
		$ddArr = array();
		foreach ($permissions as $permission) {
			$ddArr[$permission] = $result;
		}
		return $ddArr;
	}
	if ($userId != null) {
		$ps = $db->prepare(
				"select permissions from guest_permissions where userId=:userId and tableName=:tableName");
		$ps->bindValue(':userId', $userId, PDO::PARAM_INT);
		$ps->bindValue(':tableName', $tableName, PDO::PARAM_STR);
		$ps->execute();
		$row = $ps->fetch(PDO::FETCH_COLUMN);
		if ($row != null) {
			return json_decode($row, true);
		}
	}
	$ps = $db->prepare(
			"select publicPermissions from tables_info where tableName=:tableName");
	$ps->bindValue(':tableName', $tableName, PDO::PARAM_STR);
	$ps->execute();
	$row = $ps->fetch(PDO::FETCH_COLUMN);
	return json_decode($row, true);
}

function doesTableExist (PDO $db, String $tableName)
{
	if (!$tableName) {
		return false;
	}
	$ps = $db->prepare(
			'select displayedTableName from tables_info where tableName=:tableName');
	$ps->bindValue(':tableName', $tableName, PDO::PARAM_STR);
	$ps->execute();
	$row = $ps->fetch(PDO::FETCH_COLUMN);
	return $row != null;
}

?>
