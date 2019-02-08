<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description of Tiebreak
 *
 * @author Julien Favarel
 * 
 * @ORM\Entity
 * @ORM\Table(name="tiebreak")
 */

class Tiebreak {
    
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
     * @ORM\Column(name="shortName",type="string",length=255)
     * @Assert\NotBlank()
     */
    private $shortName;
    
    public function __construct($name, $shortName)
    {
        $this->name = $name;
        $this->shortName = $shortName;
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
    public function getShortName() {
        return $this->shortName;
    }
}