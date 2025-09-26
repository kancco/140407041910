/* Importer v7 – Dataset-Oriented Mapping (Persian labels only, whitelist 'active' -> 'وضعیت') */
const els = {
  ds: document.getElementById('dataset'),
  file: document.getElementById('file'),
  sheet: document.getElementById('sheetSelect'),
  batch: document.getElementById('batchId'),
  extra: document.getElementById('extraData'),
  autoMap: document.getElementById('autoMapBtn'),
  clear: document.getElementById('clearBtn'),
  start: document.getElementById('startBtn'),
  status: document.getElementById('status'),
  mapArea: document.getElementById('mappingArea'),
  unused: document.getElementById('unusedHeaders'),
  preview: document.getElementById('preview'),
  log: document.getElementById('log')
};

/* -------------------- Dataset Field Definitions -------------------- */
const FIELD_SETS = {
  factories: [
    {key:'factory_name_fa',label:'نام کارخانه',aliases:['نام شرکت','factory name'],required:true},
    {key:'national_id',label:'شناسه ملی',aliases:['کد ملی شرکت','شناسه شرکت']},
    {key:'economic_code',label:'کد اقتصادی',aliases:['economic code']},
    {key:'province_name',label:'استان',aliases:[]},
    {key:'city_name',label:'شهر',aliases:[]},
    {key:'address_fa',label:'آدرس',aliases:['نشانی','address']},
    {key:'postal_code',label:'کد پستی',aliases:['postal code','zip']},
    {key:'phone_raw',label:'تلفن',aliases:['phone','tel','شماره تماس']},
    {key:'manager_name_fa',label:'مدیرعامل',aliases:['ceo']},
    {key:'manager_national_id',label:'کدملی مدیرعامل',aliases:['manager national id']},
    {key:'company_type_fa',label:'نوع شرکت',aliases:['company type']},
    {key:'country_name_fa',label:'کشور',aliases:['country']},
    {key:'gln_code',label:'GLN',aliases:['gln']},
    {key:'email',label:'ایمیل',aliases:['email']},
  ],
  source: [
    {key:'source_code',label:'کد منبع',aliases:['شماره پروانه بهره برداری'],required:true},
    {key:'file_number',label:'شماره پرونده',aliases:[]},
    {key:'university_name_fa',label:'نام دانشگاه',aliases:[]},
    {key:'deputy_name_fa',label:'نام معاونت',aliases:[]},
    {key:'domain_fa',label:'حوزه',aliases:[]},
    {key:'license_holder_name_fa',label:'نام صاحب پروانه',aliases:[]},
    {key:'manufacturer_name_fa',label:'نام واحد تولیدی',aliases:[]},
    {key:'company_national_id',label:'شناسه ملی واحد تولیدی',aliases:[]},
    {key:'national_id',label:'شناسه ملی مرتبط',aliases:['شناسه ملی (عمومی / مرتبط)']},
    {key:'group_category',label:'گروه مرتبط با منبع',aliases:[]},
    {key:'manufacturing_industry_type',label:'نوع صنعت تولیدی',aliases:[]},
    {key:'product_type_fa',label:'نوع فرآورده',aliases:[]},
    {key:'license_type_fa',label:'نوع پروانه',aliases:[]},
    {key:'license_amend_reason_fa',label:'علت اصلاح پروانه',aliases:[]},
    {key:'status_fa',label:'وضعیت',aliases:[]},
    {key:'license_issue_note_fa',label:'صدور پروانه',aliases:[]},
    {key:'production_line_name_fa',label:'نام خط تولید',aliases:[]},
    {key:'production_lines_list_fa',label:'خطوط تولیدی (لیست)',aliases:['خطوط تولیدی']},
    {key:'production_line_type',label:'نوع خط تولید',aliases:[]},
    {key:'line_group_category',label:'گروه خط',aliases:['گروه و دسته مرتبط با خط']},
    {key:'production_site_type',label:'نوع سایت تولیدی',aliases:[]},
    {key:'site_activity_type',label:'نوع فعالیت‌های سایت',aliases:[]},
    {key:'production_site_name_fa',label:'نام سایت تولیدی',aliases:[]},
    {key:'production_site_country_fa',label:'کشور سایت تولیدی',aliases:[]},
    {key:'branch_name_fa',label:'نام شعبه',aliases:[]},
    {key:'branch_type',label:'نوع شعبه',aliases:[]},
    {key:'city_name',label:'شهر',aliases:[]},
    {key:'created_datetime_raw',label:'تاریخ ایجاد رکورد',aliases:[]},
    {key:'request_register_datetime_raw',label:'تاریخ ثبت درخواست',aliases:[]},
    {key:'final_fix_datetime_raw',label:'تاریخ رفع نقص نهایی',aliases:[]},
    {key:'technical_committee_datetime_raw',label:'تاریخ کمیته فنی',aliases:['تاریخ کمیته']},
    {key:'issue_datetime_raw',label:'تاریخ صدور',aliases:[]},
    {key:'expire_datetime_raw',label:'تاریخ انقضا',aliases:[]},
    {key:'validity_duration_text',label:'مدت اعتبار',aliases:[]}
  ],
  tech: [
    {key:'full_name_fa',label:'نام و نام خانوادگی',aliases:['نام مسئول فنی'],required:true},
    {key:'national_id',label:'کد ملی',aliases:['کدملی','شناسه ملی']},
    {key:'father_name_fa',label:'نام پدر',aliases:[]},
    {key:'gender_fa',label:'جنسیت',aliases:[]},
    {key:'birth_place_fa',label:'محل تولد',aliases:[]},
    {key:'birth_certificate_place_fa',label:'محل صدور',aliases:[]},
    {key:'birth_certificate_no',label:'شماره شناسنامه',aliases:[]},
    {key:'birth_date_raw',label:'تاریخ تولد',aliases:[]},
    {key:'mobile_phone',label:'شماره همراه',aliases:['موبایل','شماره تلفن همراه']},
    {key:'phone_landline',label:'تلفن ثابت',aliases:['شماره تلفن ثابت']},
    {key:'email',label:'ایمیل',aliases:['پست الکترونیک']},
    {key:'degree_fa',label:'مدرک تحصیلی',aliases:[]},
    {key:'major_fa',label:'رشته تحصیلی',aliases:[]},
    {key:'specialty_fa',label:'گرایش تحصیلی',aliases:[]},
    {key:'license_number',label:'شماره پروانه',aliases:[]},
    {key:'license_start_date_raw',label:'تاریخ شروع پروانه',aliases:[]},
    {key:'ttac_expire_raw',label:'تاریخ اعتبار پروانه',aliases:[]},
    {key:'has_physical_card',label:'کارت فیزیکی',aliases:[]},
    {key:'request_register_datetime_raw',label:'تاریخ ثبت درخواست',aliases:[]},
    {key:'final_fix_datetime_raw',label:'تاریخ رفع نقص نهایی',aliases:[]},
    {key:'committee_letter_number',label:'شماره نامه کمیته',aliases:[]},
    {key:'committee_letter_datetime_raw',label:'تاریخ نامه کمیته',aliases:[]},
    {key:'physical_card_letter_number',label:'شماره نامه کارت فیزیکی',aliases:[]},
    {key:'physical_card_letter_datetime_raw',label:'تاریخ نامه کارت فیزیکی',aliases:[]},
    {key:'shift_name',label:'شیفت کاری',aliases:[]},
    {key:'company_name_fa',label:'نام شرکت منتصب',aliases:['نام شرکت']},
    {key:'company_national_id',label:'شناسه ملی شرکت منتصب',aliases:['شناسه ملی شرکت','کد ملی شرکت']},
    {key:'domain_fa',label:'حوزه',aliases:[]},
    {key:'source_licenses_fa',label:'پروانه‌های ثبت منبع',aliases:['پروانه های ثبت منبع']},
    {key:'supervised_lines_fa',label:'خطوط تحت نظارت',aliases:[]},
    {key:'process_type_fa',label:'نوع فرآیند',aliases:[]},
    {key:'serial_number_fa',label:'س. شماره',aliases:['س شماره']},
    {key:'serial_date_raw',label:'س. تاریخ',aliases:['س تاریخ']},
    {key:'validity_duration_text',label:'مدت اعتبار',aliases:[]},
    {key:'review_first_action_duration_text',label:'مدت زمان بررسی',aliases:['مدت زمان بررسی و اولین اقدام']}
  ],
  irc: [
    {key:'irc_code',label:'IRC',aliases:[],required:true},
    {key:'status_fa',label:'وضعیت',aliases:[]},
    {key:'product_trade_name_fa',label:'نام تجاری',aliases:['نام تجاری فرآورده']},
    {key:'product_generic_name_fa',label:'نام عمومی',aliases:['نام عمومی فرآورده - نام فارسی']},
    {key:'issue_datetime_raw',label:'تاریخ صدور IRC',aliases:[]},
    {key:'first_issue_datetime_raw',label:'اولین تاریخ صدور IRC',aliases:[]},
    {key:'expire_datetime_raw',label:'تاریخ انقضای IRC',aliases:[]},
    {key:'mother_license_code',label:'کد پروانه مادری',aliases:[]},
    {key:'license_holder_name_fa',label:'صاحب پروانه',aliases:['صاحب پروانه - نام فارسی']},
    {key:'manufacturer_name_fa',label:'تولید کننده',aliases:['شرکت تولید کننده - نام فارسی']},
    {key:'manufacturer_country_fa',label:'کشور تولیدکننده',aliases:['شرکت تولید کننده - نام کشور']},
    {key:'trade_owner_name_fa',label:'صاحب نام تجاری',aliases:['صاحب نام تجاری - نام']},
    {key:'trade_owner_country_fa',label:'کشور صاحب نام تجاری',aliases:['صاحب نام تجاری - کشور']},
    {key:'national_id_license',label:'شناسه ملی صاحب پروانه',aliases:['شناسه ملی شرکت صاحب پروانه']},
    {key:'beneficiary_company_name_fa',label:'شرکت ذینفع',aliases:['شرکت ذینفع - نام']},
    {key:'beneficiary_company_country_fa',label:'کشور شرکت ذینفع',aliases:['شرکت ذینفع - کشور']},
    {key:'domain_fa',label:'حوزه',aliases:[]},
    {key:'category_group_code',label:'شناسه گروه دسته',aliases:[]},
    {key:'gtin',label:'GTIN',aliases:[]},
    {key:'unit_type_fa',label:'نوع واحد',aliases:[]},
    {key:'file_number',label:'شماره پرونده',aliases:[]},
    {key:'sent_datetime_raw',label:'تاریخ ارسال',aliases:[]},
    {key:'final_fix_datetime_raw',label:'تاریخ رفع نقص نهایی',aliases:[]},
    {key:'committee_datetime_raw',label:'تاریخ کمیته',aliases:[]},
    {key:'committee_letter_number',label:'شماره نامه کمیته',aliases:[]},
    {key:'committee_letter_datetime_raw',label:'تاریخ نامه کمیته',aliases:[]}
  ],
  whitelist: [
    {key:'company_national_id',label:'شناسه ملی شرکت',aliases:['شناسه ملی','کد ملی شرکت'],required:true},
    {key:'company_name_fa',label:'نام شرکت',aliases:['نام کارخانه','factory name'],required:true},
    /* تغییر: برچسب فعال -> وضعیت */
    {key:'active',label:'وضعیت',aliases:['active','فعال']},
    {key:'city_name',label:'شهر',aliases:[]},
    {key:'manufacturing_industry_type',label:'نوع صنعت تولیدی',aliases:[]},
    {key:'production_site_type',label:'نوع سایت تولیدی',aliases:[]}
  ]
};

