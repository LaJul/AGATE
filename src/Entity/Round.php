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
    * @ORM\OneToMany(targetEntity="Game", mappedBy="round", cascade={"persist", "remove"})
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
        return $this->tournament->getRounds($this->number - 1);    
    }    
    
     /**
     * @return Round
     */ 
    public function getNextRound()
    {
        return $this->tournament->getRounds($this->number + 1);    
    }    
}
