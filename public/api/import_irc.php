<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/_db.php';
require_once __DIR__.'/_normalize.php';
json_fatal_guard(__DIR__.'/irc_import_fatal.log');

$raw=file_get_contents('php://input');
if($raw===false){ http_response_code(400); echo json_encode(['error'=>'no input']); exit; }
$data=json_decode($raw,true);
if(!$data || !isset($data['rows']) || !is_array($data['rows'])){
    http_response_code(400); echo json_encode(['error'=>'bad json']); exit;
}
$rows=$data['rows'];
$batch=isset($data['batch_id'])?(string)$data['batch_id']:(string)time();
if(!count($rows)){ echo json_encode(['inserted'=>0,'skipped'=>0,'errors'=>0,'batch'=>$batch]); exit; }

$m=db();

$m->query("CREATE TABLE IF NOT EXISTS irc_raw (
 id BIGINT UNSIGNED AUTO_INCREMENT,
 irc_code VARCHAR(120) NOT NULL,
 status_fa VARCHAR(120), product_trade_name_fa VARCHAR(400), product_generic_name_fa VARCHAR(400),
 issue_datetime_raw VARCHAR(40), first_issue_datetime_raw VARCHAR(40), expire_datetime_raw VARCHAR(40),
 mother_license_code VARCHAR(120), license_holder_name_fa VARCHAR(300), manufacturer_name_fa VARCHAR(300),
 manufacturer_country_fa VARCHAR(160), trade_owner_name_fa VARCHAR(300), trade_owner_country_fa VARCHAR(160),
 national_id_license VARCHAR(20), beneficiary_company_name_fa VARCHAR(300),
 beneficiary_company_country_fa VARCHAR(160), domain_fa VARCHAR(120), category_group_code VARCHAR(120),
 gtin VARCHAR(50), unit_type_fa VARCHAR(160), file_number VARCHAR(80), sent_datetime_raw VARCHAR(40),
 final_fix_datetime_raw VARCHAR(40), committee_datetime_raw VARCHAR(40), committee_letter_number VARCHAR(120),
 committee_letter_datetime_raw VARCHAR(40), row_hash BINARY(16), raw_import_batch VARCHAR(32), extra_data LONGTEXT,
 created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id),
 UNIQUE KEY uq_irc_rowhash (irc_code,row_hash),
 KEY idx_holder (license_holder_name_fa(150)),
 KEY idx_manu (manufacturer_name_fa(150)),
 KEY idx_status (status_fa),
 KEY idx_domain (domain_fa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$inserted=0; $updated=0; $skipped=0; $errors=0;
foreach($rows as $row){
    $irc_code = $row['irc_code']??null;
    if(!$irc_code){ $skipped++; continue; }
    $status_fa = $row['status_fa']??null;
    $product_trade_name_fa = $row['product_trade_name_fa']??null;
    $product_generic_name_fa = $row['product_generic_name_fa']??null;
    $issue_datetime_raw = $row['issue_datetime_raw']??null;
    $first_issue_datetime_raw = $row['first_issue_datetime_raw']??null;
    $expire_datetime_raw = $row['expire_datetime_raw']??null;
    $mother_license_code = $row['mother_license_code']??null;
    $license_holder_name_fa = $row['license_holder_name_fa']??null;
    $manufacturer_name_fa = $row['manufacturer_name_fa']??null;
    $manufacturer_country_fa = $row['manufacturer_country_fa']??null;
    $trade_owner_name_fa = $row['trade_owner_name_fa']??null;
    $trade_owner_country_fa = $row['trade_owner_country_fa']??null;
    $national_id_license = $row['national_id_license']??null;
    $beneficiary_company_name_fa = $row['beneficiary_company_name_fa']??null;
    $beneficiary_company_country_fa = $row['beneficiary_company_country_fa']??null;
    $domain_fa = $row['domain_fa']??null;
    $category_group_code = $row['category_group_code']??null;
    $gtin = $row['gtin']??null;
    $unit_type_fa = $row['unit_type_fa']??null;
    $file_number = $row['file_number']??null;
    $sent_datetime_raw = $row['sent_datetime_raw']??null;
    $final_fix_datetime_raw = $row['final_fix_datetime_raw']??null;
    $committee_datetime_raw = $row['committee_datetime_raw']??null;
    $committee_letter_number = $row['committee_letter_number']??null;
    $committee_letter_datetime_raw = $row['committee_letter_datetime_raw']??null;
    $extra_data = isset($row['extra_data']) ? (is_scalar($row['extra_data'])?$row['extra_data']:json_encode($row['extra_data'],JSON_UNESCAPED_UNICODE)) : null;

    // row_hash
    $fields = [$irc_code,$status_fa,$product_trade_name_fa,$product_generic_name_fa,$issue_datetime_raw,$first_issue_datetime_raw,
        $expire_datetime_raw,$mother_license_code,$license_holder_name_fa,$manufacturer_name_fa,$manufacturer_country_fa,
        $trade_owner_name_fa,$trade_owner_country_fa,$national_id_license,$beneficiary_company_name_fa,$beneficiary_company_country_fa,
        $domain_fa,$category_group_code,$gtin,$unit_type_fa,$file_number,$sent_datetime_raw,$final_fix_datetime_raw,
        $committee_datetime_raw,$committee_letter_number,$committee_letter_datetime_raw];
    $buf=[]; foreach($fields as $f){
      $v=trim(mb_strtolower((string)($f??''),'UTF-8'));
      $v=preg_replace('/\s+/u',' ',$v);
      $buf[]=$v;
    }
    $row_hash=md5(implode('|',$buf),true);

    $sql="INSERT INTO irc_raw(irc_code, status_fa, product_trade_name_fa, product_generic_name_fa, issue_datetime_raw, first_issue_datetime_raw, expire_datetime_raw, mother_license_code, license_holder_name_fa, manufacturer_name_fa, manufacturer_country_fa, trade_owner_name_fa, trade_owner_country_fa, national_id_license, beneficiary_company_name_fa, beneficiary_company_country_fa, domain_fa, category_group_code, gtin, unit_type_fa, file_number, sent_datetime_raw, final_fix_datetime_raw, committee_datetime_raw, committee_letter_number, committee_letter_datetime_raw, row_hash, raw_import_batch, extra_data)
          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
          ON DUPLICATE KEY UPDATE
            status_fa=VALUES(status_fa), product_trade_name_fa=VALUES(product_trade_name_fa), product_generic_name_fa=VALUES(product_generic_name_fa),
            issue_datetime_raw=VALUES(issue_datetime_raw), first_issue_datetime_raw=VALUES(first_issue_datetime_raw), expire_datetime_raw=VALUES(expire_datetime_raw),
            mother_license_code=VALUES(mother_license_code), license_holder_name_fa=VALUES(license_holder_name_fa), manufacturer_name_fa=VALUES(manufacturer_name_fa),
            manufacturer_country_fa=VALUES(manufacturer_country_fa), trade_owner_name_fa=VALUES(trade_owner_name_fa), trade_owner_country_fa=VALUES(trade_owner_country_fa),
            national_id_license=VALUES(national_id_license), beneficiary_company_name_fa=VALUES(beneficiary_company_name_fa), beneficiary_company_country_fa=VALUES(beneficiary_company_country_fa),
            domain_fa=VALUES(domain_fa), category_group_code=VALUES(category_group_code), gtin=VALUES(gtin), unit_type_fa=VALUES(unit_type_fa),
            file_number=VALUES(file_number), sent_datetime_raw=VALUES(sent_datetime_raw), final_fix_datetime_raw=VALUES(final_fix_datetime_raw),
            committee_datetime_raw=VALUES(committee_datetime_raw), committee_letter_number=VALUES(committee_letter_number),
            committee_letter_datetime_raw=VALUES(committee_letter_datetime_raw), row_hash=VALUES(row_hash), raw_import_batch=VALUES(raw_import_batch), extra_data=VALUES(extra_data)";
    $stmt=$m->prepare($sql);
    $stmt->bind_param('ssssssssssssssssssssssssssssss',
        $irc_code,$status_fa,$product_trade_name_fa,$product_generic_name_fa,$issue_datetime_raw,$first_issue_datetime_raw,
        $expire_datetime_raw,$mother_license_code,$license_holder_name_fa,$manufacturer_name_fa,$manufacturer_country_fa,
        $trade_owner_name_fa,$trade_owner_country_fa,$national_id_license,$beneficiary_company_name_fa,$beneficiary_company_country_fa,
        $domain_fa,$category_group_code,$gtin,$unit_type_fa,$file_number,$sent_datetime_raw,$final_fix_datetime_raw,
        $committee_datetime_raw,$committee_letter_number,$committee_letter_datetime_raw,$row_hash,$batch,$extra_data
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