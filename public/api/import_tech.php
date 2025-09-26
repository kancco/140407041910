<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/_db.php';
require_once __DIR__.'/_normalize.php';
require_once __DIR__.'/_natid_validate.php';
json_fatal_guard(__DIR__.'/tech_import_fatal.log');

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

$m->query("CREATE TABLE IF NOT EXISTS tech_raw (
 id BIGINT UNSIGNED AUTO_INCREMENT,
 full_name_fa VARCHAR(300), national_id VARCHAR(20), father_name_fa VARCHAR(160), gender_fa VARCHAR(50),
 birth_place_fa VARCHAR(160), birth_certificate_place_fa VARCHAR(160), birth_certificate_no VARCHAR(50),
 birth_date_raw VARCHAR(40), mobile_phone VARCHAR(50), phone_landline VARCHAR(50), email VARCHAR(200),
 degree_fa VARCHAR(160), major_fa VARCHAR(160), specialty_fa VARCHAR(160), license_number VARCHAR(120),
 license_start_date_raw VARCHAR(40), ttac_expire_raw VARCHAR(40), has_physical_card TINYINT,
 request_register_datetime_raw VARCHAR(40), final_fix_datetime_raw VARCHAR(40), committee_letter_number VARCHAR(120),
 committee_letter_datetime_raw VARCHAR(40), physical_card_letter_number VARCHAR(120),
 physical_card_letter_datetime_raw VARCHAR(40), shift_name VARCHAR(80), company_name_fa VARCHAR(300),
 company_national_id VARCHAR(20), domain_fa VARCHAR(120), source_licenses_fa TEXT, supervised_lines_fa TEXT,
 process_type_fa VARCHAR(160), serial_number_fa VARCHAR(120), serial_date_raw VARCHAR(40),
 validity_duration_text VARCHAR(80), review_first_action_duration_text VARCHAR(120),
 row_hash BINARY(16), raw_import_batch VARCHAR(32), extra_data LONGTEXT,
 created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id),
 UNIQUE KEY uq_nat_rowhash (national_id,row_hash),
 KEY idx_nat (national_id),
 KEY idx_company (company_national_id),
 KEY idx_full_name (full_name_fa(120)),
 KEY idx_license (license_number),
 KEY idx_email (email),
 KEY idx_domain (domain_fa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$inserted=0; $updated=0; $skipped=0; $errors=0;
foreach($rows as $row){
    $full_name_fa = $row['full_name_fa']??null;
    $national_id  = normalize_id($row['national_id']??null);
    $father_name_fa = $row['father_name_fa']??null;
    $gender_fa = $row['gender_fa']??null;
    $birth_place_fa = $row['birth_place_fa']??null;
    $birth_certificate_place_fa = $row['birth_certificate_place_fa']??null;
    $birth_certificate_no = $row['birth_certificate_no']??null;
    $birth_date_raw = $row['birth_date_raw']??null;
    $mobile_phone = $row['mobile_phone']??null;
    $phone_landline = $row['phone_landline']??null;
    $email = $row['email']??null;
    $degree_fa = $row['degree_fa']??null;
    $major_fa = $row['major_fa']??null;
    $specialty_fa = $row['specialty_fa']??null;
    $license_number = $row['license_number']??null;
    $license_start_date_raw = $row['license_start_date_raw']??null;
    $ttac_expire_raw = $row['ttac_expire_raw']??null;
    $has_physical_card = $row['has_physical_card']??null;
    $request_register_datetime_raw = $row['request_register_datetime_raw']??null;
    $final_fix_datetime_raw = $row['final_fix_datetime_raw']??null;
    $committee_letter_number = $row['committee_letter_number']??null;
    $committee_letter_datetime_raw = $row['committee_letter_datetime_raw']??null;
    $physical_card_letter_number = $row['physical_card_letter_number']??null;
    $physical_card_letter_datetime_raw = $row['physical_card_letter_datetime_raw']??null;
    $shift_name = $row['shift_name']??null;
    $company_name_fa = $row['company_name_fa']??null;
    $company_national_id = $row['company_national_id']??null;
    $domain_fa = $row['domain_fa']??null;
    $source_licenses_fa = $row['source_licenses_fa']??null;
    $supervised_lines_fa = $row['supervised_lines_fa']??null;
    $process_type_fa = $row['process_type_fa']??null;
    $serial_number_fa = $row['serial_number_fa']??null;
    $serial_date_raw = $row['serial_date_raw']??null;
    $validity_duration_text = $row['validity_duration_text']??null;
    $review_first_action_duration_text = $row['review_first_action_duration_text']??null;
    $extra_data = isset($row['extra_data']) ? (is_scalar($row['extra_data'])?$row['extra_data']:json_encode($row['extra_data'],JSON_UNESCAPED_UNICODE)) : null;

    if($national_id!==null && strlen($national_id)===10 && !is_valid_person_national_id($national_id)){ $skipped++; continue; }

    $sql="INSERT INTO tech_raw(full_name_fa, national_id, father_name_fa, gender_fa, birth_place_fa, birth_certificate_place_fa, birth_certificate_no, birth_date_raw, mobile_phone, phone_landline, email, degree_fa, major_fa, specialty_fa, license_number, license_start_date_raw, ttac_expire_raw, has_physical_card, request_register_datetime_raw, final_fix_datetime_raw, committee_letter_number, committee_letter_datetime_raw, physical_card_letter_number, physical_card_letter_datetime_raw, shift_name, company_name_fa, company_national_id, domain_fa, source_licenses_fa, supervised_lines_fa, process_type_fa, serial_number_fa, serial_date_raw, validity_duration_text, review_first_action_duration_text, raw_import_batch, extra_data)
          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
          ON DUPLICATE KEY UPDATE
            full_name_fa=VALUES(full_name_fa), father_name_fa=VALUES(father_name_fa), gender_fa=VALUES(gender_fa),
            birth_place_fa=VALUES(birth_place_fa), birth_certificate_place_fa=VALUES(birth_certificate_place_fa), birth_certificate_no=VALUES(birth_certificate_no),
            birth_date_raw=VALUES(birth_date_raw), mobile_phone=VALUES(mobile_phone), phone_landline=VALUES(phone_landline),
            email=VALUES(email), degree_fa=VALUES(degree_fa), major_fa=VALUES(major_fa), specialty_fa=VALUES(specialty_fa), license_number=VALUES(license_number),
            license_start_date_raw=VALUES(license_start_date_raw), ttac_expire_raw=VALUES(ttac_expire_raw), has_physical_card=VALUES(has_physical_card),
            request_register_datetime_raw=VALUES(request_register_datetime_raw), final_fix_datetime_raw=VALUES(final_fix_datetime_raw),
            committee_letter_number=VALUES(committee_letter_number), committee_letter_datetime_raw=VALUES(committee_letter_datetime_raw),
            physical_card_letter_number=VALUES(physical_card_letter_number), physical_card_letter_datetime_raw=VALUES(physical_card_letter_datetime_raw),
            shift_name=VALUES(shift_name), company_name_fa=VALUES(company_name_fa), company_national_id=VALUES(company_national_id),
            domain_fa=VALUES(domain_fa), source_licenses_fa=VALUES(source_licenses_fa), supervised_lines_fa=VALUES(supervised_lines_fa),
            process_type_fa=VALUES(process_type_fa), serial_number_fa=VALUES(serial_number_fa), serial_date_raw=VALUES(serial_date_raw),
            validity_duration_text=VALUES(validity_duration_text), review_first_action_duration_text=VALUES(review_first_action_duration_text),
            raw_import_batch=VALUES(raw_import_batch), extra_data=VALUES(extra_data)";
    $stmt=$m->prepare($sql);
    $stmt->bind_param('ssssssssssssssssssssssssssssssssssssss',
      $full_name_fa,$national_id,$father_name_fa,$gender_fa,$birth_place_fa,$birth_certificate_place_fa,$birth_certificate_no,
      $birth_date_raw,$mobile_phone,$phone_landline,$email,$degree_fa,$major_fa,$specialty_fa,$license_number,
      $license_start_date_raw,$ttac_expire_raw,$has_physical_card,$request_register_datetime_raw,$final_fix_datetime_raw,
      $committee_letter_number,$committee_letter_datetime_raw,$physical_card_letter_number,$physical_card_letter_datetime_raw,
      $shift_name,$company_name_fa,$company_national_id,$domain_fa,$source_licenses_fa,$supervised_lines_fa,$process_type_fa,
      $serial_number_fa,$serial_date_raw,$validity_duration_text,$review_first_action_duration_text,$batch,$extra_data
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