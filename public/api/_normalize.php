<?php
require_once __DIR__.'/_jalali.php';
require_once __DIR__.'/_db.php';

function wl_allowed_id_lengths(){
    return [10,11];
}
function digit_convert($s){
    static $m=['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
               '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9'];
    return strtr($s,$m);
}
function normalize_id($s){
    if($s===null) return '';
    $s=digit_convert((string)$s);
    $d=preg_replace('/\D+/','',$s);
    if($d!=='' && in_array(strlen($d),wl_allowed_id_lengths(),true)) return $d;
    return '';
}
function normalize_name($s){
    if($s===null) return '';
    $s=str_replace(["\xEF\xBB\xBF"],'',$s);
    $s=str_replace(['ي','ك','‌','‍','‏','‎','ـ'],['ی','ک','','','','',''],$s);
    $s=preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u','',$s);
    $s=str_replace(['أ','إ','آ','ٱ'],'ا',$s);
    $s=preg_replace('/\s+/u',' ',trim($s));
    return mb_strtolower($s,'UTF-8');
}
function normalize_jalali_date($raw){
    if($raw===null || trim($raw)==='') return null;
    $s=digit_convert(trim($raw));
    $s=preg_replace('/[T\s].*$/','',$s);
    $s=str_replace(['-','.'],'/',$s);
    $s=preg_replace('/\/+/','/',$s);
    if(!preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/',$s,$m)){
        if(preg_match('/(1\d{3})\D(0?\d{1,2})\D(0?\d{1,2})/',$s,$mm)) $m=$mm; else return null;
    }
    $y=(int)$m[1]; $mo=(int)$m[2]; $d=(int)$m[3];
    if($mo<1||$mo>12||$d<1||$d>31) return null;
    if($y>=1300 && $y<=1499) return sprintf('%04d/%02d/%02d',$y,$mo,$d);
    if($y>=2000 && $y<=2100){
        $jal=to_jalali_from_greg(sprintf('%04d-%02d-%02d',$y,$mo,$d));
        return $jal;
    }
    return null;
}
function normalize_row_dates(array &$row,array $keys){
    foreach($keys as $k){
        if(array_key_exists($k,$row)){
            $row[$k]=normalize_jalali_date($row[$k]);
        }
    }
}
function load_whitelist_cache(){
    static $cache=null;
    if($cache!==null) return $cache;
    $cache=['nat'=>[],'name'=>[]];
    if(!table_exists('factories_whitelist')) return $cache;
    $m=db();
    $q="SELECT company_national_id, company_name_fa FROM factories_whitelist WHERE active=1 OR active IS NULL";
    if($res=$m->query($q)){
        while($r=$res->fetch_assoc()){
            $nid=normalize_id($r['company_national_id']);
            if($nid!=='') $cache['nat'][$nid]=true;
            if($r['company_name_fa']){
                $cache['name'][normalize_name($r['company_name_fa'])]=true;
            }
        }
        $res->free();
    }
    return $cache;
}
function whitelist_match($cache,$idCandidate,array $names){
    $ni=normalize_id($idCandidate);
    if($ni!=='' && isset($cache['nat'][$ni])) return true;
    foreach($names as $nm){
        if(!$nm) continue;
        $nn=normalize_name($nm);
        if($nn!=='' && isset($cache['name'][$nn])) return true;
    }
    return false;
}