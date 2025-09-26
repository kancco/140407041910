<?php
require_once __DIR__.'/../api/db.php';
function canonT($v){$v=trim(mb_strtolower((string)$v,'UTF-8'));return preg_replace('/\s+/u',' ',$v);}
$res=$mysqli->query("SELECT * FROM tech_raw");
$u=$mysqli->prepare("UPDATE tech_raw SET row_hash=? WHERE id=?");
$c=0;
while($r=$res->fetch_assoc()){
 $fields=['full_name_fa','national_id','father_name_fa','gender_fa','birth_place_fa','birth_certificate_place_fa','birth_certificate_no','birth_date_raw',
 'mobile_phone','phone_landline','email','degree_fa','major_fa','specialty_fa','license_number','license_start_date_raw','ttac_expire_raw',
 'has_physical_card','request_register_datetime_raw','final_fix_datetime_raw','committee_letter_number','committee_letter_datetime_raw',
 'physical_card_letter_number','physical_card_letter_datetime_raw','shift_name','company_name_fa','company_national_id','domain_fa',
 'source_licenses_fa','supervised_lines_fa','process_type_fa','serial_number_fa','serial_date_raw','validity_duration_text',
 'review_first_action_duration_text'];
 $buf=[]; foreach($fields as $f)$buf[]=canonT($r[$f]??'');
 $hash=md5(implode('|',$buf),true); $id=$r['id'];
 $u->bind_param('si',$hash,$id); $u->execute(); $c++;
}
echo "Rehashed tech: $c\n";