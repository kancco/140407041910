<?php
function jalali_to_gregorian_core($jy,$jm,$jd){
    $jy-=979;
    $days=365*$jy + intdiv($jy,33)*8 + intdiv(($jy%33+3),4);
    $m=[0,31,62,93,124,155,186,216,246,276,306,336];
    $days+=$m[$jm-1]+($jd-1);
    $gy=1600+400*intdiv($days,146097);
    $days%=146097;
    $leap=true;
    if($days>=36525){
        $days--; $gy+=100*intdiv($days,36524); $days%=36524;
        if($days>=365) $days++; else $leap=false;
    }
    $gy+=4*intdiv($days,1461); $days%=1461;
    if($days>=366){
        $leap=false;
        $days--; $gy+=intdiv($days,365); $days%=365;
    }
    $gd=$days+1;
    $gdm=[0,31,($leap?29:28),31,30,31,30,31,31,30,31,30,31];
    for($gm=1;$gm<=12;$gm++){ if($gd>$gdm[$gm]) $gd-=$gdm[$gm]; else break; }
    return [$gy,$gm,$gd];
}
function gregorian_to_jalali_core($gy,$gm,$gd){
    $g_d_m=[0,31,59,90,120,151,181,212,243,273,304,334];
    $gy2=$gy-1600; $gm2=$gm-1; $gd2=$gd-1;
    $g_day_no=365*$gy2+intdiv($gy2+3,4)-intdiv($gy2+99,100)+intdiv($gy2+399,400);
    $g_day_no+=$g_d_m[$gm2]+$gd2;
    if($gm2>1 && (($gy%4==0 && $gy%100!=0)||$gy%400==0)) $g_day_no++;
    $j_day_no=$g_day_no-79;
    $j_np=intdiv($j_day_no,12053);
    $j_day_no%=12053;
    $jy=979+33*$j_np+4*intdiv($j_day_no,1461);
    $j_day_no%=1461;
    if($j_day_no>=366){
        $jy+=intdiv($j_day_no-366,365);
        $j_day_no=($j_day_no-366)%365;
    }
    for($jm=1;$jm<=12;$jm++){
        $dim=($jm<=6)?31:(($jm<=11)?30:29);
        if($j_day_no<$dim) break;
        $j_day_no-=$dim;
    }
    $jd=$j_day_no+1;
    return [$jy,$jm,$jd];
}
function to_greg_from_jalali($j){
    if(!preg_match('/^(1[34]\d{2})\/(0?\d{1,2})\/(0?\d{1,2})$/',$j,$m)) return null;
    [$gy,$gm,$gd]=jalali_to_gregorian_core($m[1],$m[2],$m[3]);
    return sprintf('%04d-%02d-%02d',$gy,$gm,$gd);
}
function to_jalali_from_greg($g){
    if(!preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})$/',$g,$m)) return null;
    [$jy,$jm,$jd]=gregorian_to_jalali_core($m[1],$m[2],$m[3]);
    return sprintf('%04d/%02d/%02d',$jy,$jm,$jd);
}