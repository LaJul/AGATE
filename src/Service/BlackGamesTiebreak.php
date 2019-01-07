<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

/**
 * Description of BlackGamesTiebreak
 *
 * @author Aspom
 */
class BlackGamesTiebreak implements TiebreakInterface
{
    public function compare($player1, $player2) {
        return $player1->getBlackGames() - $player2->getWhiteGames();
    }
}