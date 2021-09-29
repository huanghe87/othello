<?php

class GameState{

    public $m_Empty_s = [];
    public $m_EmHead = null;
    public $m_evaluator = null;
    public $m_playerId = null;
    public $m_board = [];

    public function __construct($state = null){
        $this->m_EmHead = new EMPTY_LIST();
        if($state){
            $this->m_evaluator = $state->m_evaluator;
            $this->m_playerId = $state->m_playerId;
            foreach($state->m_board as $k => $v){
                $this->m_board[$k] = $v;
            }
            $this->InitEmptyList();
        }
    }

    function SetCurrentPlayer($player_id){
        $this->m_playerId = $player_id;
    }

    function GetCurrentPlayer(){
        return $this->m_playerId;
    }

    function SetEvaluator($evaluator){
        $this->m_evaluator = $evaluator;
    }

    function PrintGame(){
        global $cell2board;
        echo "Current game state : " .PHP_EOL;

        echo "  ";
        for($col = 0; $col < GAME_COL; $col++){
            echo ($col + 1) . ' ';
        }
        $row = 0;
        echo PHP_EOL;
        for($i = 0; $i < GAME_CELLS; $i++) {
            if(($i % GAME_COL) == 0){
                $row++;
                echo $row . ' ';
            }
            echo $this->GetCellType($this->m_board[$cell2board[$i]]);
            echo ' ';
            if(($i % GAME_COL) == 7){
                echo PHP_EOL;
            }
        }
    }

    function InitGameState($firstPlayer){
        global $cell2board;
        for($i = 0; $i < BOARD_CELLS; $i++){
            $this->m_board[$i] = DUMMY;
        }
        for($i = 0; $i < GAME_CELLS; $i++){
            $this->m_board[$cell2board[$i]] = PLAYER_NULL;
        }
        $this->m_board[BOARD_CELL(4,4)]=PLAYER_A;
        $this->m_board[BOARD_CELL(5,4)]=PLAYER_B;
        $this->m_board[BOARD_CELL(4,5)]=PLAYER_B;
        $this->m_board[BOARD_CELL(5,5)]=PLAYER_A;
        $this->m_playerId = $firstPlayer;
        $this->InitEmptyList();
    }

    function SetGameState($state, $firstPlayer){
        global $cell2board;
        for($i = 0; $i < GAME_CELLS; $i++){
            $cell = $cell2board[$i];
            $this->m_board[$cell] = $state[$i];
        }
        $this->m_playerId = $firstPlayer;
        $this->InitEmptyList();
    }

    function Evaluate($max_player_id){
        if($this->m_evaluator == NULL){
            die('m_evaluator不能为null');
        }
        return $this->m_evaluator->Evaluate($this, $max_player_id);
    }

    function SwitchPlayer(){
        $this->m_playerId = GetPeerPlayer($this->m_playerId);
    }

    function IsGameOver(){
        if(!$this->TestMoves(PLAYER_A) && !$this->TestMoves(PLAYER_B)){
            return true;
        }
        return false;
    }

    function GetWinner(){
        $ca = $this->CountCell(PLAYER_A);
        $cb = $this->CountCell(PLAYER_B);
        if($ca == $cb){
            return PLAYER_NULL;
        }else{
            return ($ca > $cb) ? PLAYER_A : PLAYER_B;
        }
    }

    function CountCell($player_id){
        $count = 0;
        for($i = 0; $i < BOARD_CELLS; $i++) {
            if($this->m_board[$i] == $player_id){
                $count++;
            }
        }
        return $count;
    }

    function GetCellType($player_id){
        if($player_id == PLAYER_NULL){
            return CELL_EMPTY;
        }else{
            return ($player_id == PLAYER_B) ? CELL_X : CELL_O;
        }
    }

    function InitEmptyList(){
        $k = 0;
        $pt = $this->m_EmHead;
        for($i = 0; $i < BOARD_CELLS; $i++){
            if($this->m_board[$i] == PLAYER_NULL){
                $this->m_Empty_s[$k] = new EMPTY_LIST();
                $pt->succ = $this->m_Empty_s[$k];
                $this->m_Empty_s[$k]->pred = $pt;
                $pt = $pt->succ;
                $pt->cell = $i;
                $k++;
            }
        }
        $pt->succ = null;
    }

    function FindEmptyListByCell($cell){
        for($em = $this->m_EmHead->succ; $em != null; $em = $em->succ){
            if($em->cell == $cell){
                return $em;
            }
        }
        return null;
    }

    function CountPosValue($player_id){
        global $cell2board, $posValue;
        $value = 0;
        for($i = 0; $i < GAME_CELLS; $i++){
            $cell = $cell2board[$i];
            if($this->m_board[$cell] == $player_id){
                $value += $posValue[$cell];
            }
        }
        return $value;
    }

    function SingleDirFlips($cell, $dir, $player_id, $opp_player_id, &$flips){
        $pt = $cell + $dir;
        if($this->m_board[$pt] == $opp_player_id){
            while($this->m_board[$pt] == $opp_player_id){
                $pt += $dir;
            }
            if($this->m_board[$pt] == $player_id){
                $pt -= $dir;
                do{
                    $this->m_board[$pt] = $player_id;
                    $flips[] = $pt;
                    $pt -= $dir;
                }while($pt != $cell);
            }
        }
    }

