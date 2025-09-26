<?php
function is_valid_person_national_id($id){
    if(!preg_match('/^\d{10}$/',$id)) return false;
    if(preg_match('/^(\d)\1{9}$/',$id)) return false;
    $sum=0;
    for($i=0;$i<9;$i++){
        $sum += (int)$id[$i]*(10-$i);
    }
    $rem=$sum%11;
    $check=(int)$id[9];
    if($rem<2) return $check===$rem;
    return $check===(11-$rem);
}