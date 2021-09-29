<?php

class ZOBRIST_HASH
{
    public $key = [];
}

$zobristHashInit = false;

$zob_hash = new ZOBRIST_HASH();

function InitZobristHashTbl(){
    global $zobristHashInit, $zob_hash;
    for($i = 0; $i < GAME_CELLS; $i++)
    {
        for($j = 0; $j < PLAYER_TYPE; $j++)
        {
            $zob_hash->key[$i][$j] = mt_rand (0, 0xffffffff);
        }
    }
    $zobristHashInit = true;
}

function GetZobristHashTbl(){
    global $zob_hash;
    return $zob_hash;
}
