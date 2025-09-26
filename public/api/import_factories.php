<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/import_factories_php_error.log');

require_once __DIR__.'/_db.php';
require_once __DIR__.'/_normalize.php';
json_fatal_guard(__DIR__.'/factories_import_fatal.log');

$raw = file_get_contents('php://input');
if ($raw === false) {
    http_response_code(400);
    echo json_encode(['error' => 'no input']);
    exit;
}
$data = json_decode($raw, true);
if (!$data || !isset($data['rows']) || !is_array($data['rows'])) {
    http_response_code(400);
    echo json_encode(['error' => 'bad json']);
    exit;
}
$rows = $data['rows'];
$batch = isset($data['batch_id']) ? (string)$data['batch_id'] : (string)time();
if (!count($rows)) {
    echo json_encode(['inserted' => 0, 'skipped' => 0, 'errors' => 0, 'batch' => $batch]);
    exit;
}

$m = db();

// گرفتن لیست وایت‌لیست (شناسه ملی و نام کارخانه)
$whitelist_national_ids = [];
$whitelist_names = [];
$res = $m->query("SELECT company_national_id, company_name_fa FROM factories_whitelist");
while($row_wh = $res->fetch_assoc()) {
    if($row_wh['company_national_id']) $whitelist_national_ids[] = $row_wh['company_national_id'];
    if($row_wh['company_name_fa']) $whitelist_names[] = $row_wh['company_name_fa'];
}
unset($res);

$m->query("CREATE TABLE IF NOT EXISTS factories_raw (
 id BIGINT UNSIGNED AUTO_INCREMENT,
 factory_name_fa VARCHAR(300), national_id VARCHAR(20), economic_code VARCHAR(30),
 province_name VARCHAR(120), city_name VARCHAR(120), address_fa VARCHAR(600),
 postal_code VARCHAR(20), phone_raw VARCHAR(120), email VARCHAR(200), website VARCHAR(300),
 manager_name_fa VARCHAR(200), manager_national_id VARCHAR(20), company_name_en VARCHAR(300),
 company_type_fa VARCHAR(160), country_name_fa VARCHAR(160), gln_code VARCHAR(40),
 raw_import_batch VARCHAR(32), extra_data LONGTEXT,
 created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id),
 UNIQUE KEY uq_fact_nat (national_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$inserted = 0;
$updated = 0;
$skipped = 0;
$errors = 0;
$skipReasons = ['missing_id'=>0, 'not_in_whitelist'=>0, 'other'=>0];

foreach($rows as $row) {
    $factory_name_fa = $row['factory_name_fa'] ?? null;
    $national_id     = normalize_id($row['national_id'] ?? null);
    $economic_code   = $row['economic_code'] ?? null;
    $province_name   = $row['province_name'] ?? null;
    $city_name       = $row['city_name'] ?? null;
    $address_fa      = $row['address_fa'] ?? null;
    $postal_code     = $row['postal_code'] ?? null;
    $phone_raw       = $row['phone_raw'] ?? null;
    $email           = $row['email'] ?? null;
    $website         = $row['website'] ?? null;
    $manager_name_fa = $row['manager_name_fa'] ?? null;
    $manager_national_id = normalize_id($row['manager_national_id'] ?? null);
    $company_name_en = $row['company_name_en'] ?? null;
    $company_type_fa = $row['company_type_fa'] ?? null;
    $country_name_fa = $row['country_name_fa'] ?? null;
    $gln_code        = $row['gln_code'] ?? null;
    $extra_data      = isset($row['extra_data']) ? (is_scalar($row['extra_data'])?$row['extra_data']:json_encode($row['extra_data'],JSON_UNESCAPED_UNICODE)) : null;

    // شرط وایت لیست
    if (
        !in_array($national_id, $whitelist_national_ids, true) &&
        !in_array($factory_name_fa, $whitelist_names, true)
    ) {
        $skipped++;
        $skipReasons['not_in_whitelist'] = ($skipReasons['not_in_whitelist'] ?? 0) + 1;
        continue;
    }

    if ($national_id === '') {
        $skipped++;
        $skipReasons['missing_id']++;
        continue;
    }

    $sql="INSERT INTO factories_raw(factory_name_fa, national_id, economic_code, province_name, city_name, address_fa, postal_code, phone_raw, email, website, manager_name_fa, manager_national_id, company_name_en, company_type_fa, country_name_fa, gln_code, raw_import_batch, extra_data)
          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
          ON DUPLICATE KEY UPDATE
            factory_name_fa=VALUES(factory_name_fa), economic_code=VALUES(economic_code), province_name=VALUES(province_name),
            city_name=VALUES(city_name), address_fa=VALUES(address_fa), postal_code=VALUES(postal_code), phone_raw=VALUES(phone_raw),
            email=VALUES(email), website=VALUES(website), manager_name_fa=VALUES(manager_name_fa), manager_national_id=VALUES(manager_national_id),
            company_name_en=VALUES(company_name_en), company_type_fa=VALUES(company_type_fa), country_name_fa=VALUES(country_name_fa),
            gln_code=VALUES(gln_code), raw_import_batch=VALUES(raw_import_batch), extra_data=VALUES(extra_data)";
    $stmt = $m->prepare($sql);
    $stmt->bind_param('ssssssssssssssssss',
        $factory_name_fa,$national_id,$economic_code,$province_name,$city_name,$address_fa,$postal_code,$phone_raw,
        $email,$website,$manager_name_fa,$manager_national_id,$company_name_en,$company_type_fa,$country_name_fa,
        $gln_code,$batch,$extra_data
    );
    if (!$stmt->execute()) {
        $errors++;
        $skipReasons['other']++;
        continue;
    }
    if ($stmt->affected_rows === 1) $inserted++;
    else $updated++;
}

echo json_encode([
  'inserted' => $inserted,
  'updated'  => $updated,
  'skipped'  => $skipped,
  'errors'   => $errors,
  'batch'    => $batch,
  'skip_reasons' => $skipReasons
], JSON_UNESCAPED_UNICODE);