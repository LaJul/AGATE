<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\Service\TiebreakInterface;

/**
 * Description of BlackGamesTiebreak
 *
 * @author Aspom
 */
class BlackGamesTiebreak implements TiebreakInterface
{
    public function setCriteria(Player $player)
    {
        return count($player->getBlackGames());
    }
    
    public function compare($player1, $player2) {
        return count($player1->getBlackGames()) - count($player2->getWhiteGames());
    }
}
