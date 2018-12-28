<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

use App\Entity\Tournament;
use App\Entity\Round;
use App\Entity\Game;

class SwissManager 
{
    public static $WHITE = 0;
    public static $BLACK = 1;
    
    public static $NO_PREF = 0;
    public static $ABS_WHITE_PREF = 1;
    public static $STRONG_WHITE_PREF = 2;
    public static $MILD_WHITE_PREF = 3;
    public static $ABS_BLACK_PREF = 4;
    public static $STRONG_BLACK_PREF = 5;
    public static $MILD_BLACK_PREF = 6;
    
    public static $WHITE_PREF = 0;
    public static $BLACK_PREF = 1;
    
    private $em;
    private $logger;
    
    /* 
     * Public methods
    */
    
    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }
    
    public function createRound(Tournament $tournament)
    {
        $currentRound = $tournament->getCurrentRound();
        
        $this->updateResults($tournament);
        
        $round = new Round($tournament, $currentRound->getNumber()+1);
        
        $tournament->setCurrentRound($round);
        
        $this->em->persist($round);
        $this->em->persist($tournament);
        $this->em->flush();
        
        return $round;
    }
    
    public function pairRound(Tournament $tournament)
    {
        $playerRepository = $this->em->getRepository('App\Entity\Player');

        $round = $tournament->getCurrentRound();
        $this->logger->info('Appariement de la ronde '.$round->getNumber());

        if ($round->getNumber() == 1)
        {
            $players = $playerRepository->findBy(['tournament' => $tournament->getId()]);
            $this->setPairingNumbers($players);
        }
        
        $players = $playerRepository->getAllActivePlayers($tournament);

        // Group players by points
        $groups = $this->getScoreGroups($players);

        // Process pairings
        $pairings = $this->processPairings($groups);

        // Create games
        $this->createGames($round, $pairings);
        
        return $round;
    }
    
    public function unpairRound(Tournament $tournament)
    {
        $games = $tournament->getCurrentRound()->getGames();
        
        foreach ($games as $game)
        {
            $this->em->remove($game);
        }
        
        $this->em->flush();
    }
  
    /* 
     * Private methods
    */
    
    private static function cmpPlayers($a, $b)
    {
        return $a->getRating() < $b->getRating();
    }
    
    private static function cmpGroups($a, $b)
    {
        return $a[0]->getPoints() < $b[0]->getPoints();
    }
    
    private static function wantWhite($player)
    {
        return $player->getColourPreference() == SwissManager::$ABS_WHITE_PREF ||
                $player->getColourPreference() == SwissManager::$STRONG_WHITE_PREF ||
                $player->getColourPreference() == SwissManager::$MILD_WHITE_PREF;
    }
    
    private static function wantBlack($player)
    {
        return $player->getColourPreference() == SwissManager::$ABS_BLACK_PREF ||
                $player->getColourPreference() == SwissManager::$STRONG_BLACK_PREF ||
                $player->getColourPreference() == SwissManager::$MILD_BLACK_PREF;
    }
    
    /**
     * @return integer
     */ 
    private function setColourPreference($player)
    {   
        $colourDifference = $player->getColourDifference();
        $colourPreference = SwissManager::$NO_PREF;

        $this->logger->info($player.' différence de couleur '.$colourDifference);

        if ($colourDifference < -1) {
            $colourPreference = SwissManager::$ABS_WHITE_PREF;
        }
        else if ($colourDifference > 1) {
            $colourPreference = SwissManager::$ABS_BLACK_PREF;
        }
        else if ($colourDifference == -1) {
            $colourPreference = SwissManager::$STRONG_WHITE_PREF;
        }
        else if ($colourDifference == 1) {
            $colourPreference = SwissManager::$STRONG_BLACK_PREF;
        }
        else if ($colourDifference == 0) {
            $gameRepository = $this->em->getRepository('App\Entity\Game');
            $lastGameColour = $gameRepository->getLastGameColour($player);
            
            if ($lastGameColour == SwissManager::$WHITE)
            {
                $colourPreference = SwissManager::$MILD_BLACK_PREF;
            }
            else if ($lastGameColour == SwissManager::$BLACK)
            {
                $colourPreference = SwissManager::$MILD_BLACK_PREF;
            }
        }
        $player->setColourPreference($colourPreference);
        $this->logger->info($player.' préférence de couleur '.$colourPreference);
    }
    
    private function setPairingNumbers($players)
    {
        usort($players, array($this, "cmpPlayers"));
            
        foreach($players as $pairingNumber => $player)
        { 
            $player->setPairingNumber($pairingNumber+1);
            $this->em->persist($player);
        }
        
        $this->em->flush();
    }
    
    private function getScoreGroups($players)
    {
        $groups = array();
        
        // Group players by points
        foreach($players as $player)
        { 
            $this->logger->info('---'.$player);
                
            $groups[number_format($player->getPoints())][] = $player;
        }

        uasort($groups, array($this, "cmpGroups"));
        $this->logger->info('Nombre de groupes '.count($groups));
        
        return $groups;
    }
    
    private function processPairings($groups)
    {
        $pairings = array();
        $n = 1;

        foreach ($groups as $points => $group)
        {
            $this->logger->info('Traitement du groupe '.$points.' pts');

            // Joker
            $x = 0;
            
            // Players with white preference
            $w = 0;
            // Players with black preference
            $b = 0;
            
            $q = floor(count($group)/2);

            if ($b > $w)
            {
                $x = $b-$q;
            }
            else
            {
                $x = $w-$q;
            }

            $S1 = array_splice($group, 0, $q);
            $S2 = $group;

            // S1 index
            $i = 0;
            //S2 index
            $j = 0;

            while ($i != count($S1))
            {
                // Player of the strong group
                $S1Player = $S1[$i];
                // Player of the weak group
                $S2Player = $S2[$j];
                
                $this->logger->info("Test de ".$S1Player->getName()." contre ".$S2Player->getName());

                $this->setColourPreference($S1Player);
                $this->setColourPreference($S2Player);
              
                if ($this->testAbsoluteCriteria($S1Player, $S2Player))
                {
                    // Keep the pairing   
                    if ($this->wantWhite($S1Player) && !$this->wantWhite($S2Player) ||
                        $this->wantBlack($S2Player) &&  !$this->wantBlack($S1Player))
                    {
                        $pairings[$n-1] = array($n, $S1Player, $S2Player);
                        $n++;
                    }
                    else if ($this->wantBlack($S1Player) && !$this->wantBlack($S2Player) ||
                        $this->wantWhite($S2Player) &&  !$this->wantWhite($S1Player))
                    {    
                        $pairings[$n-1] = array($n, $S2Player, $S1Player);
                        $n++;
                    }
                    else if ($this->wantWhite($S1Player))
                    {    
                        $pairings[$n-1] = array($n, $S1Player, $S2Player);
                        $n++;
                    }
                    else if ($this->wantBlack($S1Player))
                    {    
                        $pairings[$n-1] = array($n, $S2Player, $S1Player);
                        $n++;
                    }
                    $i++;
                    $j++;
                }
                else
                {
                    // Permutation
                    while (true)
                    {}
                }
            }
        }

        return $pairings;
    }
    
    private function createGames($round, $pairings)
    {
        // Once the pairings are validated, create the games
        foreach($pairings as $pairing)
        { 
           $game = new Game($round, $pairing[0], $pairing[1], $pairing[2]);
           $this->em->persist($game);
        }
        
        $this->em->flush();
    }
    
    private function testAbsoluteCriteria($player1, $player2)
    {
        $gameRepository = $this->em->getRepository('App\Entity\Game');

        // C1 (Two players shall not play against each other more than once)        
        if ($gameRepository->getGame($player1, $player2) != null)
        {
            $this->logger->info($player1." et ".$player2." se sont déjà joués");

            return false;
        }

        // C2 (A player who has already received a pairing-allocated bye, or has already scored a (forfeit) win
        // due to an opponent not appearing in time, shall not receive the pairing-allocated bye)
        // TO DO
        
        // C3 (Non top-scorers with the same absolute colour preference shall not meet
        $p1CP = $player1->getColourPreference();
        $p2CP = $player2->getColourPreference($player2);

        if (($p1CP == $p2CP) && (($p1CP == SwissManager::$ABS_WHITE_PREF) or($p1CP == SwissManager::$ABS_BLACK_PREF)))
        {
            $this->logger->info($player1." et ".$player2." ont les mêmes couleurs absolues");

            return false;
        }
        
        $this->logger->info("Appariement possible entre ".$player1." et ".$player2);
        
        return true;
    }
}
