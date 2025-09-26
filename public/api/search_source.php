<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/_db.php';
$m=db();

$q       = trim($_GET['q']??'');
$status  = trim($_GET['status']??'');
$company = trim($_GET['company_national_id']??'');
$latest  = isset($_GET['latest']) && $_GET['latest']=='1';
$revMin  = isset($_GET['review_min']) ? (int)$_GET['review_min'] : null;
$revMax  = isset($_GET['review_max']) ? (int)$_GET['review_max'] : null;

$page = max(1,(int)($_GET['page']??1));
$per  = min(100,max(1,(int)($_GET['per_page']??20)));
$off  = ($page-1)*$per;

$table = $latest ? 'v_latest_source' : 'tac_source_raw';

$where=[];$params=[];$types='';

if($q!==''){
    $where[]="(production_line_name_fa LIKE ? OR manufacturer_name_fa LIKE ? OR license_holder_name_fa LIKE ? OR source_code LIKE ?)";
    for($i=0;$i<4;$i++){ $params[]="%$q%"; $types.='s'; }
}
if($status!==''){
    $where[]="status_fa = ?";
    $params[]=$status; $types.='s';
}
if($company!==''){
    $where[]="company_national_id = ?";
    $params[]=$company; $types.='s';
}
if($revMin!==null){
    $where[]="review_duration_days >= ?";
    $params[]=$revMin; $types.='i';
}
if($revMax!==null){
    $where[]="review_duration_days <= ?";
    $params[]=$revMax; $types.='i';
}

$wSQL = $where?('WHERE '.implode(' AND ',$where)):'';

$totalSQL="SELECT COUNT(*) FROM $table $wSQL";
$st=$m->prepare($totalSQL);
if($params){
    $b=[$types]; foreach($params as $k=>$v){ $b[]=&$params[$k]; }
    call_user_func_array([$st,'bind_param'],$b);
}
$st->execute();
$total=$st->get_result()->fetch_row()[0]??0;

$dataSQL="SELECT source_code, status_fa, manufacturer_name_fa, license_holder_name_fa,
                 production_line_name_fa, review_duration_days, issue_datetime_raw, expire_datetime_raw
          FROM $table
          $wSQL
          ORDER BY id DESC
          LIMIT ? OFFSET ?";
$params2=$params; $types2=$types.'ii';
$params2[]=$per; $params2[]=$off;
$st2=$m->prepare($dataSQL);
$b2=[$types2]; foreach($params2 as $k=>$v){ $b2[]=&$params2[$k]; }
call_user_func_array([$st2,'bind_param'],$b2);
$st2->execute();
$res=$st2->get_result();
$rows=[];
while($r=$res->fetch_assoc()) $rows[]=$r;

echo json_encode([
  'page'=>$page,
  'per_page'=>$per,
  'total'=>$total,
  'latest'=>$latest?1:0,
  'rows'=>$rows
],JSON_UNESCAPED_UNICODE);