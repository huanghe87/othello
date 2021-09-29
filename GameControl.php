<?php
class GameControl{

    public $m_gameState = null;
    public $m_players = null;

    public function __construct(){

    }

    function SetPlayer($player, $player_id){
        $player->SetPlayerId($player_id);
        $player->SetGameState($this->m_gameState);
        $this->m_players[$player_id] = $player;
    }

    function GetPlayer($player_id){
        if(isset($this->m_players[$player_id])){
            return $this->m_players[$player_id];
        }
        return null;
    }

    function InitGameState($state){
        $this->m_gameState = $state;
    }

    function Run(){
        while(!$this->m_gameState->IsGameOver()){
            $playerId = $this->m_gameState->GetCurrentPlayer();
            $currentPlayer = $this->GetPlayer($playerId);
            if($currentPlayer == NULL){
                die('GameControl的currentPlayer不能为null');
            }
            $np = $currentPlayer->GetNextPosition();
            if($np != -1){
                $this->m_gameState->PutChess($np, $playerId);
            }
            $this->m_gameState->PrintGame();
            $this->m_gameState->SwitchPlayer();
        }
        $winner = $this->m_gameState->GetWinner();
        if($winner == PLAYER_NULL){
            echo "GameOver, Draw!" .PHP_EOL;
        }else{
            $winnerPlayer = $this->GetPlayer($winner);
            echo "GameOver, " . $winnerPlayer->GetPlayerName() . " Win!" . PHP_EOL;
        }
    }

}