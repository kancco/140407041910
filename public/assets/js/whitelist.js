import {toast,qs} from './ui-common.js';

async function loadWL(){
  qs('#wl-results').innerHTML='<div style="padding:14px;font-size:13px;color:#777;">در حال دریافت...</div>';
  const q=qs('#wl-q').value.trim();
  const params=new URLSearchParams();
  params.append('per_page','50');
  if(q) params.append('q',q);
  try{
    const res=await fetch('../api/search_factories.php?'+params.toString());
    const js=await res.json();
    render(js);
  }catch(e){
    toast('خطا در دریافت وایت لیست','error');
  }
}
function render(data){
  let html='<table class="table-clean"><thead><tr>';
  ['نام','شناسه','شهر','مدیرعامل'].forEach(h=> html+='<th>'+h+'</th>');
  html+='</tr></thead><tbody>';
  if(!data.rows || !data.rows.length){
    html+='<tr><td colspan="4" style="text-align:center;color:#777;font-size:13px;">موردی یافت نشد</td></tr>';
  }else{
    data.rows.forEach(r=>{
      html+='<tr>';
      html+='<td>'+(r.factory_name_fa||'-')+'</td>';
      html+='<td>'+(r.national_id||'-')+'</td>';
      html+='<td>'+(r.city_name||'-')+'</td>';
      html+='<td>'+(r.manager_name_fa||'-')+'</td>';
      html+='</tr>';
    });
  }
  html+='</tbody></table>';
  qs('#wl-results').innerHTML=html;
}
document.addEventListener('DOMContentLoaded',()=>{
  qs('#wl-search').addEventListener('click',()=>{ loadWL(); });
  loadWL();
});