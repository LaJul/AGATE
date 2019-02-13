<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Player;

/**
 * Description of ScoreGroup
 *
 * @author julien
 */
class ScoreGroup {

    /**
     */
    private $score;
    private $players;
    private $floaters;
    private $prev;
    private $next;
    
    private $limbo;
    private $remainder;
    
    private $S1;
    private $S2;
    private $S1initial;
    private $S2initial;
    private $exchangesTable;
    private $pairings;

    public function __construct(float $score, $players) {
        $this->score = $score;
        
        usort($players, array($this, "cmpPlayers"));
        $this->players = new ArrayCollection($players);
        $this->floaters = new ArrayCollection();
        $this->S1 = new ArrayCollection();
        $this->S2 = new ArrayCollection();
        $this->exchangesTable = array();

        $this->pairings = new ArrayCollection();
    }

    public function getScore() {
        return $this->score;
    }
    
    public function getPlayers() {
        return $this->players;
    }

    public function getAllPlayers() {
        return new ArrayCollection(array_merge($this->floaters->toArray(), $this->players->toArray()));
    }

    public function addFloater(Player $player) {
        $this->floaters->add($player);
    }
    
    public function getFloaters() {
        return $this->floaters;
    }
    
    public function setPrev(ScoreGroup $prev = null) {

        $this->prev = $prev;

        return $this;
    }

    public function getPrev() {
        return $this->prev;
    }

    public function setNext(ScoreGroup $next = null) {

        $this->next = $next;

        return $this;
    }

    public function getNext() {
        return $this->next;
    }
    
    public function getM0(){
        return $this->floaters->count();
    }
    
    public function getM1(){
        return min($this->getM0(), $this->players->count());
    }

    public function setS1S2($S1, $S2) {
        usort($S1, array($this, "cmpPlayers"));
        usort($S2, array($this, "cmpPlayers"));

        $this->setExchangesTable($S1, $S2);

        $this->S1 = new ArrayCollection($S1);
        $this->S2 = new ArrayCollection($S2);
        
        $this->S1initial = $this->S1;
        $this->S2initial = $this->S2;

        return $this;
    }

    public function getS1() {
        return $this->S1;
    }

    public function getS2() {
        return $this->S2;
    }

    public function getPairings() {
        return $this->pairings;
    }
    
    public function clearPairings() {
        return $this->pairings->clear();
    }
    
     /**
     * Find a next array permutation
     * 
     * @param array $input
     * @return boolean
     */
    public function permute() {
        $count = count($this->S2);
        // the head of the suffix
        $i = $count - 1;
        // find longest suffix
        while ($i > 0 && $this->S2[$i]->getPairingNumber() <= $this->S2[$i - 1]->getPairingNumber()) {
            $i--;
        }
        //are we at the last permutation already?
        if ($i <= 0) {
            return false;
        }
        // get the pivot
        $pivotIndex = $i - 1;
        // find rightmost element that exceeds the pivot
        $j = $count - 1;
        while ($this->S2[$j]->getPairingNumber() <= $this->S2[$pivotIndex]->getPairingNumber()) {
            $j--;
        }

        // swap the pivot with j
        $temp = $this->S2[$pivotIndex];
        $this->S2[$pivotIndex] = $this->S2[$j];
        $this->S2[$j] = $temp;
        // reverse the suffix
        $j = $count - 1;
        while ($i < $j) {
            $temp = $this->S2[$i];
            $this->S2[$i] = $this->S2[$j];
            $this->S2[$j] = $temp;
            $i++;
            $j--;
        }
        return true;
    }
    
    public function exchange() {

        if (empty($this->exchangesTable)) {
            return false;
        }
      
        $this->S1 = $this->S1initial;
        $this->S2 = $this->S2initial;

        $ij = \array_pop($this->exchangesTable);

        $temp = $this->S1[$ij[0]];
        $this->S1[$ij[0]] = $this->S2[$ij[1]];
        $this->S1[$ij[1]] = $temp;

        usort($this->S1, array($this, "cmpPlayers"));
        usort($this->S2, array($this, "cmpPlayers"));

        return true;
    }

    public function float(Player $player){
        $this->S1->removeElement($player);
        $this->next->addFloater($player);
    }
    
    public function addPairing(Pairing $pairing) {
        $this->pairings->add($pairing);
    }

    private static function cmpPlayers($a, $b) {
        if ($a->getPoints() == $b->getPoints()) {
            return $a->getPairingNumber() > $b->getPairingNumber() ? 1 : -1;
        }
        return $a->getPoints() < $b->getPoints() ? 1 : -1;
    }
    
    private function setExchangesTable() {

        $S1Count = count($this->S1);
        $S2Count = count($this->S2);

        if ($S1Count < 2) {
            return;
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

            \array_push($this->exchangesTable, array($iraz, $jraz));

            if ($j != $S2Count - 1) {
                $jraz++;
            } else if ($i != 0) {
                $iraz--;
            }

            while ($i != 0 && $j != 0) {
                $i--;
                $j--;
                \array_push($this->exchangesTable, array($i, $j));
            }
        }
    }

}
