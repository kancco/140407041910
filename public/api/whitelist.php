<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/_db.php'; // کانکشن دیتابیس را از این فایل می‌گیرد

$m = db(); // تابع اتصال به دیتابیس

$sql = "SELECT factory_name_fa, manager_name_fa FROM factories_raw GROUP BY factory_name_fa LIMIT 100";
$res = $m->query($sql);

$list = [];
while($row = $res->fetch_assoc()){
    $list[] = $row;
}
echo json_encode($list, JSON_UNESCAPED_UNICODE);