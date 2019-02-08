<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Affiliate
 *
 * @author Julien Favarel
 * 
 * @ORM\Entity
 * @ORM\Table(name="affiliate")
 */
class Affiliate {
 
     /**
     * @ORM\Column(name="id",type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="lastName",type="string", length=255)
     */
    private $lastName;
    
      /**
     * @ORM\Column(name="firstName",type="string", length=255)
     */
    private $firstName;
    
    /**
     * @ORM\Column(name="title",type="string", length=3, nullable=true)
     */
    private $title;
    
     /**
    * @ORM\Column(name="birthDate",type="date")
    */
    private $birthDate;
    
    /**
    * @ORM\Column(name="gender",type="string", length=1)
    */
    private $gender;
    
    /**
     * @ORM\Column(name="is_active",type="boolean")
     */
    private $isActive;
    
    /**
     * @ORM\Column(name="rating",type="integer")
     */
    private $rating;
    
     /**
     * @ORM\Column(name="rating_type", type="string", length=1)
     */
    private $ratingType;
    
     /**
     * @ORM\Column(name="rapid",type="integer", nullable=true)
     */
    private $rapid;
    
     /**
     * @ORM\Column(name="rapid_type", type="string", length=1)
     */
    private $rapidType;
    
    /**
     * @ORM\Column(name="blitz",type="integer", nullable=true)
     */
    private $blitz;
    
     /**
     * @ORM\Column(name="blitz_type", type="string", length=1)
     */
    private $blitzType;
    

    /**
     * @return Affiliate
     */ 
    public function __construct()
    {
        $this->isActive = true;
        
        $this->ratingType = "F";
        $this->rapidType = "F";
        $this->blitzType = "F";
    }
    
         /**
     * @param date $birthDate
     * @return Affiliate
     */ 
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;
        
        return $this;
    }
    
    /**
     * @return string
     */ 
    public function getBirthDate()
    {
        return $this->birthDate;
    }
    
       /**
     * @param integer $gender
     * @return Affiliate
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
     * @param string $lastName
     * @return Affiliate
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
     * @return Affiliate
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
    public function getTitle()
    {
        return $this->title;
    }
    
      /**
     * @param integer $title
     * @return Affiliate
     */ 
    public function setTitle($title)
    {
        $this->title = $title;
        
        return $this;
    }
    
    /**
     * @return string
     */ 
    public function getRating()
    {
        return $this->rating;
    }
    
      /**
     * @param integer $rating
     * @return Affiliate
     */ 
    public function setRating($rating)
    {
        $this->rating = $rating;
        
        return $this;
    }
    
     /**
     * @return string
     */ 
    public function getRatingType()
    {
        return $this->ratingType;
    }
    
     /**
     * @return string
     */ 
    public function getRapid()
    {
        return $this->rapid;
    }
    
      /**
     * @return string
     */ 
    public function getRapidType()
    {
        return $this->rapidType;
    }
    
     /**
     * @return string
     */ 
    public function getBlitz()
    {
        return $this->blitz;
    }
       /**
     * @return string
     */ 
    public function getBlitzType()
    {
        return $this->blitzType;
    }
 
      /**
     * @param integer $club
     * @return Affiliate
     */ 
    public function setClub($club)
    {
        $this->club = $club;
        
        return $this;
    }
    
      /**
     * @return string
     */ 
    public function getClub()
    {
        return $this->club;
    }
    
}
