<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Entity\Tournament;
use App\Entity\Round;
use App\Entity\Game;

class SwissManager {

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

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger) {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function pairRound(Tournament $tournament, Round $round) {
        $playerRepository = $this->em->getRepository('App\Entity\Player');

        $this->logger->info('Appariement de la ronde ' . $round->getNumber());

        if ($round->getNumber() == 1) {
            $players = $playerRepository->findBy(['tournament' => $tournament->getId()]);
            $this->setPairingNumbers($players);
        }
      
        $players = $playerRepository->getAllActivePlayers($tournament);
        
        // Compute colour preferences
        $this->setColourPreference($players);
        
        // Group players by points
        $groups = $this->getScoreGroups($players);

        // Process pairings
        $pairings = $this->processPairings($groups);

        // Create games
        $this->createGames($round, $pairings);

        return $round;
    }

    public function unpairRound(Round $round) {
        $games = $round->getGames();

        foreach ($games as $game) {
            $this->em->remove($game);
        }

        $this->em->flush();
    }

    /*
     * Private methods
     */

    private static function cmpPlayers($a, $b) {

        if ($a->getPoints() == $b->getPoints()) {

            if ($a->getRating() == $b->getRating()) {
                return $a->getName() > $b->getName() ? 1 : -1;
            }

            return $a->getRating() < $b->getRating() ? 1 : -1;
        }

        return $a->getPoints() < $b->getPoints() ? 1 : -1;
    }

    private static function cmpGroups($a, $b) {
        return $a[0]->getPoints() < $b[0]->getPoints();
    }

    private static function cmpPairings($a, $b) {

        // Score of the "strongest" player
        if (max($a[0]->getPoints(), $a[1]->getPoints()) == max($b[0]->getPoints(), $b[1]->getPoints())) {
            // Sum of the score of the two players
            if (($a[0]->getPoints() + $a[1]->getPoints()) == ($b[0]->getPoints() + $b[1]->getPoints())) {
                // Pairing number of the "strongest" player
                return min($a[0]->getPairingNumber(), $a[1]->getPairingNumber()) > min($b[0]->getPairingNumber(), $b[1]->getPairingNumber()) ? 1 : -1;
            }

            return (($a[0]->getPoints() + $a[1]->getPoints()) < ($b[0]->getPoints() + $b[1]->getPoints())) ? 1 : -1;
        }

        return (max($a[0]->getPoints(), $a[1]->getPoints()) < max($b[0]->getPoints(), $b[1]->getPoints())) ? 1 : -1;
    }

    private static function wantWhite($player) {
        return $player->getColourPreference() == SwissManager::$ABS_WHITE_PREF ||
                $player->getColourPreference() == SwissManager::$STRONG_WHITE_PREF ||
                $player->getColourPreference() == SwissManager::$MILD_WHITE_PREF;
    }

    private static function wantBlack($player) {
        return $player->getColourPreference() == SwissManager::$ABS_BLACK_PREF ||
                $player->getColourPreference() == SwissManager::$STRONG_BLACK_PREF ||
                $player->getColourPreference() == SwissManager::$MILD_BLACK_PREF;
    }

    /**
     * @return integer
     */
    private function setColourPreference($players) {
        foreach ($players as $player) {
            $colourDifference = $player->getColourDifference();
            $colourPreference = SwissManager::$NO_PREF;

            $this->logger->info($player . ' différence de couleur ' . $colourDifference);

            if ($colourDifference < -1) {
                $colourPreference = SwissManager::$ABS_WHITE_PREF;
            } else if ($colourDifference > 1) {
                $colourPreference = SwissManager::$ABS_BLACK_PREF;
            } else if ($colourDifference == -1) {
                $colourPreference = SwissManager::$STRONG_WHITE_PREF;
            } else if ($colourDifference == 1) {
                $colourPreference = SwissManager::$STRONG_BLACK_PREF;
            } else if ($colourDifference == 0) {
                $gameRepository = $this->em->getRepository('App\Entity\Game');
                $lastGameColour = $gameRepository->getLastGameColour($player);

                if ($lastGameColour == SwissManager::$WHITE) {
                    $colourPreference = SwissManager::$MILD_BLACK_PREF;
                } else if ($lastGameColour == SwissManager::$BLACK) {
                    $colourPreference = SwissManager::$MILD_BLACK_PREF;
                }
            }
            $player->setColourPreference($colourPreference);
            $this->logger->info($player . ' préférence de couleur ' . $colourPreference);
        }
    }

    private function setPairingNumbers($players) {
        usort($players, array($this, "cmpPlayers"));

        foreach ($players as $pairingNumber => $player) {
            $player->setPairingNumber($pairingNumber + 1);
            $this->em->persist($player);
        }

        $this->em->flush();
    }

    private function getScoreGroups($players) {
        $groups = array();

        // Group players by points
        foreach ($players as $player) {
            $this->logger->info('---' . $player);

            $groups[number_format($player->getPoints(), 1)][] = $player;
        }

        uasort($groups, array($this, "cmpGroups"));
        $this->logger->info('Nombre de groupes ' . count($groups));

        return $groups;
    }

    private function processPairings($groups) {
        $pairings = array();
        $floaters = array();
        $n = 1;

        foreach ($groups as $points => $group) {

            $this->logger->info('Traitement du groupe ' . $points . ' pts');

            uasort($group, array($this, "cmpPlayers"));

            if (empty($floaters)) {
                $S1 = $floaters;
                $S2 = $group;
            } else {
                // C4 & C5
                $S1 = array_splice($group, 0, $q);
                $S2 = $group;
            }

            // C2 & C3
            // Joker
            $x = 0;

            // Players with white preference
            $w = 0;
            // Players with black preference
            $b = 0;

            $q = floor(count($group) / 2);

            if ($b > $w) {
                $x = $b - $q;
            } else {
                $x = $w - $q;
            }

            $p = count($S1);

            $groupPairings = array();

            // Init switch table
            $switchTable = $this->getSwitchTable($S1, $S2);

            while ($p > 0) {

                // S1 index
                $i = 0;
                // S2 index
                $j = 0;

                // Number of perturbed tables
                $k = $x;

                while ($i != count($S1)) {

                    // C6
                    // Player of the strong group
                    $S1Player = $S1[$i];
                    // Player of the weak group
                    $S2Player = $S2[$j];

                    foreach ($S1 as $player) {
                        $this->logger->info("S1-" . $player->getPairingNumber() . " : " . $player);
                    }
                    
                    foreach ($S2 as $player) {
                        $this->logger->info("S2-" . $player->getPairingNumber() . " : " . $player);
                    }

                    $this->logger->info("Test de " . $S1Player->getName() . " contre " . $S2Player->getName());
                    sleep(1);
                 
                    if ($this->assertAbsoluteCriteria($S1Player, $S2Player)) {
                        if ($this->assertRelativeCriteria($S1Player, $S2Player)) {
                            array_push($groupPairings, array($S1Player, $S2Player));

                            $i++;
                            $j++;
                            $p--;
                        } else if ($k > 0) {
                            array_push($groupPairings, array($S1Player, $S2Player));

                            $i++;
                            $j++;
                            $p--;

                            $k--;
                        } else {
                            if (!$this->permute($S2)) {
                                $this->logger->info("Plus de permutations...");
                                $this->logger->info("Echange...");

                                if (empty($switchTable)) {
                                    $this->logger->info("Plus d'échanges...");
                                } else {
                                    $S1 = array_splice($group, 0, $q);
                                    $S2 = $group;

                                    $this->switchPlayers($S1, $S2, $switchTable);
                                }
                            }

                            $groupPairings = array();
                            $p = 0;
                            $i = 0;
                            $j = 0;
                            $k = $x;
                        }
                    } else {
                        if (!$this->permute($S2)) {
                            $this->logger->info("Plus de permutations...");
                            $this->logger->info("Echange...");

                            if (empty($switchTable)) {
                                $this->logger->info("Plus d'échanges...");
                            } else {
                                $S1 = array_splice($group, 0, $q);
                                $S2 = $group;

                                $this->switchPlayers($S1, $S2, $switchTable);
                            }
                        }

                        $groupPairings = array();
                        $p = 0;
                        $i = 0;
                        $j = 0;
                        $k = $x;
                    }
                }
            }

            $pairings = array_merge($pairings, $groupPairings);

            if (empty($floaters)) {
                
            }
        }

        return $pairings;
    }

    private function createGames($round, $pairings) {

        usort($pairings, array($this, "cmpPairings"));

        $n = 1;

        $S1ColourPreference = SwissManager::$ABS_BLACK_PREF;
        $S2ColourPreference = SwissManager::$ABS_WHITE_PREF;

        // Once the pairings are validated, create the games
        foreach ($pairings as $pairing) {
            $S1Player = $pairing[0];
            $S2Player = $pairing[1];

            if ($round->getNumber() == 1) {
                $S1Player->setColourPreference($S1ColourPreference);
                $S2Player->setColourPreference($S2ColourPreference);

                $S1ColourPreference = $S2Player->getColourPreference();
                $S2ColourPreference = $S1Player->getColourPreference();
            }

            if ($this->wantWhite($S1Player) && !$this->wantWhite($S2Player) ||
                    $this->wantBlack($S2Player) && !$this->wantBlack($S1Player)) {
                $white = $S1Player;
                $black = $S2Player;
            } else if ($this->wantBlack($S1Player) && !$this->wantBlack($S2Player) ||
                    $this->wantWhite($S2Player) && !$this->wantWhite($S1Player)) {
                $white = $S2Player;
                $black = $S1Player;
            } else if ($this->wantWhite($S1Player)) {
                $white = $S1Player;
                $black = $S2Player;
            } else if ($this->wantBlack($S1Player)) {
                $white = $S2Player;
                $black = $S1Player;
            }

            $game = new Game($round, $n, $white, $black);

            $this->em->persist($game);

            $n++;
        }

        $this->em->flush();
    }

    private function assertAbsoluteCriteria($player1, $player2) {
        $gameRepository = $this->em->getRepository('App\Entity\Game');

        // C1 (Two players shall not play against each other more than once)        
        if ($gameRepository->getGame($player1, $player2) != null) {
            $this->logger->info($player1 . " et " . $player2 . " se sont déjà joués");

            return false;
        }

        // C2 (A player who has already received a pairing-allocated bye, or has already scored a (forfeit) win
        // due to an opponent not appearing in time, shall not receive the pairing-allocated bye)
        // TO DO
        // C3 (Non top-scorers with the same absolute colour preference shall not meet
        $p1CP = $player1->getColourPreference();
        $p2CP = $player2->getColourPreference();

        if (($p1CP == $p2CP) && (($p1CP == SwissManager::$ABS_WHITE_PREF) or ( $p1CP == SwissManager::$ABS_BLACK_PREF))) {
            $this->logger->info($player1 . " et " . $player2 . " ont les mêmes couleurs absolues");

            return false;
        }



        $this->logger->info("Appariement possible entre " . $player1 . " et " . $player2);

        return true;
    }

    private function assertRelativeCriteria($player1, $player2) {

        return true;
    }

    /**
     * Find a next array permutation
     * 
     * @param array $input
     * @return boolean
     */
    private function permute(&$input) {
        $inputCount = count($input);
        // the head of the suffix
        $i = $inputCount - 1;
        // find longest suffix
        while ($i > 0 && $input[$i]->getPairingNumber() <= $input[$i - 1]->getPairingNumber()) {
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
        while ($input[$j]->getPairingNumber() <= $input[$pivotIndex]->getPairingNumber()) {
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

    private function getSwitchTable($S1, $S2) {

        $switchTable = array();
        $j = 0;

        $iraz = count($S1) - 1;
        $jraz = 0;

        while ($j != count($S2) - 1) {
            $i = $iraz;
            $j = $jraz;

            \array_push($switchTable, array($iraz, $jraz));

            if ($j != count($S2)) {
                $jraz++;
            } else if ($i != 0) {
                $iraz--;
            }

            while ($i != 0 && $j != 0) {
                $i--;
                $j--;
                \array_push($switchTable, array($i, $j));
                $this->logger->info("i: " . $i . " -j: " . $j);
            }
        }

        return $switchTable;
    }

    private function switchPlayers(&$group1, &$group2, &$switchTable) {

        $ij = \array_pop($switchTable);

        $temp = $group1[$ij[0]];
        $group1[$ij[0]] = $group2[$ij[1]];
        $group1[$ij[1]] = $temp;

        uasort($group1, array($this, "cmpPlayers"));
        uasort($group2, array($this, "cmpPlayers"));
    }

}
