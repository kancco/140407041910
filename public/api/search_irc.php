<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/_db.php';
$m=db();

$irc_code = trim($_GET['irc_code']??'');
$status   = trim($_GET['status']??'');
$holder   = trim($_GET['holder']??'');
$manu     = trim($_GET['manufacturer']??'');
$q        = trim($_GET['q']??'');
$latest   = isset($_GET['latest']) && $_GET['latest']=='1';

$page=max(1,(int)($_GET['page']??1));
$per=min(100,max(1,(int)($_GET['per_page']??20)));
$off=($page-1)*$per;

$table=$latest?'v_latest_irc':'irc_raw';

$where=[];$params=[];$types='';
if($irc_code!==''){ $where[]="irc_code=?"; $params[]=$irc_code; $types.='s'; }
if($status!==''){ $where[]="status_fa=?"; $params[]=$status; $types.='s'; }
if($holder!==''){ $where[]="license_holder_name_fa LIKE ?"; $params[]="%$holder%"; $types.='s'; }
if($manu!==''){ $where[]="manufacturer_name_fa LIKE ?"; $params[]="%$manu%"; $types.='s'; }
if($q!==''){
    $where[]="(product_trade_name_fa LIKE ? OR product_generic_name_fa LIKE ?)";
    $params[]="%$q%"; $types.='s';
    $params[]="%$q%"; $types.='s';
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

$sql="SELECT irc_code,status_fa,product_trade_name_fa,issue_datetime_raw,expire_datetime_raw,
             license_holder_name_fa,manufacturer_name_fa
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