<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

/**
 * Description of Pairing
 *
 * @author julien
 */
class Pairing {

    private $player1;
    
    private $player2;
    
    private $mark;

    public function __construct($player1, $player2) {
        $this->player1 = $player1;
        $this->player2 = $player2;
    }
    
    public function getPlayer1() {
        return $this->player1;
    }

    public function getPlayer2() {
        return $this->player2;
    }
    
}
