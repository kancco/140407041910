import {toast,qs,qsa} from './ui-common.js';

const tabsMap = {
  factories: { endpoint:'search_factories.php', columns:[
    {k:'factory_name_fa',t:'نام'},
    {k:'national_id',t:'شناسه'},
    {k:'city_name',t:'شهر'},
    {k:'province_name',t:'استان'},
    {k:'manager_name_fa',t:'مدیرعامل'}
  ]},
  source: { endpoint:'search_source.php', columns:[
    {k:'source_code',t:'کد منبع'},
    {k:'status_fa',t:'وضعیت'},
    {k:'manufacturer_name_fa',t:'واحد تولیدی'},
    {k:'license_holder_name_fa',t:'صاحب پروانه'},
    {k:'review_duration_days',t:'روز بررسی'},
    {k:'issue_datetime_raw',t:'صدور'},
  ], supportsLatest:true },
  tech: { endpoint:'search_tech.php', columns:[
    {k:'national_id',t:'کد ملی'},
    {k:'full_name_fa',t:'نام'},
    {k:'company_name_fa',t:'شرکت'},
    {k:'license_number',t:'شماره پروانه'},
    {k:'ttac_expire_raw',t:'انقضا'}
  ], supportsLatest:true },
  irc: { endpoint:'search_irc.php', columns:[
    {k:'irc_code',t:'IRC'},
    {k:'status_fa',t:'وضعیت'},
    {k:'product_trade_name_fa',t:'نام تجاری'},
    {k:'license_holder_name_fa',t:'صاحب پروانه'},
    {k:'manufacturer_name_fa',t:'تولیدکننده'},
    {k:'expire_datetime_raw',t:'انقضا'}
  ], supportsLatest:true }
};

let activeDataset='factories';
let currentPage=1;

function init(){
  qsa('[data-tab]').forEach(btn=>{
    btn.addEventListener('click',()=>{
      activeDataset=btn.dataset.tab;
      currentPage=1;
      qsa('[data-tab]').forEach(b=>b.classList.remove('active'));
      btn.classList.add('active');
      renderFilters();
      clearResults();
    });
  });
  qs('#searchBtn').addEventListener('click',()=>{ currentPage=1; fetchData(); });
  qs('#perPage').addEventListener('change',()=>{ currentPage=1; fetchData(); });
  renderFilters();
}

function renderFilters(){
  const wrap=qs('#filters');
  wrap.innerHTML='';
  const common = `
    <div>
      <label>جستجو کلی</label>
      <input type="text" id="q" placeholder="متن ...">
    </div>
  `;
  wrap.insertAdjacentHTML('beforeend', common);

  if(['source','tech','irc'].includes(activeDataset)){
    wrap.insertAdjacentHTML('beforeend', `
      <div>
        <label>آخرین نسخه</label>
        <select id="latest"><option value="1">بله</option><option value="0">خیر</option></select>
      </div>
    `);
  }
  if(activeDataset==='source'){
    wrap.insertAdjacentHTML('beforeend', `
      <div>
        <label>حداقل روز بررسی</label>
        <input type="text" id="review_min">
      </div>
      <div>
        <label>حداکثر روز بررسی</label>
        <input type="text" id="review_max">
      </div>
    `);
  }
  if(activeDataset==='tech'){
    wrap.insertAdjacentHTML('beforeend', `
      <div>
        <label>کد ملی</label>
        <input type="text" id="national_id">
      </div>
    `);
  }
  if(activeDataset==='irc'){
    wrap.insertAdjacentHTML('beforeend', `
      <div>
        <label>کد IRC دقیق</label>
        <input type="text" id="irc_code">
      </div>
    `);
  }
}

function buildQuery(){
  const params=new URLSearchParams();
  const q=qs('#q')?.value?.trim();
  if(q) params.append('q',q);
  if(qs('#latest')) params.append('latest', qs('#latest').value);
  if(activeDataset==='source'){
    const mn=qs('#review_min')?.value.trim(); if(mn) params.append('review_min',mn);
    const mx=qs('#review_max')?.value.trim(); if(mx) params.append('review_max',mx);
  }
  if(activeDataset==='tech'){
    const nid=qs('#national_id')?.value.trim(); if(nid) params.append('national_id',nid);
  }
  if(activeDataset==='irc'){
    const ic=qs('#irc_code')?.value.trim(); if(ic) params.append('irc_code',ic);
  }
  params.append('page',currentPage);
  params.append('per_page',qs('#perPage').value);
  return params.toString();
}

async function fetchData(){
  const tab=tabsMap[activeDataset];
  if(!tab){ return; }
  const query=buildQuery();
  qs('#results').innerHTML='<div style="padding:20px;text-align:center;font-size:13px;color:#777">در حال دریافت...</div>';
  try{
    const res=await fetch(`../api/${tab.endpoint}?${query}`);
    const js=await res.json();
    renderTable(js, tab);
  }catch(e){
    toast('خطا در ارتباط: '+e.message,'error');
  }
}

function renderTable(data,tab){
  const cols=tab.columns;
  let html='<table class="table-clean"><thead><tr>';
  cols.forEach(c=> html+=`<th>${c.t}</th>`);
  html+='</tr></thead><tbody>';
  if(!data.rows || !data.rows.length){
    html+='<tr><td colspan="'+cols.length+'" style="text-align:center;color:#777;font-size:13px;">موردی یافت نشد</td></tr>';
  }else{
    data.rows.forEach(r=>{
      html+='<tr>';
      cols.forEach(c=>{
        let v=r[c.k];
        if(v==null || v==='') v='-';
        html+='<td>'+escapeHtml(String(v))+'</td>';
      });
      html+='</tr>';
    });
  }
  html+='</tbody></table>';
  html+=paginationBar(data.total, data.per_page, data.page);
  qs('#results').innerHTML=html;
  bindPagination();
}

function paginationBar(total, per, page){
  if(total<=per) return '';
  const pages=Math.ceil(total/per);
  let buf='<div class="pagination" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:12px;">';
  const start=Math.max(1,page-2);
  const end=Math.min(pages,page+2);
  if(page>1) buf+=`<button data-page="${page-1}" class="btn outline" style="padding:4px 12px;font-size:12px;">قبلی</button>`;
  for(let p=start;p<=end;p++){
    buf+=`<button data-page="${p}" class="btn ${p===page?'':'outline'}" style="padding:4px 12px;font-size:12px;">${p}</button>`;
  }
  if(page<pages) buf+=`<button data-page="${page+1}" class="btn outline" style="padding:4px 12px;font-size:12px;">بعدی</button>`;
  buf+='</div>';
  return buf;
}
function bindPagination(){
  qsa('.pagination button').forEach(b=>{
    b.addEventListener('click',()=>{
      currentPage=parseInt(b.dataset.page,10);
      fetchData();
    });
  });
}
function clearResults(){ qs('#results').innerHTML=''; }

function escapeHtml(s){
  return s.replace(/[&<>"']/g,c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));
}

document.addEventListener('DOMContentLoaded', init);