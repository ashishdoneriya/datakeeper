<?php
header("Access-Control-Allow-Methods: GET");

include_once './config/database.php';
include_once './utils.php';
session_start();
$userId = $_SESSION['userId'];
$database = new Database();
$db = $database->getConnection();
$tableName = htmlspecialchars(strip_tags($_GET['tableName']));
$query = trim(htmlspecialchars(strip_tags($_GET['searchQuery'])));
$searchQuery = trim($_GET['searchQuery']);
$pageNumber = htmlspecialchars(strip_tags($_GET['pageNumber']));
$maximumResults = htmlspecialchars(strip_tags($_GET['maximumResults']));
$sortBy = htmlspecialchars(strip_tags($_GET['sortBy']));
$order = htmlspecialchars(strip_tags($_GET['order']));

if (! doesTableExist($db, $tableName)) {
	header('HTTP/1.0 500 Internal Server Error');
	echo '{"status" : "failed", "message" : "No such table"}';
	return;
}

$finalFields = getFields($db, $userId, $tableName);
if ($finalFields == null) {
	header('HTTP/1.0 401 Unauthorized');
	echo 'You are not authorized.';
	return;
}

if (!$sortBy) {
	$sortBy = 'primaryKey';
} else {
	$foundOrderBy = false;
	foreach ($finalFields as $field) {
		if ($field['Id'] == $sortBy) {
			$foundOrderBy = true;
			break;
		}
	}
	if (! $foundOrderBy) {
		header('HTTP/1.0 401 Unauthorized');
		echo 'You are not authorized.';
		return;
	}
}

if (!$order) {
	$order = 'asc';
} else {
	if ($order != 'asc' && $order != 'desc' ) {
		header('HTTP/1.0 401 Unauthorized');
		echo 'You are not authorized.';
		return;
	}
	
}

$fieldsArr = array();
foreach ($finalFields as $field) {
	array_push($fieldsArr, $field['fieldId']);
}
$fieldsString = join(', ', $fieldsArr);

$searchArr = array();
$isQueryNumeric = is_numeric($searchQuery);
if ($searchQuery) {
	foreach ($finalFields as $field) {
		if ($field['type'] == 'primaryKey') {
			if ( $field['autoIncrement']) {
				if ($isQueryNumeric) {
					array_push($searchArr, $field['fieldId'] . '=:' . $field['fieldId'] );
				}
				
			} else {
				array_push($searchArr, $field['fieldId'] . '=:' . $field['fieldId'] );
			}
			continue;
		}
		
		if ($field['type'] == 'Number' || $field['type'] == 'Decimal Number') {
			if ($isQueryNumeric) {
				array_push($searchArr,
						$field['fieldId'] . '=:' . $field['fieldId']);
			} else {
				array_push($searchArr,
						'cast(' . $field['fieldId'] . 'as char)' . ' like :' .
								 $field['fieldId']);
			}
			continue;
		}
		
		if ($field['type'] == 'Date' || $field['type'] == 'Time' ||
				 $field['type'] == 'Date Time') {
			array_push($searchArr, $field['fieldId'] . '=:' . $field['fieldId']);
			array_push($searchArr, 'cast(' . $field['fieldId'] . ' as char)' . ' like :' .
							 $field['fieldId'] . 'Str');
			continue;
		}
		
		array_push($searchArr, $field['fieldId'] . ' like :' . $field['fieldId']);
	}
}

$whereSearch = '';
if (count($searchArr) > 0) {
	$whereSearch = ' where ' . join(" or ", $searchArr);
}

$query = "select $fieldsString from $tableName $whereSearch  order by $sortBy $order";

if ($pageNumber != null && $maximumResults != null && is_numeric($pageNumber) &&
		 is_numeric($maximumResults)) {
	$query = $query . " limit :firstRow, :max";
}

$pd = $db->prepare($query);

if ($pageNumber != null && $maximumResults != null && is_numeric($pageNumber) &&
		 is_numeric($maximumResults)) {
	$max = (int) $maximumResults;
	$firstRow = ((int) $pageNumber - 1) * $max;
	$pd->bindValue(':max', $max, PDO::PARAM_INT);
	$pd->bindValue(':firstRow', $firstRow, PDO::PARAM_INT);
}

if ($searchQuery && count($searchArr) > 0) {
	foreach ($finalFields as $field) {
		if ($field['type'] == 'primaryKey') {
			if ( $field['autoIncrement']) {
				if ($isQueryNumeric) {
					$pd->bindValue(':' . $field['fieldId'] , $searchQuery, PDO::PARAM_INT);
				}
				
			} else {
				$pd->bindValue(':' . $field['fieldId'] , $searchQuery, PDO::PARAM_STR);
			}
			continue;
		}
		
		if ($field['type'] == 'Number' || $field['type'] == 'Decimal Number') {
			if ($isQueryNumeric) {
				$pd->bindValue(':' . $field['fieldId'], $searchQuery, PDO::PARAM_INT);
			} else {
				$pd->bindValue(':' . $field['fieldId'], '%' . $searchQuery . '%',
						PDO::PARAM_STR);
			}
			continue;
		}
		
		if ($field['type'] == 'Date' || $field['type'] == 'Time' ||
				 $field['type'] == 'Date Time') {
				 	$pd->bindValue(':' . $field['fieldId'], $searchQuery, PDO::PARAM_STR);
				 	$pd->bindValue(':' . $field['fieldId'] . 'Str', '%' . $searchQuery . '%',
					PDO::PARAM_STR);
			continue;
		}
		
		$pd->bindValue(':' . $field['fieldId'], '%' . $searchQuery . '%',
				PDO::PARAM_STR);
	}
}

$result = $pd->execute();
if (! $result) {
	header('HTTP/1.0 500 Internal Server Error');
	echo '{"status" : "failed", "message" : "Unable to fetch data"}';
}
$rows = $pd->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows);

?>