<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/_db.php';
$m=db();

$name    = trim($_GET['name']??'');
$nid     = trim($_GET['national_id']??'');
$company = trim($_GET['company_national_id']??'');
$latest  = isset($_GET['latest']) && $_GET['latest']=='1';

$page=max(1,(int)($_GET['page']??1));
$per =min(100,max(1,(int)($_GET['per_page']??20)));
$off =($page-1)*$per;

$table=$latest?'v_latest_tech':'tech_raw';

$where=[];$params=[];$types='';
if($nid!==''){ $where[]="national_id=?"; $params[]=$nid; $types.='s'; }
if($company!==''){ $where[]="company_national_id=?"; $params[]=$company; $types.='s'; }
if($name!==''){
    $where[]="(full_name_fa LIKE ? OR company_name_fa LIKE ?)";
    $params[]="%$name%"; $types.='s';
    $params[]="%$name%"; $types.='s';
}

$wSQL=$where?('WHERE '.implode(' AND ',$where)):'';

$totalSQL="SELECT COUNT(*) FROM $table $wSQL";
$st=$m->prepare($totalSQL);
if($params){
    $b=[$types]; foreach($params as $i=>$v){ $b[]=&$params[$i]; }
    call_user_func_array([$st,'bind_param'],$b);
}
$st->execute();
$total=$st->get_result()->fetch_row()[0]??0;

$sql="SELECT national_id, full_name_fa, company_name_fa, company_national_id,
             license_number, ttac_expire_raw, validity_duration_text, review_first_action_duration_text
      FROM $table
      $wSQL
      ORDER BY id DESC
      LIMIT ? OFFSET ?";
$params2=$params; $types2=$types.'ii';
$params2[]=$per; $params2[]=$off;
$st2=$m->prepare($sql);
$b2=[$types2]; foreach($params2 as $i=>$v){ $b2[]=&$params2[$i]; }
call_user_func_array([$st2,'bind_param'],$b2);
$st2->execute();
$res=$st2->get_result();
$out=[]; while($r=$res->fetch_assoc()) $out[]=$r;

echo json_encode([
  'page'=>$page,'per_page'=>$per,'total'=>$total,'latest'=>$latest?1:0,'rows'=>$out
],JSON_UNESCAPED_UNICODE);