/* -------------------- State -------------------- */
let workbook=null;
let headers=[];
let rawRows=[];
let mapping={};
let manualLock={};

/* Logging */
function log(m){ els.log.textContent+=m+"\n"; els.log.scrollTop=els.log.scrollHeight; }
function clearLog(){ els.log.textContent=''; }
els.clear.addEventListener('click',clearLog);

/* Helpers */
function sanitizeHeader(h){
  if(h==null)return '';
  let s=String(h);
  s=s.replace(/[\u200c\u200d\u200e\u200f\uFEFF]/g,'');
  s=s.replace(/[ي]/g,'ی').replace(/[ك]/g,'ک');
  s=s.replace(/\s+/g,' ').trim();
  return s;
}
function normKey(s){ return sanitizeHeader(s).toLowerCase(); }

/* File handling */
els.file.addEventListener('change',async ()=>{
  resetAfterFile();
  const f=els.file.files[0];
  if(!f) return;
  const name=f.name.toLowerCase();
  try{
    if(name.endsWith('.csv')){
      const txt=await f.text(); parseCSV(txt);
      els.sheet.disabled=true; els.sheet.innerHTML='<option>(csv)</option>';
    }else if(name.endsWith('.xlsx')||name.endsWith('.xls')){
      const buf=await f.arrayBuffer();
      workbook=XLSX.read(buf,{type:'array',cellDates:false});
      buildSheetSelect(); autoPickSheet(); loadSheet(els.sheet.value);
    }else log('فرمت پشتیبانی نمی‌شود.');
  }catch(e){ log('خطا: '+e.message); }
  buildDatasetMappingUI(); refreshUnusedHeaders();
});
function resetAfterFile(){
  workbook=null; headers=[]; rawRows=[];
  mapping={}; manualLock={};
  els.mapArea.innerHTML=''; buildPreview();
  els.sheet.innerHTML=''; els.sheet.disabled=true;
  els.unused.textContent='';
}
function buildSheetSelect(){
  els.sheet.disabled=false; els.sheet.innerHTML='';
  workbook.SheetNames.forEach(sn=>{
    const o=document.createElement('option'); o.value=sn; o.textContent=sn;
    els.sheet.appendChild(o);
  });
  els.sheet.addEventListener('change',()=>{
    loadSheet(els.sheet.value); buildDatasetMappingUI(); refreshUnusedHeaders();
  });
}
function autoPickSheet(){
  let best=null;
  workbook.SheetNames.forEach(sn=>{
    const ws=workbook.Sheets[sn];
    const mat=XLSX.utils.sheet_to_json(ws,{header:1,defval:''});
    if(!mat.length) return;
    const hdr=mat[0];
    const non=hdr.filter(c=>String(c).trim()!=='').length;
    const dataRows=mat.slice(1).filter(r=>r.some(c=>String(c).trim()!=='')).length;
    const score=non*2+dataRows;
    if(!best||score>best.score) best={name:sn,score};
  });
  els.sheet.value=best?best.name:workbook.SheetNames[0];
}
function detectHeaderRow(rows){
  for(let i=0;i<rows.length;i++){
    const non=rows[i].filter(c=>String(c).trim()!=='').length;
    if(non>=2) return i;
  }
  return 0;
}
function loadSheet(sn){
  const ws=workbook.Sheets[sn];
  const mat=XLSX.utils.sheet_to_json(ws,{header:1,defval:''});
  if(!mat.length){ headers=[]; rawRows=[]; buildPreview(); return; }
  const hi=detectHeaderRow(mat);
  headers=mat[hi].map(h=>sanitizeHeader(h));
  rawRows=mat.slice(hi+1)
    .filter(r=>r.some(c=>String(c).trim()!==''))
    .map(r=>{
      const o={}; headers.forEach((h,i)=>o[h]=sanitizeHeader(r[i]??'')); return o;
    });
  log(`Sheet=${sn} | HeaderRow=${hi+1} | Rows=${rawRows.length} | Cols=${headers.length}`);
  buildPreview();
}
function parseCSV(txt){
  let sep=','; if((txt.match(/;/g)||[]).length>(txt.match(/,/g)||[]).length) sep=';';
  const lines=txt.replace(/\r/g,'').split('\n').filter(l=>l.trim()!=='');
  if(!lines.length){ headers=[]; rawRows=[]; return; }
  const all=lines.map(l=>l.split(sep));
  let hi=0; for(let i=0;i<all.length;i++){ if(all[i].filter(c=>c.trim()!=='').length>=2){hi=i;break;} }
  headers=all[hi].map(h=>sanitizeHeader(h));
  rawRows=all.slice(hi+1).filter(r=>r.some(c=>c.trim()!==''))
    .map(r=>{ const o={}; headers.forEach((h,i)=>o[h]=sanitizeHeader(r[i]??'')); return o; });
  log(`CSV | HeaderRow=${hi+1} | Rows=${rawRows.length} | Cols=${headers.length}`);
  buildPreview();
}
function buildPreview(){
  const t=els.preview;
  t.tHead.innerHTML=''; t.tBodies[0]?.remove();
  const thead=t.createTHead(); const hr=thead.insertRow();
  headers.forEach(h=>{ const th=document.createElement('th'); th.textContent=h||'(blank)'; hr.appendChild(th); });
  const tb=t.createTBody();
  rawRows.slice(0,10).forEach(r=>{
    const tr=tb.insertRow();
    headers.forEach(h=>{
      const td=tr.insertCell(); td.textContent=r[h]; td.title=r[h];
    });
  });
}

