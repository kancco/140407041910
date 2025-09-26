<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/_db.php';

$national_id = isset($_GET['national_id']) ? trim($_GET['national_id']) : '';
if(!$national_id) { echo json_encode(['error'=>'شناسه ملی ارسال نشده']); exit; }

$m = db();

// نوع صنعت تولیدی و نوع سایت تولیدی از factories
$stmt1 = $m->prepare("SELECT industry_type, site_type FROM factories WHERE national_id=? LIMIT 1");
$stmt1->bind_param('s', $national_id);
$stmt1->execute();
$res1 = $stmt1->get_result()->fetch_assoc();
$stmt1->close();

// شماره موبایل مدیر از companies
$stmt2 = $m->prepare("SELECT mobile_manager FROM companies WHERE national_id=? LIMIT 1");
$stmt2->bind_param('s', $national_id);
$stmt2->execute();
$res2 = $stmt2->get_result()->fetch_assoc();
$stmt2->close();

// نام و موبایل مسئول فنی از techs
$stmt3 = $m->prepare("SELECT tech_name, mobile_tech FROM techs WHERE national_id=? LIMIT 1");
$stmt3->bind_param('s', $national_id);
$stmt3->execute();
$res3 = $stmt3->get_result()->fetch_assoc();
$stmt3->close();

echo json_encode([
    'industry_type'  => $res1['industry_type'] ?? '',
    'site_type'      => $res1['site_type'] ?? '',
    'mobile_manager' => $res2['mobile_manager'] ?? '',
    'tech_name'      => $res3['tech_name'] ?? '',
    'mobile_tech'    => $res3['mobile_tech'] ?? ''
], JSON_UNESCAPED_UNICODE);
?>