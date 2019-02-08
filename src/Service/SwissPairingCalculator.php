<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Entity\Tournament;
use App\Entity\Round;
use App\Entity\Game;

class SwissPairingCalculator {

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

    public function pairRound(Round $round) {

        $this->logger->info('Appariement de la ronde ' . $round->getNumber());

        if ($round->getNumber() == 1) {
            $this->logger->info("Attribution des numéros d'appariement");
            $this->setPairingNumbers($round->getUnpairedPlayers()->toArray());
        }

        $players = $round->getUnpairedPlayers();

        // Compute colour preferences
        $this->setColourPreference($players);

        // Group players by points
        $groups = $this->getScoreGroups($players);

        // Process pairings
        $pairings = $this->processPairings($round, $groups);

        // Create games
        $this->createGames($round, $pairings);

        return $round;
    }

    public function unpairRound(Round $round) {
        $games = $round->getGames();

        foreach ($games as $game) {
            $round->add($game->getWhite());
            if ($game->getBlack() != null) {
                $round->add($game->getBlack());
            } else {
                $round->setExempt();
            }
            $this->em->remove($game);
        }

        $this->em->flush();
    }

    public function setGameResult(Game $game, string $result) {
        $game->setResult($result);

        $round = $game->getRound();

        if ($round->isOver()) {
            $nextRound = $round->getNextRound();
            if ($nextRound != null) {
                foreach ($round->getGames() as $game) {
                    $nextRound->add($game->getWhite());
                    if ($game->getBlack() != null) {
                        $nextRound->add($game->getBlack());
                    }
                }
            }
        }
    }

    /*
     * Private methods
     */

    private static function cmpInitialPlayers($a, $b) {

        if ($a->getRating() == $b->getRating()) {
            return $a->getLastName() > $b->getLastName() ? 1 : -1;
        }

        return $a->getRating() < $b->getRating() ? 1 : -1;
    }

    private static function cmpPlayers($a, $b) {

        if ($a->getPoints() == $b->getPoints()) {
            return $a->getPairingNumber() > $b->getPairingNumber() ? 1 : -1;
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
        return $player->getColourPreference() == SwissPairingCalculator::$ABS_WHITE_PREF ||
                $player->getColourPreference() == SwissPairingCalculator::$STRONG_WHITE_PREF ||
                $player->getColourPreference() == SwissPairingCalculator::$MILD_WHITE_PREF;
    }

    private static function wantBlack($player) {
        return $player->getColourPreference() == SwissPairingCalculator::$ABS_BLACK_PREF ||
                $player->getColourPreference() == SwissPairingCalculator::$STRONG_BLACK_PREF ||
                $player->getColourPreference() == SwissPairingCalculator::$MILD_BLACK_PREF;
    }

    /**
     * @return integer
     */
    private function setColourPreference($players) {
        foreach ($players as $player) {
            $colourDifference = $player->getColourDifference();
            $colourPreference = SwissPairingCalculator::$NO_PREF;

            //$this->logger->info($player . ' différence de couleur ' . $colourDifference);

            if ($colourDifference < -1) {
                $colourPreference = SwissPairingCalculator::$ABS_WHITE_PREF;
            } else if ($colourDifference > 1) {
                $colourPreference = SwissPairingCalculator::$ABS_BLACK_PREF;
            } else if ($colourDifference == -1) {
                $colourPreference = SwissPairingCalculator::$STRONG_WHITE_PREF;
            } else if ($colourDifference == 1) {
                $colourPreference = SwissPairingCalculator::$STRONG_BLACK_PREF;
            } else if ($colourDifference == 0) {
                $gameRepository = $this->em->getRepository('App\Entity\Game');
                $lastGameColour = $gameRepository->getLastGameColour($player);

                if ($lastGameColour == SwissPairingCalculator::$WHITE) {
                    $colourPreference = SwissPairingCalculator::$MILD_BLACK_PREF;
                } else if ($lastGameColour == SwissPairingCalculator::$BLACK) {
                    $colourPreference = SwissPairingCalculator::$MILD_BLACK_PREF;
                }
            }
            $player->setColourPreference($colourPreference);
            //$this->logger->info($player . ' préférence de couleur ' . $colourPreference);
        }
    }

    private function setPairingNumbers($players) {
        usort($players, array($this, "cmpInitialPlayers"));

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
            $groups[number_format($player->getPoints(), 1)][] = $player;
        }

        uasort($groups, array($this, "cmpGroups"));
        $this->logger->info('Nombre de groupes ' . count($groups));

        return $groups;
    }