/* Dataset change */
els.ds.addEventListener('change',()=>{
  mapping={}; manualLock={};
  buildDatasetMappingUI(); refreshUnusedHeaders();
});

/* Build mapping UI */
function buildDatasetMappingUI(){
  const ds=els.ds.value;
  const fields=FIELD_SETS[ds]||[];
  els.mapArea.innerHTML='';
  fields.forEach(f=>{
    if(mapping[f.key]===undefined) mapping[f.key]='';
    const row=document.createElement('div');
    row.style.cssText='display:flex;align-items:center;gap:8px;margin-bottom:6px;';
    const label=document.createElement('div');
    label.style.flex='0 0 210px'; label.style.fontSize='13px';
    label.innerHTML=`<strong>${f.label}${f.required?'<span style="color:#dc2626"> *</span>':''}</strong>`;
    label.title=f.key;
    const sel=document.createElement('select');
    sel.style.flex='1'; sel.dataset.fieldKey=f.key;
    sel.innerHTML='';
    const opt0=document.createElement('option'); opt0.value=''; opt0.textContent='(انتخاب ستون)';
    const optIgnore=document.createElement('option'); optIgnore.value='__IGNORE__'; optIgnore.textContent='(نادیده)';
    sel.appendChild(opt0); sel.appendChild(optIgnore);
    headers.forEach(h=>{ const o=document.createElement('option'); o.value=h; o.textContent=h; sel.appendChild(o); });
    if(mapping[f.key]) sel.value=mapping[f.key];
    sel.addEventListener('change',()=>{
      const k=sel.dataset.fieldKey;
      const v=sel.value;
      mapping[k]=(v===''||v==='__IGNORE__')?'':v;
      manualLock[k]=true;
      refreshUnusedHeaders();
    });
    row.appendChild(label);
    row.appendChild(sel);
    els.mapArea.appendChild(row);
  });
}

