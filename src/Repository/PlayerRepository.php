<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Description of PlayerRepository
 *
 * @author Julien Favarel
 */
class PlayerRepository extends EntityRepository {
    
    public function getAllActivePlayers($tournament)
    {
        $qb = $this->createQueryBuilder('p');
        
        $qb/*->addSelect('COUNT(ww.id) + COUNT(bw.id) AS wins')
            ->addSelect('COUNT(wd.id) + COUNT(bd.id)AS draws')
            ->addSelect('COUNT(wl.id) + COUNT(bl.id)AS losses')*/
            ->where('p.isActive = true')
            ->andWhere('p.tournament = :tournament')
            /*->leftJoin('p.whiteGames', 'ww', 'WITH', 'ww.result = \'1-0\'') 
            ->leftJoin('p.blackGames', 'bw', 'WITH', 'bw.result = \'0-1\'') 
            ->leftJoin('p.whiteGames', 'wd', 'WITH', 'wd.result = \'X-X\'') 
            ->leftJoin('p.blackGames', 'bd', 'WITH', 'bd.result = \'X-X\'') 
            ->leftJoin('p.whiteGames', 'wl', 'WITH', 'wl.result = \'0-1\'') 
            ->leftJoin('p.blackGames', 'bl', 'WITH', 'bl.result = \'1-0\'') */
            ->groupBy('p')
            //->orderBy('p.pairingNumber', 'ASC')
            ->setParameter('tournament', $tournament); 

        return $qb->getQuery()->getResult();
    }
}
