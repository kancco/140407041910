<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/_db.php';
require_once __DIR__.'/_normalize.php';
json_fatal_guard(__DIR__.'/source_import_fatal.log');

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

// جدول اصلی فقط با فیلدهای موردنظر شما
$m->query("CREATE TABLE IF NOT EXISTS tac_source_raw (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    university_name_fa VARCHAR(300),
    source_code VARCHAR(80) NOT NULL,
    file_number VARCHAR(80),
    production_site_name_fa VARCHAR(300),
    production_site_country_fa VARCHAR(160),
    production_site_type VARCHAR(160),
    site_type VARCHAR(160),
    site_activity_type VARCHAR(160),
    license_holder_name_fa VARCHAR(300),
    company_national_id VARCHAR(20),
    manufacturer_name_fa VARCHAR(300),
    national_id VARCHAR(20),
    branch_name_fa VARCHAR(300),
    branch_type VARCHAR(160),
    production_line_name_fa VARCHAR(400),
    production_line_type VARCHAR(160),
    group_category VARCHAR(200),
    line_group_category VARCHAR(400),
    status_fa VARCHAR(120),
    license_type_fa VARCHAR(160),
    technical_committee_datetime_raw VARCHAR(40),
    technical_committee_number VARCHAR(80),
    issue_datetime_raw VARCHAR(40),
    expire_datetime_raw VARCHAR(40),
    request_register_datetime_raw VARCHAR(40),
    final_fix_datetime_raw VARCHAR(40),
    validity_duration_text VARCHAR(80),
    review_duration_days INT,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    raw_import_batch VARCHAR(32),
    extra_data LONGTEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$inserted = 0; $updated = 0; $skipped = 0; $errors = 0;

foreach($rows as $row){
    $university_name_fa = $row['university_name_fa'] ?? null;
    $source_code = $row['source_code'] ?? null;
    if(!$source_code){ $skipped++; continue; }
    $file_number = $row['file_number'] ?? null;
    $production_site_name_fa = $row['production_site_name_fa'] ?? null;
    $production_site_country_fa = $row['production_site_country_fa'] ?? null;
    $production_site_type = $row['production_site_type'] ?? null;
    $site_type = $row['site_type'] ?? null;
    $site_activity_type = $row['site_activity_type'] ?? null;
    $license_holder_name_fa = $row['license_holder_name_fa'] ?? null;
    $company_national_id = $row['company_national_id'] ?? null;
    $manufacturer_name_fa = $row['manufacturer_name_fa'] ?? null;
    $national_id = $row['national_id'] ?? null;
    $branch_name_fa = $row['branch_name_fa'] ?? null;
    $branch_type = $row['branch_type'] ?? null;
    $production_line_name_fa = $row['production_line_name_fa'] ?? null;
    $production_line_type = $row['production_line_type'] ?? null;
    $group_category = $row['group_category'] ?? null;
    $line_group_category = $row['line_group_category'] ?? null;
    $status_fa = $row['status_fa'] ?? null;
    $license_type_fa = $row['license_type_fa'] ?? null;
    $technical_committee_datetime_raw = $row['technical_committee_datetime_raw'] ?? null;
    $technical_committee_number = $row['technical_committee_number'] ?? null;
    $issue_datetime_raw = $row['issue_datetime_raw'] ?? null;
    $expire_datetime_raw = $row['expire_datetime_raw'] ?? null;
    $request_register_datetime_raw = $row['request_register_datetime_raw'] ?? null;
    $final_fix_datetime_raw = $row['final_fix_datetime_raw'] ?? null;
    $validity_duration_text = $row['validity_duration_text'] ?? null;
    $review_duration_days = $row['review_duration_days'] ?? null;
    $extra_data = isset($row['extra_data']) ? (is_scalar($row['extra_data'])?$row['extra_data']:json_encode($row['extra_data'],JSON_UNESCAPED_UNICODE)) : null;
    $raw_import_batch = $batch;

    // پیدا کردن رکورد تکراری
    $sql_check = "SELECT * FROM tac_source_raw WHERE source_code = ?";
    $stmt_check = $m->prepare($sql_check);
    $stmt_check->bind_param('s', $source_code);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    $found_same = false;
    $found_id = null;
    while($old = $result->fetch_assoc()){
        // مقایسه همه فیلدها به جز id، created_at، updated_at
        $diff = false;
        foreach([
            'university_name_fa','file_number','production_site_name_fa','production_site_country_fa','production_site_type',
            'site_type','site_activity_type','license_holder_name_fa','company_national_id','manufacturer_name_fa','national_id',
            'branch_name_fa','branch_type','production_line_name_fa','production_line_type','group_category','line_group_category',
            'status_fa','license_type_fa','technical_committee_datetime_raw','technical_committee_number','issue_datetime_raw',
            'expire_datetime_raw','request_register_datetime_raw','final_fix_datetime_raw','validity_duration_text','review_duration_days',
            'raw_import_batch','extra_data'
        ] as $f) {
            $old_val = ($old[$f]===null ? '' : (string)$old[$f]);
            $new_val = ($$f===null ? '' : (string)$$f);
            if($old_val !== $new_val){
                $diff = true;
                break;
            }
        }
        if(!$diff){
            $found_same = true;
            $found_id = $old['id'];
            break;
        }
    }
    if($found_same && $found_id){
        $sql_update = "UPDATE tac_source_raw SET updated_at=NOW(), extra_data=? WHERE id=?";
        $stmt_update = $m->prepare($sql_update);
        $stmt_update->bind_param('si', $extra_data, $found_id);
        if(!$stmt_update->execute()){ $errors++; continue; }
        $updated++;
        continue;
    }

    // اگر حتی یکی فرق داشت، رکورد جدید بساز
    $sql_insert = "INSERT INTO tac_source_raw (
        university_name_fa, source_code, file_number, production_site_name_fa, production_site_country_fa, production_site_type,
        site_type, site_activity_type, license_holder_name_fa, company_national_id, manufacturer_name_fa, national_id,
        branch_name_fa, branch_type, production_line_name_fa, production_line_type, group_category, line_group_category,
        status_fa, license_type_fa, technical_committee_datetime_raw, technical_committee_number, issue_datetime_raw,
        expire_datetime_raw, request_register_datetime_raw, final_fix_datetime_raw, validity_duration_text, review_duration_days,
        raw_import_batch, extra_data
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt_insert = $m->prepare($sql_insert);
    $stmt_insert->bind_param(
        'ssssssssssssssssssssssssssssss',
        $university_name_fa, $source_code, $file_number, $production_site_name_fa, $production_site_country_fa, $production_site_type,
        $site_type, $site_activity_type, $license_holder_name_fa, $company_national_id, $manufacturer_name_fa, $national_id,
        $branch_name_fa, $branch_type, $production_line_name_fa, $production_line_type, $group_category, $line_group_category,
        $status_fa, $license_type_fa, $technical_committee_datetime_raw, $technical_committee_number, $issue_datetime_raw,
        $expire_datetime_raw, $request_register_datetime_raw, $final_fix_datetime_raw, $validity_duration_text, $review_duration_days,
        $raw_import_batch, $extra_data
    );
    if(!$stmt_insert->execute()){ $errors++; continue; }
    $inserted++;
}

echo json_encode([
    'inserted'=>$inserted,
    'updated'=>$updated,
    'skipped'=>$skipped,
    'errors'=>$errors,
    'batch'=>$batch
],JSON_UNESCAPED_UNICODE);