/* Auto-map */
els.autoMap.addEventListener('click',()=>{
  const ds=els.ds.value;
  const fields=FIELD_SETS[ds]||[];
  const headNorm={}; headers.forEach(h=>headNorm[normKey(h)]=h);
  fields.forEach(f=>{
    if(manualLock[f.key]) return;
    if(mapping[f.key]) return;
    const cand=[f.key,f.label].concat(f.aliases||[]);
    for(const c of cand){
      const nk=normKey(c);
      if(headNorm[nk]){ mapping[f.key]=headNorm[nk]; break; }
    }
  });
  [...els.mapArea.querySelectorAll('select')].forEach(sel=>{
    const k=sel.dataset.fieldKey;
    if(mapping[k]) sel.value=mapping[k];
  });
  refreshUnusedHeaders();
  log('Auto-map: '+Object.values(mapping).filter(v=>v).length+' فیلد مپ شد.');
});

/* Unused headers */
function refreshUnusedHeaders(){
  const used=new Set(Object.values(mapping).filter(v=>v));
  const unused=headers.filter(h=>!used.has(h));
  els.unused.textContent=unused.length?unused.join(' , '):'(ستونی بدون استفاده نداریم یا هنوز مپ نشده)';
}

/* ارسال */
els.start.addEventListener('click',()=>{
  if(!rawRows.length){ alert('فایل بارگذاری نشده'); return; }
  const ds=els.ds.value;
  const fields=FIELD_SETS[ds]||[];
  const missing=fields.filter(f=>f.required && !mapping[f.key]);
  if(missing.length){
    alert('فیلدهای اجباری بدون ستون: '+missing.map(f=>f.label).join(', '));
    return;
  }
  const prepared=rawRows.map(r=>{
    const o={};
    fields.forEach(f=>{
      const src=mapping[f.key];
      if(src) o[f.key]=r[src]??'';
    });
    if(els.extra.value.trim()){
      try{o.extra_data=JSON.parse(els.extra.value);}
      catch(e){o.extra_data=els.extra.value.trim();}
    }
    return o;
  });
  sendInChunks(ds,prepared);
});
async function sendInChunks(dataset,rows){
  const API={
    factories:'import_factories.php',
    source:'import_source.php',
    tech:'import_tech.php',
    irc:'import_irc.php',
    whitelist:'import_whitelist.php'
  };
  const endpoint=API[dataset];
  if(!endpoint){ log('Endpoint ناشناخته برای '+dataset); return; }
  const batch=els.batch.value.trim()||Date.now();
  const CHUNK=1000;
  log(`ارسال ${rows.length} رکورد ...`);
  for(let i=0;i<rows.length;i+=CHUNK){
    els.status.textContent=`${i+1}-${Math.min(i+CHUNK,rows.length)}/${rows.length}`;
    const slice=rows.slice(i,i+CHUNK);
    try{
      const res=await fetch('../api/'+endpoint,{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({batch_id:batch,rows:slice})
      });
      const js=await res.json();
      log('Chunk '+(i/CHUNK+1)+': '+JSON.stringify(js));
    }catch(e){
      log('خطا در ارسال: '+e.message);
    }
  }
  els.status.textContent='پایان';
  log('تمام شد.');
}