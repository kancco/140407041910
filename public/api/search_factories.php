<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/_db.php';
$m=db();

$nid  = trim($_GET['national_id']??'');
$name = trim($_GET['name']??'');
$q    = trim($_GET['q']??'');

$page=max(1,(int)($_GET['page']??1));
$per =min(100,max(1,(int)($_GET['per_page']??20)));
$off =($page-1)*$per;

$where=[];$params=[];$types='';

if($nid!==''){ $where[]="national_id=?"; $params[]=$nid; $types.='s'; }
if($name!==''){ $where[]="factory_name_fa LIKE ?"; $params[]="%$name%"; $types.='s'; }
if($q!==''){
    $where[]="(factory_name_fa LIKE ? OR city_name LIKE ? OR province_name LIKE ?)";
    for($i=0;$i<3;$i++){ $params[]="%$q%"; $types.='s'; }
}

$wSQL=$where?('WHERE '.implode(' AND ',$where)):'';
$totalSQL="SELECT COUNT(*) FROM factories_raw $wSQL";
$st=$m->prepare($totalSQL);
if($params){
    $b=[$types]; foreach($params as $i=>$v){ $b[]=&$params[$i]; }
    call_user_func_array([$st,'bind_param'],$b);
}
$st->execute();
$total=$st->get_result()->fetch_row()[0]??0;

$sql="SELECT factory_name_fa,national_id,economic_code,province_name,city_name,manager_name_fa
      FROM factories_raw
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
  'page'=>$page,'per_page'=>$per,'total'=>$total,'rows'=>$out
],JSON_UNESCAPED_UNICODE);