<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Game
 *
 * @author Julien Favarel
 * 
 * @ORM\Entity
 * @ORM\Table(name="game")
 * @ORM\Entity(repositoryClass="App\Repository\GameRepository")
 */
class Game {

    public static $NO_RESULT = "";
    public static $WHITE_WINS = "1-0";
    public static $DRAW = "X-X";
    public static $BLACK_WINS = "0-1";
    public static $WHITE_WINS_BY_FORFAIT = "1-F";
    public static $BLACK_WINS_BY_FORFAIT = "F-1";
    public static $DOUBLE_LOSS = "0-0";
   
    
     /**
     * @ORM\Column(name="id",type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
    * @ORM\ManyToOne(targetEntity="Round", inversedBy="games")
    */
    private $round;
    
     /**
     * @ORM\Column(name="number",type="integer")
     */
    private $number;
    
     /**
     * @ORM\ManyToOne(targetEntity="Player", inversedBy="whiteGames")
     */
    private $white;
   
    /**
     * @ORM\Column(name="whitePoints",type="float")
     */
    private $whitePoints;
    
       /**
     * @ORM\Column(name="$whiteFloat",type="string", nullable=true)
     */
    private $whiteFloat;
    
     /**
     * @ORM\ManyToOne(targetEntity="Player", inversedBy="blackGames")
     */
    private $black;
    
     /**
     * @ORM\Column(name="blackPoints",type="float")
     */
    private $blackPoints;
    
    
      /**
     * @ORM\Column(name="$blackFloat",type="string", nullable=true)
     */
    private $blackFloat;
    
     /**
     * @ORM\Column(name="result",type="string",length=3, nullable=true)
     */
    private $result;
    
      /**
     * @ORM\Column(name="$isResultLocked",type="string")
     */
    //private $isResultLocked;
    
     /**
     * @ORM\Column(name="pgn",type="string",length=3, nullable=true)
     */
    private $pgn;
    
     /**
     * @param string $name
     * @return Player
     */ 
    public function __construct($round, $number, $white, $black)
    {
        $this->round = $round;
        $this->number = $number;
        $this->white = $white;
        $this->whitePoints = $white->getPoints();
        $this->black = $black;
        $this->blackPoints = $black->getPoints();
        $this->result = "";
    }
    
    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    
     /**
     * @return string
     */ 
    public function getNumber()
    {
        return $this->number;
    }
    
     /**
     * @return string
     */ 
    public function getWhite()
    {
        return $this->white;
    }
    
    /**
     * @return integer
     */ 
    public function getWhitePoints()
    {
        return $this->whitePoints;
    }
    
     /**
     * @return string
     */ 
    public function getBlack()
    {
        return $this->black;
    }
    
    /**
     * @return integer
     */ 
    public function getBlackPoints()
    {
        return $this->blackPoints;
    }
    
    /**
     * @return Game
     */ 
    public function permute()
    {
        $player = $this->white;
        
        $this->white = $this->black;
        $this->whitePoints = $this->white->getPoints();
        $this->black = $player;
        $this->blackPoints = $this->black->getPoints();
        
        return $this;
    }
    
      /**
     * @param string $result
     * @return Game
     */ 
    public function setResult($result)
    {
        $this->result = $result;
        
        return $this;
    }
    
      /**
     * @return string
     */ 
    public function getResult()
    {
        return $this->result;
    }
}
