<?php
class HumanPlayer extends Player {

    function __construct($name){
        $this->SetPlayerName($name);
    }

    function GetNextPosition(){
        if($this->m_state == NULL){
            die('HumanPlayer的m_state不能为null');
        }
        if(!$this->m_state->TestMoves($this->GetPlayerId())) {
            echo "you have no valid position, skip this step!".PHP_EOL;
            return -1;
        }
        $np = 0;
        while(true)
        {
            echo "Please select your position (row = 1-8,col = 1-8): ";
            $inputStr = trim(fgets(STDIN));
            $inputArr = explode(',', $inputStr);
            if(count($inputArr) != 2){
                echo "input error" . PHP_EOL;
                continue;
            }
            foreach($inputArr as $k => &$v){
                $v = intval($v);
                if(($v < 1) || ($v > 8)){
                if($k == 0){
                echo "row input error" . PHP_EOL;
                }else{
                echo "col input error" . PHP_EOL;
                }
                continue;
                }
            }
            $row = $inputArr[0];
            $col = $inputArr[1];
            $np = BOARD_CELL($row, $col);
            if( (($np >= 0) && ($np < BOARD_CELLS)) && $this->m_state->IsValidPosition($np, $this->GetPlayerId())){
                break;
            }else{
                echo "Invalid position on (" . $row . " , " . $col . ")" . PHP_EOL;
            }
        }
        return $np;
    }

}