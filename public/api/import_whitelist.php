<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/_db.php';
require_once __DIR__.'/_normalize.php';
json_fatal_guard(__DIR__.'/whitelist_import_fatal.log');

$raw = file_get_contents('php://input');
if($raw === false){ http_response_code(400); echo json_encode(['error'=>'no input']); exit; }
$data = json_decode($raw, true);
if(!$data || !isset($data['rows']) || !is_array($data['rows'])){
    http_response_code(400); echo json_encode(['error'=>'bad json']); exit;
}
$rows = $data['rows'];
$batch = isset($data['batch_id']) ? (string)$data['batch_id'] : (string)time();
if(!count($rows)){ echo json_encode(['inserted'=>0,'skipped'=>0,'errors'=>0,'batch'=>$batch]); exit; }

$m = db();

$m->query("CREATE TABLE IF NOT EXISTS factories_whitelist (
 id BIGINT UNSIGNED AUTO_INCREMENT,
 company_national_id VARCHAR(20),
 company_name_fa VARCHAR(300),
 active TINYINT,
 city_name VARCHAR(120),
 manufacturing_industry_type VARCHAR(160),
 production_site_type VARCHAR(160),
 normalized_hash BINARY(16),
 raw_import_batch VARCHAR(32),
 extra_data LONGTEXT,
 created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id),
 UNIQUE KEY uq_nat (company_national_id),
 KEY idx_name (company_name_fa(150)),
 KEY idx_active (active),
 KEY idx_industry (manufacturing_industry_type),
 KEY idx_site_type (production_site_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

if(!function_exists('normalize_id')) {
    function normalize_id($str) {
        if($str === null) return '';
        $str = trim($str);
        $str = preg_replace('/\D/', '', $str);
        return $str;
    }
}

$inserted=0; $updated=0; $skipped=0; $errors=0;
foreach($rows as $row){
    $company_national_id = normalize_id($row['company_national_id']??null);
    $company_name_fa = $row['company_name_fa']??null;

    // --- تبدیل فعال/غیرفعال به عدد ---
    if (isset($row['active'])) {
        $v = trim($row['active']);
        $active = ($v === 'فعال' || strtolower($v) === 'active' || $v === '1' || $v === 1) ? 1 : 0;
    } else {
        $active = null;
    }

    $city_name = $row['city_name']??null;
    $manufacturing_industry_type = $row['manufacturing_industry_type']??null;
    $production_site_type = $row['production_site_type']??null;
    $extra_data = isset($row['extra_data']) ? (is_scalar($row['extra_data'])?$row['extra_data']:json_encode($row['extra_data'],JSON_UNESCAPED_UNICODE)) : null;

    if($company_national_id==='' && (!$company_name_fa||$company_name_fa==='')){ $skipped++; continue; }
    $sql="INSERT INTO factories_whitelist(company_national_id, company_name_fa, active, city_name, manufacturing_industry_type, production_site_type, raw_import_batch, extra_data)
          VALUES (?,?,?,?,?,?,?,?)
          ON DUPLICATE KEY UPDATE
            company_name_fa=VALUES(company_name_fa), active=VALUES(active), city_name=VALUES(city_name),
            manufacturing_industry_type=VALUES(manufacturing_industry_type), production_site_type=VALUES(production_site_type),
            raw_import_batch=VALUES(raw_import_batch), extra_data=VALUES(extra_data)";
    $stmt=$m->prepare($sql);
    $stmt->bind_param('ssisssss',
        $company_national_id,
        $company_name_fa,
        $active,
        $city_name,
        $manufacturing_industry_type,
        $production_site_type,
        $batch,
        $extra_data
    );
    if(!$stmt->execute()){ $errors++; continue; }
    if($stmt->affected_rows===1) $inserted++; else $updated++;
}
echo json_encode([
  'inserted'=>$inserted,
  'updated'=>$updated,
  'skipped'=>$skipped,
  'errors'=>$errors,
  'batch'=>$batch
],JSON_UNESCAPED_UNICODE);
?>