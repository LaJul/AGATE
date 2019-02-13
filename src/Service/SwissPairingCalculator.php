<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
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
        $pairings = $this->processPairings($groups);

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

    private function setPairingNumbers($players) {
        usort($players, array($this, "cmpInitialPlayers"));

        foreach ($players as $pairingNumber => $player) {
            $player->setPairingNumber($pairingNumber + 1);
            $this->em->persist($player);
        }

        $this->em->flush();
    }

    private static function cmpGroups($a, $b) {
        return $a[0]->getPoints() < $b[0]->getPoints();
    }

    private static function cmpPairings($a, $b) {

        // Score of the "strongest" player
        if (max($a->getPlayer1()->getPoints(), $a->getPlayer2()->getPoints()) == max($b->getPlayer1()->getPoints(), $b->getPlayer2()->getPoints())) {
            // Sum of the score of the two players
            if (($a->getPlayer1()->getPoints() + $a->getPlayer2()->getPoints()) == ($b->getPlayer1()->getPoints() + $b->getPlayer2()->getPoints())) {
                // Pairing number of the "strongest" player
                return min($a->getPlayer1()->getPairingNumber(), $a->getPlayer2()->getPairingNumber()) > min($b->getPlayer1()->getPairingNumber(), $b->getPlayer2()->getPairingNumber()) ? 1 : -1;
            }

            return (($a->getPlayer1()->getPoints() + $a->getPlayer2()->getPoints()) < ($b->getPlayer1()->getPoints() + $b->getPlayer2()->getPoints())) ? 1 : -1;
        }

        return (max($a->getPlayer1()->getPoints(), $a->getPlayer2()->getPoints()) < max($b->getPlayer1()->getPoints(), $b->getPlayer2()->getPoints())) ? 1 : -1;
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
        }
    }

    private function getScoreGroups($players) {
        $groups = array();

        // Group players by points
        foreach ($players as $player) {
            $groups[number_format($player->getPoints(), 1)][] = $player;
        }

        uasort($groups, array($this, "cmpGroups"));
        $this->logger->info('Nombre de groupes ' . count($groups));

        $prev = null;

        foreach ($groups as $score => $group) {
            $scoreGroup = new ScoreGroup($score, $group);

            $scoreGroup->setPrev($prev);

            if (is_null($prev)) {
                $firstGroup = $scoreGroup;
            } else {
                $prev->setNext($scoreGroup);
            }

            $prev = $scoreGroup;
        }

        return $firstGroup;
    }

    private function processPairings(ScoreGroup $group) {
        $pairings = array();

        $firstGroup = $group;

        while ($group != null) {

            $this->logger->info('----------------------------------------------');
            $this->logger->info('| Traitement du groupe ' . $group->getScore() . ' pts |');
            $this->logger->info('----------------------------------------------');

            $nextGroup = $group->getNext();

            if (count($group->getAllPlayers()) === 1) {

                $player = $group->getAllPlayers()->first();

                if ($nextGroup) {
                    $this->logger->info($player . ' est flotteur descendant');
                    $group->float($player);
                    $group = $nextGroup;

                    continue;
                } else {
                    // Last group

                    if ($this->assertAbsoluteCriteria($player, null)) {
                        $group->addPairing($player, null);
                    }
                }
            } else {
                if (!($group->getFloaters()->isEmpty())) {
                    $this->logger->info('Groupe hétérogène');
                    $result = $this->processHeterogenousGroup($group);
                } else {
                    $this->logger->info('Groupe homogène');
                    $result = $this->processHomogenousGroup($group);
                }
            }
            // If no pairings possible, undo the pairings of the previous group
            if (!$result) {
                $group = $group->getPrev();
                continue;
            }

            $group = $nextGroup;
        }

        return $this->getAllPairings($firstGroup);
    }

    private function processHomogenousGroup(ScoreGroup $group) {

        $N1 = floor(count($group->getPlayers()) / 2);

        $players = $group->getPlayers()->toArray();

        // C4 & C5
        $group->setS1S2(array_splice($players, 0, $N1), $players);

        return $this->processGroup($group);
    }

    private function processHeterogenousGroup(ScoreGroup $group) {

        $group->setS1S2($group->getFloaters()->toArray(), $group->getPlayers()->toArray());

        $this->processGroup($group);

        $this->logger->info('Groupe résiduel');
        $this->processHomogenousGroup($group);

        return true;
    }

    private function processGroup(ScoreGroup $group) {

        $S1 = $group->getS1();
        $S2 = $group->getS2();

        $this->debugGroup($S1, $S2);

        $x = $this->getX($group);

        $maxPairs = count($S1);
        $j = 0;

        while ($maxPairs > 0) {

            // S1 index
            $i = 0;
            // S2 index
            $j = 0;

            // Number of perturbed tables
            $k = $x;

            while ($i != count($S1)) {
                // in french C6
                $S1Player = $S1[$i];
                $S2Player = $S2[$j];

                $this->logger->info("Test de " . $S1Player->getLastName() . " contre " . $S2Player->getLastName());

                if ($this->assertAbsoluteCriteria($S1Player, $S2Player)) {
                    if ($this->assertQualityCriteria($S1Player, $S2Player)) {
                        $group->addPairing(new Pairing($S1Player, $S2Player));

                        $i++;
                        $j++;
                        $maxPairs--;
                    } else if ($k > 0) {
                        $group->addPairing(new Pairing($S1Player, $S2Player));

                        $i++;
                        $j++;
                        $maxPairs--;

                        $k--;
                    } else {
                        $this->logger->info("Permutation...");
                        if (!$group->permute()) {
                            $this->logger->info("Plus de permutations...Echange");
                            if (!$group->exchange()) {
                                $this->logger->info("Plus d'échanges...");
                            }
                        }

                        $group->clearPairings();
                        $maxPairs = 0;
                        $i = 0;
                        $j = 0;
                        $k = $x;
                    }
                } else {

                    $this->logger->info("Permutation...");
                    if (!$group->permute()) {
                        $this->logger->info("Plus de permutations...Echange");
                        if (!$group->exchange()) {
                            $this->logger->info("Plus d'échanges...");

                            $this->logger->info($S1Player . ' flotte au niveau inférieur');
                            $group->float($S1Player);
                        }
                    }

                    $group->clearPairings();
                    $maxPairs = count($group->getS1());
                    $i = 0;
                    $j = 0;
                    $k = $x;
                }
            }
        }

        while ($j < count($S2)) {
            $this->logger->info($S2[$j] . ' flotte au niveau inférieur');
            $group->float($S2[$j]);
            $j++;
        }
        $this->logger->info('Fin du traitement du groupe');

        return true;
        ;
    }

    private function createGames(Round $round, $pairings) {

        usort($pairings, array($this, "cmpPairings"));

        // Board number
        $n = 1;

        $S2ColourPreference = SwissPairingCalculator::$ABS_BLACK_PREF;
        $S1ColourPreference = SwissPairingCalculator::$ABS_WHITE_PREF;

        // Once the pairings are validated, create the games
        foreach ($pairings as $pairing) {
            $S1Player = $pairing->getPlayer1();
            $S2Player = $pairing->getPlayer2();

            if ($S2Player == null) {
                $game = new Game($round, $n, $S1Player, null);
                $round->getUnpairedPlayers()->removeElement($exempt);
                $game->setResult('1-F');
            } else {
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
            }

            $this->em->persist($game);

            $n++;
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

    private function getX(ScoreGroup $scoreGroup) {

        $q = ceil((count($scoreGroup->getAllPlayers())) / 2);

        // C2 & C3
        // Joker
        $x = 0;

        // Players with white preference
        $w = count(array_filter($scoreGroup->getAllPlayers()->toArray(), array($this, "wantWhite")));
        // Players with black preference
        $b = count(array_filter($scoreGroup->getAllPlayers()->toArray(), array($this, "wantBlack")));

        if ($b > $w) {
            $x = $b - $q;
        } else {
            $x = $w - $q;
        }

        return $x;
    }

    private function getAllPairings(ScoreGroup $group) {
        $pairings = array();

        while ($group != null) {
            $pairings = array_merge($pairings, $group->getPairings()->toArray());
            $group = $group->getNext();
        }

        return $pairings;
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
