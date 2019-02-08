<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\Service\TiebreakInterface;
use App\Entity\Player;

/**
 * Description of PerformanceTiebreak
 *
 * @author Aspom
 */
class PerformanceTiebreak implements TiebreakInterface {
    
    public static $D = array(
        0 =>   array(13 => -736, 12 => -736, 11 => -736, 10 => -736, 9 => -736, 8 => -736, 7 => -736, 6 => -736, 5 => -736, 4 => -736),
        0.5 => array(13 => -538, 12 => -501, 11 => -501, 10 => -470, 9 => -470, 8 => -444, 7 => -422, 6 => -401, 5 => -366, 4 => -366),
        1 =>   array(13 => -422, 12 => -401, 11 => -383, 10 => -366, 9 => -351, 8 => -336, 7 => -309, 6 => -284, 5 => -240, 4 => -193),
        1.5 => array(13 => -351, 12 => -336, 11 => -322, 10 => -366, 9 => -351, 8 => -336, 7 => -309, 6 => -284, 5 => -240, 4 => -193),
        2 =>   array(13 => -296, 12 => -284, 11 => -262, 10 => -240, 9 => -220, 8 => -193, 7 => -166, 6 => -125, 5 => -72, 4 => 0),
        2.5 => array(13 => -251, 12 => -240, 11 => -220, 10 => -193, 9 => -175, 8 => -141, 7 => -110, 6 => -65, 5 => 0, 4 => 95),
        3 =>   array(13 => -211, 12 => -193, 11 => -175, 10 => -149, 9 => -125, 8 => -95, 7 => -57, 6 => 0, 5 => 72, 4 => 193),
        3.5 => array(13 => -184, 12 => -158, 11 => -141, 10 => -110, 9 => -87, 8 => -50, 7 => 0, 6 => 65, 5 => 149, 4 => 336),
        4 =>   array(13 => -149, 12 => -125, 11 => -102, 10 => -72, 9 => -43, 8 => 0, 7 => 57, 6 => 125, 5 => 240, 4 => 736),
        4.5 => array(13 => -117, 12 => -95, 11 => -72, 10 => -36, 9 => 0, 8 => 50, 7 => 110, 6 => 193, 5 => 366),
        5 =>   array(13 => -87, 12 => -72, 11 => -36, 10 => 0, 9 => 43, 8 => 95, 7 => 166, 6 => 284, 5 => 736),
        5.5 => array(13 => -57, 12 => -36, 11 => -0, 10 => 36, 9 => 87, 8 => 141, 7 => 230, 6 => 401),
        6 =>   array(13 => -29, 12 => 0, 11 => 36, 10 => 72, 9 => 125, 8 => 193, 7 => 309, 6 => 736),
        6.5 => array(13 => 0, 12 => 36, 11 => 72, 10 => 110, 9 => 175, 8 => 251, 7 => 422),
        7 =>   array(13 => 29, 12 => 72, 11 => 102, 10 => 149, 9 => 220, 8 => 336, 7 => 736),
        7.5 => array(13 => 57, 12 => 95, 11 => 141, 10 => 193, 9 => 284, 8 => 444),
        8 =>   array(13 => 87, 12 => 125, 11 => 175, 10 => 240, 9 => 351, 8 => 736),
        8.5 => array(13 => 117, 12 => 158, 11 => 220, 10 => 296, 9 => 470),
        9 =>   array(13 => 149, 12 => 193, 11 => 262, 10 => 366, 9 => 736),
        9.5 => array(13 => 184, 12 => 240, 11 => 322, 10 => 470),
        10 =>  array(13 => 211, 12 => 284, 11 => 383, 10 => 736),
        10.5 =>array(13 => 251, 12 => 336, 11 => 501),
        11 =>  array(13 => 296, 12 => 401, 11 => 736),
        11.5 =>array(13 => 351, 12 => 501),
        12 =>  array(13 => 422, 12 => 736),
        12.5 =>array(13 => 538),
        13 =>  array(13 => 736)
    );

    public function setCriteria(Player $player) {
        
        $ratingSum = 0;
        
        $nbGames = 0;
        
        foreach ($player->getWhiteGames() as $whiteGame) {
            // Ajouter regle 400 points
            $ratingSum += $whiteGame->getBlack()->getRating();
            $nbGames++;
        }

        foreach ($player->getBlackGames() as $blackGame) {
            $ratingSum += $blackGame->getWhite()->getRating();
            $nbGames++;
        }
        
        if ($nbGames < 4)
        {
            return 0;
        }

        return round($ratingSum / $nbGames + PerformanceTiebreak::$D[$player->getPoints()][$nbGames]);
        
    }

    public function compare($player1, $player2) {

        return $player1->getPerformance() - $player2->getPerformance();
    }

}
