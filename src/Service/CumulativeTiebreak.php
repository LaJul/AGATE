<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

use App\Service\TiebreakInterface;
use App\Entity\Player;

/**
 * Description of PerformanceTiebreak
 *
 * @author Aspom
 */
class CumulativeTiebreak implements TiebreakInterface {

    private $em;
    
    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }
    
    public function setCriteria(Player $player) {
        
        $cumulative = 0;
        
        $games = $this->em->getRepository('App\Entity\Game')->getGames($player);
        
        foreach ($games as $game) {
            
            if($player == $game->getWhite())
            {
                $cumulative += $game->getWhitePoints();
            }
            else
            {
                $cumulative += $game->getBlackPoints();
            }
        }

        return $cumulative;
    }

    public function compare($player1, $player2) {

        return $player1->getRating() - $player2->getRating();
    }
}
