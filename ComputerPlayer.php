<?php
class ComputerPlayer extends Player{

    public $m_searcher = null;
    public $m_depth = 3;

    public function __construct($name){
        $this->SetPlayerName($name);
    }

    function GetNextPosition(){
        if($this->m_state == NULL){
            die('ComputerPlayer的m_state不能为null');
        }
        if($this->m_searcher == NULL){
            die('ComputerPlayer的m_searcher不能为null');
        }

        $np = $this->m_searcher->SearchBestPlay($this->m_state, $this->m_depth);
        $row = intval(($np - 10) / BOARD_COL);
        $col = ($np - 10) % BOARD_COL;

        echo "Computer play at [" . ($row + 1) . " , " . ($col + 1) . "]" .PHP_EOL;

        return $np;
    }

    function SetSearcher($searcher, $depth){
        $this->m_searcher = $searcher;
        $this->m_depth = $depth;
    }

}