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
     * @ORM\Column(name="name",type="string", length=255)
     */
    private $name;
    
    /**
     * @ORM\Column(name="title",type="string", length=3, nullable=true)
     */
    private $title;
    
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
     * @ORM\Column(name="rapid",type="integer")
     */
    private $rapid;
    
     /**
     * @ORM\Column(name="rapid_type", type="string", length=1)
     */
    private $rapidType;
    
    /**
     * @ORM\Column(name="blitz",type="integer")
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
        $this->name = "MVL";
        $this->title = "GM";
        $this->gender = "M";
        
        $this->isActive = true;
        
        $this->rating = 2900;
        $this->rapid = 2900;
        $this->blitz = 2900;
        $this->ratingType = "F";
        $this->rapidType = "F";
        $this->blitzType = "F";
    }
    
    /**
     * @return string
     */ 
    public function getGender()
    {
        return $this->gender;
    }
   
    /**
     * @return string
     */ 
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @return string
     */ 
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * @return string
     */ 
    public function getRating()
    {
        return $this->rating;
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
    
}