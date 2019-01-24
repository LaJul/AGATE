<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description of Tournament
 *
 * @author Julien Favarel
 * 
 * @ORM\Entity
 * @ORM\Table(name="tournament")
 */
class Tournament {
    
     /**
     * @ORM\Column(name="id",type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
     /**
     * @ORM\Column(name="name",type="string",length=255)
     * @Assert\NotBlank()
     */
    private $name;
    
     /**
     * @ORM\Column(name="hom_number",type="integer", nullable=true)
     */
    private $homNumber;
    
     /**
     * @ORM\Column(name="start_date",type="date", nullable=true)
     * @Assert\Type("\DateTime")
     */
    private $startDate;
    
     /**
     * @ORM\Column(name="end_date",type="date", nullable=true)
     */
    private $endDate;
    
     /**
     * @ORM\Column(name="nb_rounds",type="integer", nullable=true)
     * @Assert\Type("integer")
     */
    private $nbRounds;
    
    /**
    * @ORM\Column(name="time_control_type",type="integer", nullable=true)
    */
    private $timeControlType;
    
     /**
     * @ORM\Column(name="time_control",type="string",length=255, nullable=true)
     */
    private $timeControl;
    
     /**
     * @ORM\OneToMany(targetEntity="Round", mappedBy="tournament")
     */
    private $rounds;
    
    /**
     * @ORM\OneToMany(targetEntity="Player", mappedBy="tournament", cascade={"persist", "remove"})
     * @ORM\OrderBy({"ranking" = "ASC"})
     */
    private $players;
    
     /**
     * @ORM\OneToOne(targetEntity="Round")
     */
    private $currentRound;
   
    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->rounds = new ArrayCollection();
    }
    
    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param string $name
     * @return Tournament
     */ 
    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
    }
    
    /**
     * @return string
     */ 
    public function getName()
    {
        return $this->name;
    }
    
     /**
     * @param integer $homNumber
     * @return Tournament
     */ 
    public function setHomNumber($homNumber)
    {
        $this->homNumber = $homNumber;
        
        return $this;
    }
    
    /**
     * @return integer
     */ 
    public function getHomNumber()
    {
        return $this->homNumber;
    }
    
      /**
     * @param date $startDate
     * @return Tournament
     */ 
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
        
        return $this;
    }
    
    /**
     * @return string
     */ 
    public function getStartDate()
    {
        return $this->startDate;
    }
    
    /**
     * @param integer $nbRounds
     * @return Tournament
    */ 
    public function setNbRounds($nbRounds)
    {
        $this->nbRounds = $nbRounds;
        
        return $this;
    }
    
    /**
     * @return integer
     */ 
    public function getNbRounds()
    {
        return $this->nbRounds;
    }
    
      /**
     * @param integer $timeControlType
     * @return Tournament
     */ 
    public function setTimeControlType($timeControlType)
    {
        $this->timeControlType = $timeControlType;
        
        return $this;
    }
    
    /**
     * @return string
     */ 
    public function getTimeControlType()
    {
        return $this->timeControlType;
    }
    
      /**
     * @param string $timeControl
     * @return Tournament
     */ 
    public function setTimeControl($timeControl)
    {
        $this->timeControl = $timeControl;
        
        return $this;
    }
    
    /**
     * @return string
     */ 
    public function getTimeControl()
    {
        return $this->timeControl;
    }
    
     /**
     * @return entities
     */ 
    public function getPlayers()
    { 
        return $this->players;
    }
    
      /**
     * @return integer
     */ 
    public function getRounds()
    {
        return $this->rounds;
    }
    
    
    /**
     * @return integer
     */ 
    public function getRound($number)
    {
        return $this->rounds[$number-1];
    }
     /**
     * @param Round $round
     * @return Tournament
    */ 
    public function setCurrentRound($round)
    {
        $this->currentRound = $round;
        
        return $this;
    }
    
    /**
     * @return integer
     */ 
    public function getCurrentRound()
    {
        return $this->currentRound;
    }
}
