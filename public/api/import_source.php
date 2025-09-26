<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/_db.php';
require_once __DIR__.'/_normalize.php';
json_fatal_guard(__DIR__.'/source_import_fatal.log');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/import_source_php_error.log');

// دریافت دیتا
$raw = file_get_contents('php://input');
if($raw === false){ http_response_code(400); echo json_encode(['error'=>'no input']); exit; }
$data = json_decode($raw, true);
if(!$data || !isset($data['rows']) || !is_array($data['rows'])){
    http_response_code(400); echo json_encode(['error'=>'bad json']); exit;
}
$rows = $data['rows'];
$batch = isset($data['batch_id']) ? (string)$data['batch_id'] : (string)time();
if(!count($rows)){ echo json_encode(['inserted'=>0,'updated'=>0,'skipped'=>0,'errors'=>0,'batch'=>$batch]); exit; }

$m = db();

// گرفتن نام ستون‌های دیتابیس (به جز id, created_at, updated_at)
$db_columns = [];
$res = $m->query("SHOW COLUMNS FROM tac_source_raw");
while($row_col = $res->fetch_assoc()){
    $col = $row_col['Field'];
    if(!in_array($col, ['id','created_at','updated_at'])) $db_columns[] = $col;
}
$res->free();

// کش robust وایت‌لیست
$whitelist_national_ids = [];
$whitelist_names = [];
$res = $m->query("SELECT company_national_id, company_name_fa FROM factories_whitelist");
while($row_wh = $res->fetch_assoc()) {
    if($row_wh['company_national_id']) $whitelist_national_ids[] = normalize_id($row_wh['company_national_id']);
    if($row_wh['company_name_fa']) $whitelist_names[] = normalize_name($row_wh['company_name_fa']);
}
unset($res);

$inserted = 0; $updated = 0; $skipped = 0; $errors = 0;

foreach($rows as $row){
    $source_code = $row['source_code'] ?? null;
    if(!$source_code){ $skipped++; continue; }
    $company_national_id = normalize_id($row['company_national_id'] ?? null);
    $license_holder_name_fa = $row['license_holder_name_fa'] ?? null;
    $manufacturer_name_fa = $row['manufacturer_name_fa'] ?? null;

    // شرط وایت لیست
    if (
        !in_array($company_national_id, $whitelist_national_ids, true) &&
        !in_array(normalize_name($license_holder_name_fa), $whitelist_names, true) &&
        !in_array(normalize_name($manufacturer_name_fa), $whitelist_names, true)
    ) {
        $skipped++;
        continue;
    }

    // گرفتن همه رکوردهای دیتابیس با همین source_code
    $sql_check = "SELECT * FROM tac_source_raw WHERE source_code = ?";
    $stmt_check = $m->prepare($sql_check);
    $stmt_check->bind_param('s', $source_code);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    $found_same = false;
    $found_id = null;

    // فقط ستون‌هایی که هم در دیتابیس و هم در فایل وجود دارند را مقایسه کن
    while($old = $result->fetch_assoc()){
        $diff = false;
        foreach($db_columns as $col) {
            // فقط اگر این ستون در فایل هم هست آن را مقایسه کن
            if(array_key_exists($col, $row)){
                $old_val = ($old[$col]===null ? '' : (string)$old[$col]);
                $new_val = ($row[$col]===null ? '' : (string)$row[$col]);
                if($old_val !== $new_val){
                    $diff = true;
                    break;
                }
            }
        }
        if(!$diff){
            $found_same = true;
            $found_id = $old['id'];
            break;
        }
    }

    if($found_same && $found_id){
        // فقط updated_at را تغییر بده (یا هر فیلد دلخواه)
        $sql_update = "UPDATE tac_source_raw SET updated_at=NOW() WHERE id=?";
        $stmt_update = $m->prepare($sql_update);
        $stmt_update->bind_param('i', $found_id);
        if(!$stmt_update->execute()){ $errors++; continue; }
        $updated++;
        continue;
    }

    // اگر مشابه نبود، رکورد جدید بساز
    // فقط ستون‌هایی که هم در فایل و هم در دیتابیس وجود دارند را اینسرت کن
    $insert_cols = [];
    $insert_vals = [];
    $bind_types = '';
    $bind_vals = [];
    foreach($db_columns as $col){
        if(array_key_exists($col, $row)){
            $insert_cols[] = $col;
            $insert_vals[] = '?';
            $bind_types .= 's';
            $bind_vals[] = $row[$col];
        }
    }
    // اضافه کردن batch_id به عنوان raw_import_batch اگر در فایل نیست
    if(!in_array('raw_import_batch', $insert_cols)){
        $insert_cols[] = 'raw_import_batch';
        $insert_vals[] = '?';
        $bind_types .= 's';
        $bind_vals[] = $batch;
    }

    $sql_insert = "INSERT INTO tac_source_raw (" . implode(',', $insert_cols) . ")
                   VALUES (" . implode(',', $insert_vals) . ")";
    $stmt_insert = $m->prepare($sql_insert);
    $stmt_insert->bind_param($bind_types, ...$bind_vals);
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
?>