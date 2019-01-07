<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\Entity\Player;

/**
 *
 * @author Aspom
 */
interface TiebreakInterface
{
    public function compare(Player $player1, Player $player2);
}
