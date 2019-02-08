<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Club
 *
 * @author Julien Favarel
 * 
 * @ORM\Entity
 * @ORM\Table(name="club")
 */
class Club {

     /**
     * @ORM\Column(name="id",type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="name",type="string",length=255)
     */
    private $name;
    
     /**
     * @ORM\Column(name="league",type="string",length=255)
     */
    private $league;
    
     
     /**
     * @ORM\Column(name="federation",type="string",length=255)
     */
    private $federation;
    
    /**
     * @ORM\OneToMany(targetEntity="Affiliate", mappedBy="club")
     */
    private $affiliates;
    
    public function __construct($name, $league, $federation)
    {
        $this->name = $name;
        $this->league = $league;
        $this->federation = $federation;
    }
      
    /**
     * @return string
     */
    public function getName() {
        return $this->name;
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
    
}
