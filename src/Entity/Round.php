<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of Round
 *
 * @author Julien Favarel
 * 
 * @ORM\Entity
 * @ORM\Table(name="round")
 */

class Round {
 
    /**
     * @ORM\Column(name="id",type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
     /**
    * @ORM\ManyToOne(targetEntity="Tournament", inversedBy="rounds")
    * @ORM\JoinColumn(name="tournament_id", referencedColumnName="id", onDelete="CASCADE")
    */
    private $tournament;
    
     /**
     * @ORM\Column(name="number", type="integer")
     */
    private $number;
    
    /**
    * @ORM\Column(name="start_date", type="datetime", nullable=true)
    */
    private $startDate;
    
    /**
    * @ORM\ManyToMany(targetEntity="Player", cascade={"persist"})
    */
    private $unPairedPlayers;
    
    /**
    * @ORM\ManyToMany(targetEntity="Player")
    * @ORM\JoinTable(name="round_tournamentOutPlayers")
    */
    private $tournamentOutPlayers;
    
    /**
    * @ORM\ManyToMany(targetEntity="Player")
    * @ORM\JoinTable(name="round_roundOutPlayers")
    */
    private $roundOutPlayers;
  
    /**
    * @ORM\OneToMany(targetEntity="Game", mappedBy="round")
    */
    private $games;
    
    /**
    * @ORM\OneToOne(targetEntity="Player")
    */
    private $exempt;
    
    public function __construct($tournament, $number)
    {
        $this->tournament = $tournament;
        $this->number = $number;
        $this->tournamentOutPlayers = new ArrayCollection();
        $this->roundOutPlayers = new ArrayCollection();
        $this->unPairedPlayers = new ArrayCollection();
    }
    
    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    
     /**
     * @return integer
     */ 
    public function getNumber()
    {
        return $this->number;
    }
    
     /**
     * @return entities
     */
    public function getRoundOutPlayers() {
        return $this->roundOutPlayers;
    }
    
    /**
     * @return this
     */
    public function add(Player $player){
        return $this->unPairedPlayers->add($player);
    }
    
     /**
     * @return this
     */
    public function remove(Player $player){
        $this->unPairedPlayers->removeElement($player);
        return $this->roundOutPlayers->add($player);
    }

    /**
     * @return entities
     */
    public function getTournamentOutPlayers() {
        return $this->tournamentOutPlayers;
    }
    
    /**
     * @return entities
     */
    public function getUnpairedPlayers() {
        return $this->unPairedPlayers;
    }
    
     /**
     * @return entities
     */ 
    public function getGames()
    {
        return $this->games;
    }
    
     /**
     * @return entities
     */ 
    public function getGame(int $number)
    {
        return $this->games[$number-1];
    }
    
    /**
     * @return Round
     */ 
    public function getPreviousRound()
    {
        return $this->tournament->getRound($this->number - 1);    
    }    
    
     /**
     * @return Round
     */ 
    public function getNextRound()
    {
        return $this->tournament->getRound($this->number + 1);    
    }

    /**
     * @return boolean
     */ 
    public function isCurrentRound()
    {
        return $this->tournament->getCurrentRound()->getNumber() == $this->number;    
    }   
    
    /**
     * @return boolean
     */ 
    public function isOver()
    {
        return !$this->games->isEmpty() && (!$this->games->exists(function($key, $element) {
            return $element->getResult() === "";
        }));    
    }   
    
    /**
     * @return boolean
     */ 
    public function isPairable()
    {
        if ($this->getPreviousRound() != NULL)
        {
            return $this->getPreviousRound()->isOver() && $this->games->isEmpty();
        }
        
        return $this->games->isEmpty();
        
    } 
    
      /**
     * @return boolean
     */ 
    public function isUnpairable()
    {
        if ($this->getNextRound() != NULL)
        {
            return $this->getNextRound()->getGames()->isEmpty() && !$this->games->isEmpty();
        }
        
        return !$this->games->isEmpty();
    } 
    
    /**
     * @param string $player
     * @return Game
     */ 
    public function setExempt(Player $player = NULL)
    {
        $this->exempt = $player;
        
        return $this;
    }
    
     /**
     * @return Player
     */ 
    public function getExempt()
    {
        return $this->exempt;
    }
}