    private function processPairings($round, $groups) {
        $pairings = array();
        $floaters = array();

        while (current($groups)) {

            $group = current($groups);

            $this->logger->info('----------------------------------------------');
            $this->logger->info('| Traitement du groupe ' . key($groups) . ' pts |');
            $this->logger->info('----------------------------------------------');

            if (count($group) === 1) {

                $nextGroup = next($groups);

                if ($nextGroup) {
                    $this->logger->info($group[0] . ' est flotteur descendant');
                    $floaters[] = $group[0];
                    continue;
                } else {
                    // Last group
                    if ($this->assertAbsoluteCriteria($group[0], null)) {
                        $round->setExempt($group[0]);
                    }
                }
            } else {
                if (!empty($floaters)) {
                    $this->logger->info('Groupe hétérogène');
                    $groupPairings = $this->processHeterogenousGroup($floaters, $group);
                } else {
                    $this->logger->info('Groupe homogène');
                    $groupPairings = $this->processHomogenousGroup($floaters, $group);
                }
            }
            // If no pairings possible, undo the pairings of the previous group
            if (count($groupPairings) === 0) {
                prev($groups);
                continue;
            }

            $pairings[key($groups)] = $groupPairings;

            next($groups);
        }

        return $this->flatten($pairings);
    }

    private function processHomogenousGroup(&$floaters, $group) {

        $q = floor(count($group) / 2);

        usort($group, array($this, "cmpPlayers"));

        // C4 & C5
        $S1 = array_splice($group, 0, $q);
        $S2 = $group;

        return $this->processGroup($floaters, $S1, $S2);
    }

    private function processHeterogenousGroup(&$floaters, $group) {
        $S1 = array_merge($floaters);
        $S2 = $group;

        $floaters = array();

        usort($S1, array($this, "cmpPlayers"));
        usort($S2, array($this, "cmpPlayers"));

        $floatersGroupPairings = $this->processGroup($floaters, $S1, $S2);
        $this->logger->info('Groupe résiduel');

        $group = array_merge($floaters);
        $floaters = array();

        $residualGroupPairings = $this->processHomogenousGroup($floaters, $group);

        return array_merge($floatersGroupPairings, $residualGroupPairings);
    }

    private function processGroup(&$floaters, $S1, $S2) {

        $groupPairings = array();

        $this->debugGroup($S1, $S2);

        $q = floor((count($S1) + count($S2)) / 2);

        // C2 & C3
        // Joker
        $x = 0;

        $group = array_merge($S1, $S2);

        // Players with white preference
        $w = count(array_filter($group, array($this, "wantWhite")));
        // Players with black preference
        $b = count(array_filter($group, array($this, "wantBlack")));

        if ($b > $w) {
            $x = $b - $q;
        } else {
            $x = $w - $q;
        }
        // max Pairs ('p' in french)
        $maxPairs = count($S1);

        // Init switch table
        $switchTable = $this->getSwitchTable($S1, $S2);

        $S1init = $S1;
        $S2init = $S2;

        while ($maxPairs > 0) {

            // S1 index
            $i = 0;
            // S2 index
            $j = 0;

            // Number of perturbed tables
            $k = $x;

            while ($i != count($S1)) {
                // in french C6
                // Player of the strong group
                $S1Player = $S1[$i];
                // Player of the weak group
                $S2Player = $S2[$j];

                $this->logger->info("Test de " . $S1Player->getLastName() . " contre " . $S2Player->getLastName());

                if ($this->assertAbsoluteCriteria($S1Player, $S2Player)) {
                    if ($this->assertQualityCriteria($S1Player, $S2Player)) {
                        $groupPairings[] = array($S1Player, $S2Player);

                        $i++;
                        $j++;
                        $maxPairs--;
                    } else if ($k > 0) {
                        $groupPairings[] = array($S1Player, $S2Player);

                        $i++;
                        $j++;
                        $maxPairs--;

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
                        $maxPairs = 0;
                        $i = 0;
                        $j = 0;
                        $k = $x;
                    }
                } else {

                    $this->logger->info("Permutation...");
                    if (!$this->permute($S2)) {
                        $this->logger->info("Plus de permutations...Echange");

                        if (empty($switchTable)) {
                            $this->logger->info("Plus d'échanges...");

                            $floaters[] = $S1Player;
                            $this->logger->info($S1Player . ' flotte au niveau inférieur');
                            return $this->processHomogenousGroup($floaters, array_merge(array_splice($S1, 0, 1), $S2));
                        } else {
                            $S1 = array_splice($S1init, 0, $q);
                            $S2 = $S2init;

                            $this->switchPlayers($S1, $S2, $switchTable);
                        }
                    }

                    $groupPairings = array();
                    $maxPairs = count($S1);
                    $i = 0;
                    $j = 0;
                    $k = $x;
                }
            }
        }

        while ($j < count($S2)) {
            $this->logger->info($S2[$j] . ' flotte au niveau inférieur');
            $floaters[] = $S2[$j];
            $j++;
        }
        $this->logger->info('Fin du traitement du groupe');

        return $groupPairings;
    }

