<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

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
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(name="slug",type="string",length=255, unique=true)
     */
    private $slug;

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
     * @ORM\OneToMany(targetEntity="Round", mappedBy="tournament", cascade={"persist"})
     * @ORM\OrderBy({"number" = "ASC"})
     */
    private $rounds;

    /**
     * @ORM\OneToMany(targetEntity="Player", mappedBy="tournament", cascade={"persist"})
     * @ORM\OrderBy({"pairingNumber" = "ASC", "rating" = "DESC"})
     */
    private $players;

    /**
     * @ORM\ManyToMany(targetEntity="Tiebreak")
     */
    private $tiebreaks;

    public function __construct() {
        $this->players = new ArrayCollection();
        $this->rounds = new ArrayCollection();
        $this->tiebreaks = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param string $name
     * @return Tournament
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $slug
     * @return Tournament
     */
    public function setSlug($slug) {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug() {
        return $this->slug;
    }

    /**
     * @param integer $homNumber
     * @return Tournament
     */
    public function setHomNumber($homNumber) {
        $this->homNumber = $homNumber;

        return $this;
    }

    /**
     * @return integer
     */
    public function getHomNumber() {
        return $this->homNumber;
    }

    /**
     * @param date $startDate
     * @return Tournament
     */
    public function setStartDate($startDate) {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getStartDate() {
        return $this->startDate;
    }

    /**
     * @param date $endDate
     * @return Tournament
     */
    public function setEndDate($endDate) {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getEndDate() {
        return $this->endDate;
    }

    /**
     * @param integer $nbRounds
     * @return Tournament
     */
    public function setNbRounds($nbRounds) {
        $this->nbRounds = $nbRounds;

        return $this;
    }

    /**
     * @return integer
     */
    public function getNbRounds() {
        return $this->nbRounds;
    }

    /**
     * @param integer $timeControlType
     * @return Tournament
     */
    public function setTimeControlType($timeControlType) {
        $this->timeControlType = $timeControlType;

        return $this;
    }

    /**
     * @return string
     */
    public function getTimeControlType() {
        return $this->timeControlType;
    }

    /**
     * @param string $timeControl
     * @return Tournament
     */
    public function setTimeControl($timeControl) {
        $this->timeControl = $timeControl;

        return $this;
    }

    /**
     * @return string
     */
    public function getTimeControl() {
        return $this->timeControl;
    }

    /**
     * @return integer
     */
    public function addTiebreak($tiebreak) {
        $this->tiebreaks->add($tiebreak);
        return $this;
    }

    /**
     * @return integer
     */
    public function getTiebreaks() {
        return $this->tiebreaks;
    }

    /**
     * @return integer
     */
    public function addRound($round) {
        $this->rounds->add($round);
        return $this;
    }

    /**
     * @return integer
     */
    public function getRounds() {
        return $this->rounds;
    }

    /**
     * @return integer
     */
    public function getRound($number) {
        return $this->rounds[$number - 1];
    }

    /**
     * @return integer
     */
    public function getCurrentRound() {
        return $this->rounds->filter(function($round) {
                    return !$round->isOver();
                })->first();
    }

    /**
     * @return integer
     */
    public function addPlayer($affiliate = null) {

        $player = new Player($affiliate);

        if ($affiliate != null) {

            switch ($this->getTimeControlType()) {
                case 0:
                    $player->setRating($affiliate->getRating());
                    $player->setRatingType($affiliate->getRatingType());
                    break;
                case 1:
                    $player->setRating($affiliate->getRapid());
                    $player->setRatingType($affiliate->getRapidType());
                    break;
                case 2:
                    $player->setRating($affiliate->getBlitz());
                    $player->setRatingType($affiliate->getBlitzType());
                    break;
            }
        }
        $player->setTournament($this);

        $this->players->add($player);

        $this->rounds->get(0)->add($player);

        return $player;
    }

    /**
     * @return entities
     */
    public function getPlayers() {
        return $this->players;
    }

    /**
     * @return this
     */
    public function remove(player $player, $roundNumber) {

        foreach ($this->rounds as $round) {
            if ($round->getNumber >= $roundNumber) {
                $round->tournamentOutPlayers->add($player);
            }
        }
    }

}
