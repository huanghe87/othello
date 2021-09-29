<?php

class NegamaxAlphaBetaSearcher{

    public function __construct(){
        InitTranspositionTable();
    }

    function SearchBestPlay($state, $depth){
        $bestValue = -GAME_INF;
        $bestPos = 0;
        $tryState = $state;
        $player_id = $state->GetCurrentPlayer();
        $opp_player_id = GetPeerPlayer($player_id);
        $moves = [];
        $mc = $tryState->FindMoves($player_id, $opp_player_id, $moves);
        if($mc == 0){
            return -1;
        }
        SortMoves($moves);
        for($i = 0; $i < $mc; $i++){
            $flips = [];
            $tryState->DoPutChess($moves[$i]->em, $player_id, $flips);
            $tryState->SwitchPlayer();
            $value = -$this->NegaMax($tryState, $depth - 1, -GAME_INF, GAME_INF, $player_id);
            $tryState->UndoPutChess($moves[$i]->em, $player_id, $flips);
            $tryState->SwitchPlayer();
            if($value >= $bestValue){
                $bestValue = $value;
                $bestPos = $moves[$i]->em->cell;
            }
        }
        return $bestPos;
    }

    function EvaluateNegaMax($state, $max_player_id){
        if($state->GetCurrentPlayer() == $max_player_id){
            return $state->Evaluate($max_player_id);
        }else{
            return -$state->Evaluate($max_player_id);
        }
    }

    function NegaMax($state, $depth, $alpha, $beta, $max_player_id){
        $alphaOrig = $alpha;
        $state_hash = $state->GetZobristHash();
        //查询置换表
        $ttEntry = new TT_ENTRY();
        if(LookupTranspositionTable($state_hash, $ttEntry) && ($ttEntry->depth >= $depth)){
            if($ttEntry->flag == TT_FLAG_EXACT){
                return $ttEntry->value;
            }else if($ttEntry->flag == TT_FLAG_LOWERBOUND){
                $alpha = max($alpha, $ttEntry->value);
            }else{
                $beta = min($beta, $ttEntry->value);
            }
            if($beta <= $alpha){
                return $ttEntry->value;
            }
        }
        if($state->IsGameOver() || ($depth == 0)){
            return $this->EvaluateNegaMax($state, $max_player_id);
        }
        $score = -GAME_INF;
        $player_id = $state->GetCurrentPlayer();
        $opp_player_id = GetPeerPlayer($player_id);
        $moves = [];
        $mc = $state->FindMoves($player_id, $opp_player_id, $moves);
        if($mc > 0){
            SortMoves($moves);
            $flips = [];
            for($i = 0; $i < $mc; $i++){
                $state->DoPutChess($moves[$i]->em, $player_id, $flips);
                $state->SwitchPlayer();
                $value = -$this->NegaMax($state, $depth - 1, -$beta, -$alpha, $max_player_id);
                $state->UndoPutChess($moves[$i]->em, $player_id, $flips);
                $state->SwitchPlayer();
                $score = max($score, $value);
                $alpha = max($alpha, $value);
                if($beta <= $alpha){
                    break;
                }
            }
        }else{
            $state->SwitchPlayer();
            $score = -$this->NegaMax($state, $depth - 1, -$beta, -$alpha, $max_player_id);
            $state->SwitchPlayer();
        }
        //写入置换表
        $ttEntry->value = $score;
        if($score <= $alphaOrig){
            $ttEntry->flag = TT_FLAG_UPPERBOUND;
        }else if($score >= $beta){
            $ttEntry->flag = TT_FLAG_LOWERBOUND;
        }else{
            $ttEntry->flag = TT_FLAG_EXACT;
        }
        $ttEntry->depth = $depth;
        StoreTranspositionTable($state_hash, $ttEntry);
        return $score;
    }

}