<?php
require_once __DIR__.'/../api/db.php';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="whitelist_export.csv'");
$out=fopen('php://output','w');
fputcsv($out,['company_national_id','company_name_fa','active']);
$res=$mysqli->query("SELECT company_national_id,company_name_fa,active FROM factories_whitelist ORDER BY id");
while($r=$res->fetch_assoc()) fputcsv($out,$r);
fclose($out);