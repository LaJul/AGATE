<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\Service\TiebreakInterface;

/**
 * Description of PerformanceTiebreak
 *
 * @author Aspom
 */
class CumulativeTiebreak implements TiebreakInterface {

    private $tournament;
    
    public function __construct(Tournament $tournament) {
        $this->tournament = $tournament;
    }
    
    public function setCriteria(Player $player) {
        
        $cumulative = 0;
        
        foreach ($this->tournament->getRounds() as $round) {
            $cumulative += $player->getRoundPoints($round);
        }

        return $cumulative;
    }

    public function compare($player1, $player2) {

        return $player1->getRating() - $player2->getRating();
    }
}
