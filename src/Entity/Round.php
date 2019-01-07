<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

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
    * @ORM\OneToMany(targetEntity="Game", mappedBy="round")
    */
    private $games;
    
    public function __construct($tournament, $number)
    {
        $this->tournament = $tournament;
        $this->number = $number;
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
    public function getGames()
    {
        return $this->games;
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
}
