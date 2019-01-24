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
class PerformanceTiebreak implements TiebreakInterface {

    public function setCriteria(Player $player) {
        foreach ($player->getWhiteGames() as $whiteGame) {
            // Ajouter regle 400 points
            $ratingSum += $whiteGame->getBlack()->getRating();
        }

        foreach ($player->getBlackGames() as $blackGame) {
            $ratingSum += $blackGame->getWhite()->getRating();
        }

        $nbGames = count($player->getGames());

        return $ratingSum / $nbGames + $this->D[$this->getPoints() / $nbGames];
    }

    public function compare($player1, $player2) {

        return $player1->getPerformance() - $player2->getPerformance();
    }

}
