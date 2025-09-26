<?php
require_once __DIR__.'/../api/db.php';
require_once __DIR__.'/../api/_whitelist_common.php';
$res=$mysqli->query("SELECT id, company_national_id FROM factories_whitelist WHERE company_national_id IS NOT NULL");
$u=$mysqli->prepare("UPDATE factories_whitelist SET company_national_id=? WHERE id=?");
$c=0;
while($r=$res->fetch_assoc()){
  $norm=wl_normalize_id($r['company_national_id']);
  if($norm!=='' && $norm!==$r['company_national_id']){
    $id=$r['id']; $u->bind_param('si',$norm,$id); $u->execute(); $c++;
  }
}
echo "Normalized $c whitelist IDs\n";