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
 * Description of Player
 *
 * @author Julien Favarel
 * 
 * @ORM\Table(name="player") 
 * @ORM\Entity(repositoryClass="App\Repository\PlayerRepository")
 */
class Player {

    /**
     * @ORM\Column(name="id",type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
    * @ORM\ManyToOne(targetEntity="Tournament", inversedBy="players")
    */
    private $tournament;
    
     /**
     * @ORM\Column(name="name",type="string",length=255)
     */
    private $name;
    
    /**
     * @ORM\Column(name="title",type="string",length=3, nullable=true)
     */
    private $title;
    
    /**
    * @ORM\Column(name="gender",type="string", length=1, nullable=true)
    */
    private $gender;
    
    /**
     * @ORM\Column(name="rating",type="integer")
     */
    private $rating;
    
    /**
     * @ORM\Column(name="rating_type", type="string", length=1, nullable=true)
     */
    private $ratingType;
    	 
    /**
    * @ORM\Column(name="pairing_number",type="integer", nullable=true)
    */
    private $pairingNumber;
    
    /**
     * @ORM\Column(name="points",type="integer")
     */
    private $points;
    
     /**
     * @ORM\Column(name="is_active",type="boolean")
     */
    private $isActive;
   
      /**
    * @ORM\OneToMany(targetEntity="Game", mappedBy="white")
    */
    private $whiteGames;
    
    /**
    * @ORM\OneToMany(targetEntity="Game", mappedBy="black")
    */
    private $blackGames;
    
    private $colourPreference;
    
    /**
     * @param string $name
     * @return Player
     */ 
    public function __construct($tournament, $affiliate = null)
    {
        if ($affiliate != null)
        {
            $this->gender = $affiliate->getGender();
            $this->title = $affiliate->getTitle();
            $this->name = $affiliate->getName();
            
            switch ($tournament->getTimeControlType()){
            case 0:
                $this->rating = $affiliate->getRating();
                $this->ratingType = $affiliate->getRatingType();
                break;
            case 1:
                $this->rating = $affiliate->getRapid();
                $this->ratingType = $affiliate->getRapidType();
                break;
            case 2:
                $this->rating = $affiliate->getBlitz();
                $this->ratingType = $affiliate->getBlitzType();
                break;
            }
        }
      
        $this->points = 0;
        $this->isActive = true;
        $this->tournament = $tournament;
        $this->whiteGames = new ArrayCollection();
        $this->blackGames = new ArrayCollection();
    }
    
     /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param string $gender
     * @return Player
     */ 
    public function setGender($gender)
    {
        $this->gender = $gender;
        
        return $this;
    }
    
    /**
     * @return string
     */ 
    public function getGender()
    {
        return $this->gender;
    }
    
    /**
     * @param string $name
     * @return Player
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
     * @param string $pairingNumber
     * @return Player
     */ 
    public function setPairingNumber($pairingNumber)
    {
        $this->pairingNumber = $pairingNumber;
        
        return $this;
    }
    
     /**
     * @return integer
     */ 
    public function getPairingNumber()
    {
        return $this->pairingNumber;
    }
    
     /**
     * @param string $title
     * @return Player
     */ 
    public function setTitle($title)
    {
        $this->title = $title;
        
        return $this;
    }
    
    /**
     * @return title
     */ 
    public function getTitle()
    {
        return $this->title;
    }
    
     /**
     * @param integer $rating
     * @return Player
     */ 
    public function setRating($rating)
    {
        $this->rating = $rating;
        
        return $this;
    }
    
    /**
     * @return integer
     */ 
    public function getRating()
    {
        return $this->rating;
    }
   
       /**
     * @param string $colourPreference
     * @return Player
     */ 
    public function setColourPreference($colourPreference)
    {
        $this->colourPreference = $colourPreference;
        
        return $this;
    }
    
    /**
     * @return string
     */ 
    public function getColourPreference()
    {
        return $this->colourPreference;
    }
    
      /**
     * @return float
     */ 
    public function getColourDifference()
    {
        return count($this->whiteGames) - count($this->blackGames);
    }
    
     /**
     * @return boolean
     */ 
    public function isPaired()
    {
        return (count($this->whiteGames) + count($this->blackGames)) == $this->getTournament()->getCurrentRound();
    }
    
    public function getNbWins()
    {
        return count($this->getWhiteWins()) + count($this->getBlackWins());
    }
    
    public function getNbDraws()
    {
        return count($this->getWhiteDraws()) + count($this->getBlackDraws());
    }
    
     /**
     * @return float
     */ 
    public function getPoints()
    {
        return (float) $this->getNbWins() * 1 + $this->getNbDraws() * 0.5;
    }
    
    public function floatedUp()
    {
        return $this->whiteGames->filter(function($game) {
            return $game->getWhiteFloat() == 'UP';
        });
    }
    
    public function floatedDown()
    {
        return $this->whiteGames->filter(function($game) {
            return $game->getWhiteFloat() == 'DOWN';
        });
    }
    
    
    private function getWhiteWins()
    {
        return $this->whiteGames->filter(function($game) {
            return $game->getResult() == '1-0';
        });
    }
    
    private function getBlackWins()
    {
        return $this->blackGames->filter(function($game) {
            return $game->getResult() == '0-1';
        });
    }
    
     private function getWhiteDraws()
    {
        return $this->whiteGames->filter(function($game) {
            return $game->getResult() == 'X-X';
        });
    }
    
    private function getBlackDraws()
    {
        return $this->blackGames->filter(function($game) {
            return $game->getResult() == 'X-X';
        });
    }
    
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name."(".$this->pairingNumber.") ".$this->rating." "." : ".$this->getPoints();
    }
}
