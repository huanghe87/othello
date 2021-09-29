<?php

class Player{

    public $m_playerId;
    public $m_playerName;
    public $m_state;

    public function GetPlayerName() { return $this->m_playerName; }
    public function SetPlayerName($name) { $this->m_playerName = $name; }
    public function GetPlayerId() { return $this->m_playerId; }
    public function SetPlayerId($id) { $this->m_playerId = $id; }
    public function GetGameState() { return $this->m_state; }
    public function SetGameState($state) { $this->m_state = $state; }

};

