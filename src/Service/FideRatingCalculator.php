<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Entity\Player;

class FideRatingCalculator {
    
    private $em;
    private $logger;

    public static $D = array(
        0.99 => 677,
        0.98 => 589,
        0.97 => 538,
        0.96 => 501,
        0.95 => 470,
        0.94 => 444,
        0.93 => 422,
        0.92 => 401,
        0.91 => 383,
        0.90 => 366,
        0.89 => 351,
        0.88 => 336,
        0.87 => 322,
        0.86 => 309,
        0.85 => 296,
        0.84 => 284,
        0.83 => 273,
        0.82 => 262,
        0.81 => 251,
        0.80 => 240,
        0.79 => 230,
        0.78 => 220,
        0.77 => 211,
        0.76 => 202,
        0.75 => 193,
        0.74 => 184,
        0.73 => 175,
        0.72 => 166,
        0.71 => 158,
        0.70 => 149,
        0.69 => 141,
        0.68 => 133,
        0.67 => 125,
        0.66 => 117,
        0.65 => 110,
        0.64 => 102,
        0.63 => 95,
        0.62 => 87,
        0.61 => 80,
        0.60 => 72,
        0.59 => 65,
        0.58 => 57,
        0.57 => 50,
        0.56 => 43,
        0.55 => 36,
        0.54 => 29,
        0.53 => 21,
        0.52 => 14,
        0.51 => 7,
        0.50 => 0,
        0.49 => -7
    );
    
    /*
     * Public methods
     */

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger) {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function getPerformance(Player $player) {
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
    
    public function getDelta(Player $player) {
        
        $K = 10;
        $delta = 0;
        
        foreach ($player->getWhiteGames() as $whiteGame) {
            // Ajouter regle 400 points
            switch ($whiteGame->getResult())
            {
                case "1-0" :
                    $W = 1;
                    break;
                case "X-X" :
                    $W = 0.5;
                    break;
                case "0-1" :
                    $W = 0;
                    break;
            }
            
            $delta +=  $K * ($W - array_search($player->getRating() - $whiteGame->getBlack()->getRating(), $this->D));
        }

        foreach ($player->getBlackGames() as $blackGame) {
            
            switch ($blackGame->getResult())
            {
                case "1-0" :
                    $W = 0;
                    break;
                case "X-X" :
                    $W = 0.5;
                    break;
                case "0-1" :
                    $W = 1;
                    break;
            }
            
            $delta +=  $K * ($W - array_search($player->getRating() - $blackGame->getWhite()->getRating(), $this->D));
        }
        
        return $delta;
    }
    
    public function getFide(Player $player)
    {
        if ($player->getRatingType == "F")
        {
            return $this->getDelta($player);
        }
        else
        {
            return $this->getPerformance($player);
        }
    }
    
    
}