    private function createGames(Round $round, $pairings) {

        usort($pairings, array($this, "cmpPairings"));

        $n = 1;

        $S1ColourPreference = SwissPairingCalculator::$ABS_BLACK_PREF;
        $S2ColourPreference = SwissPairingCalculator::$ABS_WHITE_PREF;

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
            $this->logger->info($game);

            $round->getUnpairedPlayers()->removeElement($white);
            $round->getUnpairedPlayers()->removeElement($black);

            $this->em->persist($game);

            $n++;
        }

        $exempt = $round->getExempt();

        if ($exempt != null) {
            $game = new Game($round, $n, $exempt, null);
            $round->getUnpairedPlayers()->removeElement($exempt);

            $game->setResult('1-F');

            $this->em->persist($game);
        }

        $this->em->persist($round);

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
        if ($gameRepository->getWonGamesByForfeit($player1) != null && $player2 == null) {
            return false;
        }

        // C3 (Non top-scorers with the same absolute colour preference shall not meet
        $p1CP = $player1->getColourPreference();
        $p2CP = $player2->getColourPreference();

        if (($player1->isTopscorer() && $player2->isTopscorer()) &&
                ((($p1CP == SwissPairingCalculator::$ABS_WHITE_PREF) && ($p2CP == SwissPairingCalculator::$ABS_WHITE_PREF)) ||
                (($p1CP == SwissPairingCalculator::$ABS_BLACK_PREF) && ($p2CP == SwissPairingCalculator::$ABS_BLACK_PREF)))) {

            $this->logger->info($player1 . " et " . $player2 . " ont les mêmes couleurs absolues");

            return false;
        }

        $this->logger->info("Appariement possible entre " . $player1 . " et " . $player2);

        return true;
    }

    private function assertQualityCriteria($player1, $player2) {
        // C5

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

        $S1Count = count($S1);
        $S2Count = count($S2);

        if ($S1Count < 2) {
            return $switchTable;
        }


        if (($S1Count + $S2Count) & 1) {
            $iraz = $S1Count - 2;
            $ilim = 0;
        } else {
            $iraz = $S1Count - 1;
            $ilim = 1;
        }

        $jraz = 0;

        while ($jraz != $S2Count - 2 && $ilim != 0) {
            $i = $iraz;
            $j = $jraz;

            \array_push($switchTable, array($iraz, $jraz));

            if ($j != $S2Count - 1) {
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

        usort($group1, array($this, "cmpPlayers"));
        usort($group2, array($this, "cmpPlayers"));
    }

    private function flatten(array $array) {
        $return = array();
        
        foreach ($array as $value){
            $return = array_merge($return, $value);
        }
        
        return $return;
    }
    
    private function debugGroup($S1, $S2) {

        $i = 0;

        $this->logger->info('-------------------------------------------------------------------------');
        $this->logger->info('|               S1                  |                 S2                |');
        $this->logger->info('-------------------------------------------------------------------------');

        while ($i < max(count($S2), count($S1))) {

            if (!array_key_exists($i, $S1)) {
                $S1player = '';
            } else {
                $S1player = $S1[$i];
            }

            if (!array_key_exists($i, $S2)) {
                $S2player = '';
            } else {
                $S2player = $S2[$i];
            }

            $this->logger->info('|' . str_pad($S1player, 35) . '|' . str_pad($S2player, 35) . '|');
            $i++;
        }

        $this->logger->info('-------------------------------------------------------------------------');
    }

}
