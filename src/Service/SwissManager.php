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
        
        $this->confirmResults($tournament);
        
        $round = new Round($tournament, $currentRound->getNumber()+1);
        
        $tournament->setCurrentRound($round);
        
        $this->em->persist($round);
        $this->em->persist($tournament);
        $this->em->flush();
        
        return $round;
    }
    
    public function pairRound(Tournament $tournament, Round $round)
    {
        $playerRepository = $this->em->getRepository('App\Entity\Player');

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
    
    public function unpairRound(Round $round)
    {
        $games = $round->getGames();
        
        foreach ($games as $game)
        {
            $this->em->remove($game);
        }
        
        $this->em->flush();
    }
  
    public function confirmResults(Tournament $tournament)
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
    
     private static function cmpPairings($a, $b)
    {
       // Score of the "strongest" player
        $delta =  $a[0]->getPoints() < $b[0]->getPoints();
        
        if ($delta == 0)
        {
            // Sum of the score of the two players
            $delta =  $a[0]->getPoints() + $a[1]->getPoints() < $b[0]->getPoints() + $b[1]->getPoints();
            
            if ($delta == 0)
            {
                // Pairing number of the "strongest" player
                $delta =  $a[0]->getPairingNumber() > $b[0]->getPairingNumber();
            }
        }
        
        return $delta;
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

            uasort($group, array($this, "cmpPlayers"));

            // C2 & C3
            
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
            
            // C4 & C5
            $S1 = array_splice($group, 0, $q);
            $S2 = $group;

            $pairingsPerGroup = count($S1);
                
            while ($pairingsPerGroup > 0){
                                    
                    // S1 index
                    $i = 0;
                    // S2 index
                    $j = 0;
                
                    // Number of perturbed tables
                    $k = $x;
                    
                    while ($i != count($S1))
                    {
                        // C6

                        // Player of the strong group
                        $S1Player = $S1[$i];
                        // Player of the weak group
                        $S2Player = $S2[$j];

                        $this->logger->info("Test de ".$S1Player->getName()." contre ".$S2Player->getName());

                        $this->setColourPreference($S1Player);
                        $this->setColourPreference($S2Player);

                        if ($this->AssertAbsoluteCriteria($S1Player, $S2Player))
                        {
                            if ($this->AssertRelativeCriteria($S1Player, $S2Player))
                            {
                                $pairings[$n-1] = array($S1Player, $S2Player);
                                $n++;
                            
                                $i++;
                                $j++;
                                $pairingsPerGroup--;
                            }
                            else if ($k > 0)
                            {
                                $pairings[$n-1] = array($S1Player, $S2Player);
                                $n++;
                            
                                $i++;
                                $j++;
                                $pairingsPerGroup--;
                                
                                $k-- ;
                            }
                            else
                            {
                                dump($S2);
                                $this->permute($S2);
                                dump($S2);
                            }
                        }
                        else
                        {
                          $this->permute($S2);
                        }
                    }
                }
        }

        return $pairings;
    }
    
    private function createGames($round, $pairings)
    {
        uasort($pairings, array($this, "cmpPairings"));

        $n = 1;
        
        $S1ColourPreference = SwissManager::$ABS_BLACK_PREF;
        $S2ColourPreference = SwissManager::$ABS_WHITE_PREF;
        
        // Once the pairings are validated, create the games
        foreach($pairings as $pairing)
        { 
            $S1Player = $pairing[0];
            $S2Player = $pairing[1];
            
            if ($round->getNumber() == 1)
            {
                $S1Player->setColourPreference($S1ColourPreference);
                $S2Player->setColourPreference($S2ColourPreference);
                
                $S1ColourPreference = $S2Player->getColourPreference();
                $S2ColourPreference = $S1Player->getColourPreference();
            }
            
            if ($this->wantWhite($S1Player) && !$this->wantWhite($S2Player) ||
                $this->wantBlack($S2Player) &&  !$this->wantBlack($S1Player))
            {
                $white = $S1Player;
                $black = $S2Player;
            }
            else if ($this->wantBlack($S1Player) && !$this->wantBlack($S2Player) ||
                     $this->wantWhite($S2Player) &&  !$this->wantWhite($S1Player))
            {    
                $white = $S2Player;
                $black = $S1Player;
            }
            else if ($this->wantWhite($S1Player))
            {    
                $white = $S1Player;
                $black = $S2Player;
            }
            else if ($this->wantBlack($S1Player))
            {    
                $white = $S2Player;
                $black = $S1Player;
            }
            
            $game = new Game($round, $n, $white, $black);
           
            $this->em->persist($game);
           
            $n++;
        }
        
        $this->em->flush();
    }
    
    private function AssertAbsoluteCriteria($player1, $player2)
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
    
    private function AssertRelativeCriteria($player1, $player2)
    {
        return true;
    }
    
    
   /**
 * Find a next array permutation
 * 
 * @param array $input
 * @return boolean
 */
function permute(&$input)
{
	$inputCount = count($input);
	// the head of the suffix
	$i = $inputCount - 1;
	// find longest suffix
	while ($i > 0 && $input[$i] <= $input[$i - 1]) {
		$i--;
	}
	//are we at the last permutation already?
	if ($i <= 0) {
		return false;
	}
	// get the pivot
	$pivotIndex = $i - 1;
	// find rightmost element that exceeds the pivot
	$j = $inputCount - 1;
	while ($input[$j] <= $input[$pivotIndex]) {
		$j--;
	}
		
	// swap the pivot with j
	$temp = $input[$pivotIndex];
	$input[$pivotIndex] = $input[$j];
	$input[$j] = $temp;
	// reverse the suffix
	$j = $inputCount - 1;
	while ($i < $j) {
	        $temp = $input[$i];
	        $input[$i] = $input[$j];
	        $input[$j] = $temp;
	        $i++;
	        $j--;
	}
	return true;
}

    
    private function switchPlayers($group1, $group2)
    {
    
    }
}
