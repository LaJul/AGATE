<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\Service\TiebreakInterface;
use App\Entity\Tournament;
use App\Entity\Player;

/**
 * Description of BlackGamesTiebreak
 *
 * @author Aspom
 */
class DirectEncounterTiebreak implements TiebreakInterface
{
    private $tournament;
    
    public function __construct(Tournament $tournament) {
        $this->tournament = $tournament;
    }
    
    public function setCriteria(Player $player) {
        return 0;
    }
    
    public function compare(Player $player1, Player $player2) {
       
        $game = $this->tournament->getGames()->filter(function($game) {
            return ($game->getWhite() == $player1 && $game->getBlack() == $player2)
            || ($game->getWhite() == $player2 && $game->getBlack() == $player1);
        });
         
        if ($game == NULL || $game->getResult() == "X-X")
        {
            return 0;
        }
        else if ($game->getResult() == "1-0" && $game->getWhite() == $player1 ||
                $game->getResult() == "0-1" && $game->getBlack() == $player1){
            return 1;
        }
        else {
            return -1;
        }
    }
}
