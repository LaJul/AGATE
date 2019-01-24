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
     * @ORM\OneToMany(targetEntity="Affiliate", mappedBy="club")
     */
    private $affiliates;
}
