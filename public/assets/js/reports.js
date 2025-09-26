import {toast,qs} from './ui-common.js';

async function loadSummaries(){
  qs('#report-area').innerHTML='<div style="padding:18px;text-align:center;color:#777;font-size:13px;">در حال دریافت...</div>';
  try{
    const [src,tech,irc] = await Promise.all([
      fetch('../api/search_source.php?latest=1&per_page=5').then(r=>r.json()),
      fetch('../api/search_tech.php?latest=1&per_page=5').then(r=>r.json()),
      fetch('../api/search_irc.php?latest=1&per_page=5').then(r=>r.json())
    ]);
    qs('#report-area').innerHTML=buildSection('آخرین منبع‌ها',src,['source_code','status_fa','manufacturer_name_fa','issue_datetime_raw'])
      + buildSection('آخرین مسئولین فنی',tech,['full_name_fa','company_name_fa','license_number','ttac_expire_raw'])
      + buildSection('آخرین IRC',irc,['irc_code','product_trade_name_fa','license_holder_name_fa','expire_datetime_raw']);
  }catch(e){
    toast('خطا در گزارش: '+e.message,'error');
  }
}
function buildSection(title,data,cols){
  let html=`<div class="surface"><h3>${title}</h3>`;
  html+='<table class="table-clean"><thead><tr>';
  cols.forEach(c=> html+='<th>'+c+'</th>');
  html+='</tr></thead><tbody>';
  if(!data.rows || !data.rows.length){
    html+='<tr><td colspan="'+cols.length+'" style="text-align:center;color:#777;font-size:13px;">داده‌ای نیست</td></tr>';
  }else{
    data.rows.forEach(r=>{
      html+='<tr>';
      cols.forEach(c=>{
        html+='<td>'+(r[c]??'-')+'</td>';
      });
      html+='</tr>';
    });
  }
  html+='</tbody></table></div>';
  return html;
}
document.addEventListener('DOMContentLoaded', loadSummaries);