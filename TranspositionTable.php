<?php

const TT_FLAG_EXACT = 1;
const TT_FLAG_LOWERBOUND = 2;
const TT_FLAG_UPPERBOUND = 3;

class TT_ENTRY{
    public $flag;
    public $depth;
    public $value;
}

$tt_map = [];

function InitTranspositionTable(){
    global $tt_map;
    InitZobristHashTbl();
    $tt_map = [];
}

function ResetTranspositionTable(){
    global $tt_map;
    $tt_map = [];
}

function LookupTranspositionTable($hash, &$ttEntry){
    global $tt_map;
    if(isset($tt_map[$hash])){
        $ttEntry = $tt_map[$hash];
        return true;
    }
    return false;
}

function StoreTranspositionTable($hash, $ttEntry){
    global $tt_map;
    if(isset($tt_map[$hash])){
        $old_entry = $tt_map[$hash];
        if($ttEntry->depth >= $old_entry->depth){
            $tt_map[$hash] = $ttEntry;
        }
    }else{
        $tt_map[$hash] = $ttEntry;
    }
}