    function DoFlips($cell, $player_id, $opp_player_id, &$flips){
        global $dir_mask, $dir_step;
        $flips = [];
        /*在8个方向试探*/
        for($i = 0; $i < 8; $i++){
            $mask = 0x01 << $i;
            if($dir_mask[$cell] & $mask){
                $this->SingleDirFlips($cell, $dir_step[$i], $player_id, $opp_player_id, $flips);
            }
        }
        return count($flips);
    }

    function UndoFlips(&$flips, $opp_player_id){
        foreach($flips as $it){
            $this->m_board[$it] = $opp_player_id;
        }
    }

    function CanSingleDirFlips($cell, $dir_step, $player_id, $opp_player_id){
        $pt = $cell + $dir_step; //cell是空位
        //如果不是对手的棋子，说明也是空位或己方的棋子，就不用搜索了
        if($this->m_board[$pt] == $opp_player_id){
            while($this->m_board[$pt] == $opp_player_id){
                $pt += $dir_step;
            }
            //根据棋盘模型，最后可能是己方棋子，空位或边界标志，只有是己方棋子时才能反转这一行
            return ($this->m_board[$pt] == $player_id) ? true : false;
        }
        return false;
    }

    function CanFlips($cell, $player_id, $opp_player_id){
        global $dir_mask, $dir_step;
        /*在8个方向试探，任何一个方向可以翻转对方的棋子就返回true*/
        for($i = 0; $i < 8; $i++){
            $mask = 0x01 << $i;
            if($dir_mask[$cell] & $mask){
                if($this->CanSingleDirFlips($cell, $dir_step[$i], $player_id, $opp_player_id)){
                    return true;
                }
            }
        }
        return false;
    }

    function IsValidPosition($cell, $player_id){
        $opp_player_id = GetPeerPlayer($player_id);
        $em = $this->FindEmptyListByCell($cell);
        if($em != null){
            return $this->CanFlips($cell, $player_id, $opp_player_id);
        }
        return false;
    }

    function CountMobility($player_id){
        $opp_player_id = GetPeerPlayer($player_id);
        $mobility = 0;
        for($em = $this->m_EmHead->succ; $em != null; $em = $em->succ){
            if($this->CanFlips($em->cell, $player_id, $opp_player_id)){
                $mobility++;
            }
        }
        return $mobility;
    }

    function PutChess($cell, $player_id){
        $em = $this->FindEmptyListByCell($cell);
        if($em == null){
            return 0;
        }
        $flips = [];
        return $this->DoPutChess($em, $player_id, $flips);
    }

    function DoPutChess($em, $player_id, &$flips){
        $opp_player_id = GetPeerPlayer($player_id);
        $j = $this->DoFlips($em->cell, $player_id, $opp_player_id, $flips);
        $this->m_board[$em->cell] = $player_id;
        $em->pred->succ = $em->succ;
        if($em->succ != null){
            $em->succ->pred = $em->pred;
        }
        return $j;
    }

    function UndoPutChess($em, $player_id, &$flips){
        $opp_player_id = GetPeerPlayer($player_id);
        $this->UndoFlips($flips, $opp_player_id);
        $this->m_board[$em->cell] = PLAYER_NULL;
        $em->pred->succ = $em;
        if($em->succ != null){
            $em->succ->pred = $em;
        }
    }

    function TestMoves($player_id){
        $opp_player_id = GetPeerPlayer($player_id);
        for($em = $this->m_EmHead->succ; $em != null; $em = $em->succ){
            if($this->CanFlips($em->cell, $player_id, $opp_player_id)){
                return true;
            }
        }
        return false;
    }

    function FindMoves($player_id, $opp_player_id, &$moves){
        $flips = [];
        $ml = new MOVES_LIST();
        $moves = [];
        for($em = $this->m_EmHead->succ; $em != null; $em = $em->succ){
            $cell = $em->cell;
            $flipped = $this->DoFlips($cell, $player_id, $opp_player_id, $flips);
            if($flipped > 0){
                $this->m_board[$cell] = $player_id;
                $em->pred->succ = $em->succ; //cell链表的succ链暂时跳过em（CountMobility函数会用到这个链表）
                $ml->goodness = -$this->CountMobility($opp_player_id);
                $em->pred->succ = $em; //cell链表的succ链恢复em
                $ml->em = $em;
                $this->UndoFlips($flips, $opp_player_id);
                $this->m_board[$cell] = PLAYER_NULL;
                $moves[] = $ml;
            }
        }

        return count($moves);
    }

    function CountEmptyCells(){
        $empty = 0;
        for($em = $this->m_EmHead->succ; $em != null; $em = $em->succ){
            $empty++;
        }
        return $empty;
    }

    function GetZobristHash(){
        global $cell2board;
        $zob_hash = GetZobristHashTbl();
        $hash = 0;
        for($i = 0; $i < GAME_CELLS; $i++){
            $cell = $cell2board[$i];
            $hash ^= $zob_hash->key[$i][$this->m_board[$cell]];
        }
        return $hash;
    }

}