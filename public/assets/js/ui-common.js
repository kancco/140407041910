(function(){
  try{
    if(CSS.supports('font-variation-settings','normal')) document.documentElement.classList.add('vf-on');
  }catch(e){}
})();
export function toast(msg, type='info', timeout=4000){
  let c=document.querySelector('.toast-container');
  if(!c){
    c=document.createElement('div');
    c.className='toast-container';
    document.body.appendChild(c);
  }
  const el=document.createElement('div');
  el.className='toast';
  if(type==='error') el.style.borderColor='#dc2626';
  if(type==='success') el.style.borderColor='#059669';
  el.textContent=msg;
  c.appendChild(el);
  setTimeout(()=>{ el.style.opacity='0'; el.style.transform='translateY(4px)'; setTimeout(()=>el.remove(),350); }, timeout);
}
export function qs(sel,root=document){ return root.querySelector(sel); }
export function qsa(sel,root=document){ return [...root.querySelectorAll(sel)]; }