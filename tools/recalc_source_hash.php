<?php
require_once __DIR__.'/../api/db.php';
function canon($v){$v=trim(mb_strtolower((string)$v,'UTF-8'));return preg_replace('/\s+/u',' ',$v);}
$res=$mysqli->query("SELECT * FROM tac_source_raw");
$u=$mysqli->prepare("UPDATE tac_source_raw SET row_hash=? WHERE id=?");
$c=0;
while($r=$res->fetch_assoc()){
 $fields=['source_code','file_number','university_name_fa','deputy_name_fa','domain_fa','license_holder_name_fa','manufacturer_name_fa',
 'company_national_id','national_id','group_category','manufacturing_industry_type','product_type_fa','license_type_fa',
 'license_amend_reason_fa','status_fa','license_issue_note_fa','production_line_name_fa','production_lines_list_fa',
 'production_line_type','line_group_category','production_site_type','site_activity_type','production_site_name_fa',
 'production_site_country_fa','branch_name_fa','branch_type','city_name','created_datetime_raw',
 'request_register_datetime_raw','final_fix_datetime_raw','technical_committee_datetime_raw','issue_datetime_raw',
 'expire_datetime_raw','validity_duration_text'];
 $buf=[];foreach($fields as $f)$buf[]=canon($r[$f]??'');
 $hash=md5(implode('|',$buf),true); $id=$r['id'];
 $u->bind_param('si',$hash,$id); $u->execute(); $c++;
}
echo "Rehashed source: $c\n";