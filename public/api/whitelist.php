<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/_db.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$m = db();

$where = "";
$params = [];
$types = "";

if($q){
    $where = "WHERE c.name LIKE CONCAT('%', ?, '%') OR c.city LIKE CONCAT('%', ?, '%')";
    $params = [$q, $q];
    $types = "ss";
}

$sql = "SELECT 
    c.name, 
    c.national_id, 
    c.city,
    f.industry_type, 
    f.site_type,
    c.mobile_manager,
    t.tech_name,
    t.mobile_tech
  FROM companies c
  LEFT JOIN factories f ON c.national_id = f.national_id
  LEFT JOIN techs t ON c.national_id = t.national_id
  $where
  LIMIT 100";

$stmt = $m->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => $m->error, 'sql' => $sql]);
    exit;
}

if($q){
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$res = $stmt->get_result();

$list = [];
while($row = $res->fetch_assoc()){
    $list[] = $row;
}
echo json_encode($list, JSON_UNESCAPED_UNICODE);