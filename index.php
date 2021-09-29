<?php

//位置权值表
$posValue = [
    0,0,0,0,0,0,0,0,0,
    0,100,  -5,  10,   5,   5,  10,  -5, 100,
    0,-5, -45,   1,   1,   1,   1, -45,  -5,
    0,10,   1,   3,   2,   2,   3,   1,  10,
    0,5,   1,   2,   1,   1,   2,   1,   5,
    0,5,   1,   2,   1,   1,   2,   1,   5,
    0,10,   1,   3,   2,   2,   3,   1,  10,
    0,-5, -45,   1,   1,   1,   1, -45,  -5,
    0,100,  -5,  10,   5,   5,  10,  -5, 100,
    0,0,0,0,0,0,0,0,0
];

$dir_mask = [
    0,0,0,0,0,0,0,0,0,
    0,81,81,87,87,87,87,22,22,
    0,81,81,87,87,87,87,22,22,
    0,121,121,255,255,255,255,182,182,
    0,121,121,255,255,255,255,182,182,
    0,121,121,255,255,255,255,182,182,
    0,121,121,255,255,255,255,182,182,
    0,41,41,171,171,171,171,162,162,
    0,41,41,171,171,171,171,162,162,
    0,0,0,0,0,0,0,0,0,0
];

$dir_step = [
    1,//右
    -1,//左
    8,//左下
    -8,//右上
    9,//下
    -9,//上
    10,//右下
    -10,//左上
    0
];

$cell2board = [
    10,11,12,13,14,15,16,17,
    19,20,21,22,23,24,25,26,
    28,29,30,31,32,33,34,35,
    37,38,39,40,41,42,43,44,
    46,47,48,49,50,51,52,53,
    55,56,57,58,59,60,61,62,
    64,65,66,67,68,69,70,71,
    73,74,75,76,77,78,79,80
];

class EMPTY_LIST{
    public $cell = 0;
    public $pred = null;
    public $succ = null;
}

class MOVES_LIST{
    public $em = null;
    public $goodness = 0;
}

const GAME_ROW = 8;
const GAME_COL = 8;
const GAME_CELLS = 64;


const BOARD_ROW = 10;
const BOARD_COL = 9;
const BOARD_CELLS = 91;

const GAME_INF = 10000;
const DRAW = 0;

const CELL_EMPTY = '-';
const CELL_O = 'o';
const CELL_X = 'x';


const PLAYER_TYPE = 3;

const PLAYER_NULL = 0;
const PLAYER_A = 1;
const PLAYER_B = 2;
const DUMMY = 3;

function BOARD_CELL($row, $col){
    return ($col - 1) + 10 + ($row - 1) * 9;
}

function MoveGreater ($elem1, $elem2){
    return $elem1->goodness > $elem2->goodness;
}

function SortMoves($moves){
    usort($moves, 'MoveGreater');
}

function GetPeerPlayer($player_id) {
    return ($player_id == PLAYER_A) ? PLAYER_B : PLAYER_A;
}

include "ZobristHash.php";
include "TranspositionTable.php";

spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $classFile =  __DIR__.'/'.$class . '.php';
    if (is_file($classFile)) {
        require_once($classFile);
    }
    return true;
});

const SEARCH_DEPTH = 6;

//$testState = [
//    0,0,2,1,1,1,0,0,
//    2,0,0,2,1,1,0,0,
//    2,2,2,1,2,1,1,1,
//    2,2,2,1,2,2,1,1,
//    2,2,2,1,2,2,2,0,
//    2,2,2,1,2,0,0,0,
//    2,2,2,1,2,2,0,0,
//    2,0,1,1,1,0,0,0
//];

function main(){
    $nabs = new NegamaxAlphaBetaSearcher();
    $human = new HumanPlayer("huanghe");
    $computer = new ComputerPlayer("xiaogang");
    $computer->SetSearcher($nabs, SEARCH_DEPTH);
    $wzEv = new WzEvaluator();
    $init_state = new GameState();
    $init_state->InitGameState(PLAYER_A);
    $init_state->SetEvaluator($wzEv);
    $gc = new GameControl();
    $gc->InitGameState($init_state);
    $gc->SetPlayer($computer, PLAYER_A);
    $gc->SetPlayer($human, PLAYER_B);
    $gc->Run();
    return 0;
}

main();