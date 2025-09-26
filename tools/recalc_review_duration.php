<?php
require_once __DIR__.'/../api/db.php';
require_once __DIR__.'/../api/_review_duration.php';
$res=$mysqli->query("SELECT id, final_fix_datetime_raw, technical_committee_datetime_raw FROM tac_source_raw");
$u=$mysqli->prepare("UPDATE tac_source_raw SET review_duration_days=?, review_duration_text=?, review_duration_anomaly=? WHERE id=?");
$c=0;
while($r=$res->fetch_assoc()){
  $rd=compute_review_duration_safe($r['final_fix_datetime_raw'],$r['technical_committee_datetime_raw']);
  $u->bind_param('isii',$rd['days'],$rd['text'],$rd['anomaly'],$r['id']); $u->execute(); $c++;
}
echo "Recalculated: $c\n";