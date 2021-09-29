<?php

class WzEvaluator{

    function Evaluate($state, $max_player_id){
        $min =  GetPeerPlayer($max_player_id);
        $empty = $state->CountEmptyCells();
        $ev = 0;
        if($empty >= 40){
            $ev += ($state->CountMobility($max_player_id) - $state->CountMobility($min)) * 7;
        }else if(($empty >= 18) && ($empty < 40)){
            $ev += ($state->CountPosValue($max_player_id) - $state->CountPosValue($min)) * 2;
            $ev += ($state->CountMobility($max_player_id) - $state->CountMobility($min)) * 7;
        }else{
            $ev += ($state->CountPosValue($max_player_id) - $state->CountPosValue($min)) * 2;
            $ev += ($state->CountMobility($max_player_id) - $state->CountMobility($min)) * 7;
            $ev += ($state->CountCell($max_player_id) - $state->CountCell($min)) * 2;
        }
        return $ev;
    }
}