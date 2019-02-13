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
     * @ORM\JoinColumn(name="tournament_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $tournament;

    /**
     * @ORM\Column(name="lastName",type="string",length=255)
     */
    private $lastName;

    /**
     * @ORM\Column(name="firstName",type="string",length=255)
     */
    private $firstName;

    /**
     * @ORM\Column(name="title",type="string",length=3, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(name="category", type="string", length=3, nullable=true)
     */
    private $category;

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
     * @ORM\Column(name="club",type="string", nullable=true)
     */
    private $club;

    /**
     * @ORM\Column(name="league",type="string", length=3, nullable=true)
     */
    private $league;

    /**
     * @ORM\Column(name="federation",type="string", length=3, nullable=true)
     */
    private $federation;

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
    public function __construct($affiliate = null) {
        if ($affiliate != null) {

            $this->title = $affiliate->getTitle();
            $this->lastName = $affiliate->getLastName();
            $this->firstName = $affiliate->getFirstName();

            $this->gender = $affiliate->getGender();
            $this->category = $this->setCategory($affiliate->getBirthDate());

            $this->club = $affiliate->getClub()->getName();
            $this->league = $affiliate->getClub()->getLeague();
            $this->federation = $affiliate->getClub()->getFederation();
        }

        $this->points = 0;
        $this->isActive = true;

        $this->whiteGames = new ArrayCollection();
        $this->blackGames = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId() {
        return $this->id;
    }
    
    public function getTournament() {
        return $this->tournament;
    }
    
     /**
     * @param Tournament $tournament
     * @return Player
     */
    public function setTournament($tournament) {
        $this->tournament = $tournament;
        
        return $this;
    }

    
    /**
     * @param string $gender
     * @return Player
     */
    public function setGender($gender) {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return string
     */
    public function getGender() {
        return $this->gender;
    }

    /**
     * @return string
     */
    public function getCategory() {
        return $this->category;
    }

    /**
     * @param string $lastName
     * @return Player
     */
    public function setLastName($lastName) {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName() {
        return $this->lastName;
    }

    /**
     * @param string $firstName
     * @return Player
     */
    public function setFirstName($firstName) {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName() {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getFullName() {
        return $this->lastName . " " . $this->firstName;
    }

    /**
     * @param string $pairingNumber
     * @return Player
     */
    public function setPairingNumber($pairingNumber) {
        $this->pairingNumber = $pairingNumber;

        return $this;
    }

    /**
     * @return integer
     */
    public function getPairingNumber() {
        return $this->pairingNumber;
    }

    /**
     * @param string $title
     * @return Player
     */
    public function setTitle($title) {
        $this->title = $title;

        return $this;
    }

    /**
     * @return title
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @param integer $rating
     * @return Player
     */
    public function setRating($rating) {
        $this->rating = $rating;

        return $this;
    }

    /**
     * @return integer
     */
    public function getRating() {
        return $this->rating;
    }

    /**
     * @param integer $ratingType
     * @return Player
     */
    public function setRatingType($ratingType) {
        $this->ratingType = $ratingType;

        return $this;
    }

    /**
     * @return string
     */
    public function getRatingType() {
        return $this->ratingType;
    }

    /**
     * @return string
     */
    public function getClub() {
        return $this->club;
    }

    /**
     * @return string
     */
    public function getLeague() {
        return $this->league;
    }

    /**
     * @return string
     */
    public function getFederation() {
        return $this->federation;
    }

    /**
     * @param string $colourPreference
     * @return Player
     */
    public function setColourPreference($colourPreference) {
        $this->colourPreference = $colourPreference;

        return $this;
    }

    /**
     * @return string
     */
    public function getColourPreference() {
        return $this->colourPreference;
    }

    /**
     * @return float
     */
    public function getColourDifference() {
        return count($this->whiteGames) - count($this->blackGames);
    }

    /**
     * @return boolean
     */
    public function isPaired() {
        return (count($this->whiteGames) + count($this->blackGames)) == $this->getTournament()->getCurrentRound();
    }
    
    public function isTopscorer() {
        
        $nbGames = $this->getNbGames();
       
        return $nbGames != 0 ? $this->points / $nbGames > 0.5 : false;
    }
    
    public function getWhiteGames() {
        return $this->whiteGames;
    }

    public function getBlackGames() {
        return $this->blackGames;
    }

    public function getGames() {
        return new ArrayCollection(array_merge($this->whiteGames->toArray(), $this->blackGames->toArray()));
    }
    
    public function getNbGames()
    {
        return count($this->whiteGames) + count($this->blackGames);
    }

    public function getNbWins() {
        return count($this->getWhiteWins()) + count($this->getBlackWins());
    }

    public function getNbDraws() {
        return count($this->getWhiteDraws()) + count($this->getBlackDraws());
    }

    /**
     * @return float
     */
    public function getPoints() {
        return (float) $this->getNbWins() * 1 + $this->getNbDraws() * 0.5;
    }

    public function getResults() {
        // 1 -> result, opponent ranking, colour

        $results = array();

        foreach ($this->whiteGames as $game) {

            switch ($game->getResult()) {
                case "1-0" :
                    $result = "+";
                    break;
                case "X-X" :
                    $result = "=";
                    break;
                case "0-1" :
                    $result = "-";
                    break;
                case "1-F" :
                    $result = ">";
                    break;
                case "F-1" :
                    $result = "<";
                    break;
            }

            array_push($results, array("result" => $result, "opponent" => $game->getBlack()->getRanking(), "colour" => "B"));
        }

        foreach ($this->blackGames as $game) {

            switch ($game->getResult()) {
                case "1-0" :
                    $result = "-";
                    break;
                case "X-X" :
                    $result = "=";
                    break;
                case "0-1" :
                    $result = "+";
                    break;
                case "1-F" :
                    $result = "<";
                    break;
                case "F-1" :
                    $result = ">";
                    break;
            }

            array_push($results, array("result" => $result, "opponent" => $game->getWhite()->getRanking(), "colour" => "N"));
        }

        return $results;
    }

    public function floatedUp() {
        return $this->whiteGames->filter(function($game) {
                    return $game->getWhiteFloat() == 'UP';
                });
    }

    public function floatedDown() {
        return $this->whiteGames->filter(function($game) {
                    return $game->getWhiteFloat() == 'DOWN';
                });
    }

    /**
     * @return string
     */
    public function __toString() {
        return "(" . $this->pairingNumber . ") " . $this->lastName . " " . $this->firstName . " " . $this->rating . " : " . $this->getPoints();
    }

    private function setCategory(\DateTime $birthDate) {

        $today = new \DateTime();


        if ($today->format('n') >= 9 && $today->format('n') <= 12) {
            $date = new \DateTime("01-01-" . $today->format('y'));
        } else {
            $year = $today->format('y') - 1;

            $date = new \DateTime("01-01-" . $year);
        }

        $age = $birthDate->diff($date)->y;

        if ($age < 8) {
            return "Ppo";
        }
        if ($age == 8 || $age == 9) {
            return "Pou";
        }
        if ($age == 10 || $age == 11) {
            return "Pup";
        }
        if ($age == 12 || $age == 13) {
            return "Ben";
        }
        if ($age == 14 || $age == 15) {
            return "Min";
        }
        if ($age == 16 || $age == 17) {
            return "Cad";
        }
        if ($age == 18 || $age == 19) {
            return "Jun";
        }
        if ($age >= 20 && $age <= 49) {
            return "Sen";
        }
        if ($age >= 50 && $age <= 64) {
            return "Sep";
        }
        if ($age > 65) {
            return "Vet";
        }
    }

    private function getWins() {
        return new ArrayCollection(array_merge($this->getWhiteWins()->toArray(), $this->getBlackWins()->toArray()));
    }

    private function getDraws() {
        return new ArrayCollection(array_merge($this->getWhiteDraws()->toArray(), $this->getBlackDraws()->toArray()));
    }

    private function getWhiteWins() {
        return $this->whiteGames->filter(function($game) {
                    return $game->getResult() == '1-0' || $game->getResult() == '1-F';
                });
    }
    
    private function getBlackWins() {
        return $this->blackGames->filter(function($game) {
                    return $game->getResult() == '0-1' || $game->getResult() == 'F-1';
                });
    }

    private function getWhiteDraws() {
        return $this->whiteGames->filter(function($game) {
                    return $game->getResult() == 'X-X';
                });
    }

    private function getBlackDraws() {
        return $this->blackGames->filter(function($game) {
                    return $game->getResult() == 'X-X';
                });
    }

}
