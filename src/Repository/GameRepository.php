<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Description of GameRepository
 *
 * @author Julien Favarel
 */
class GameRepository extends EntityRepository {

    public function getGame($player1, $player2)
    {
        $qb = $this->createQueryBuilder('g');
        
        $qb->where($qb->expr()->orX(
            $qb->expr()->andX($qb->expr()->eq('g.white', ':player1'),$qb->expr()->eq('g.black', ':player2')),
            $qb->expr()->andX($qb->expr()->eq('g.white', ':player2'),$qb->expr()->eq('g.black', ':player1'))
        ))
            ->setParameter('player1', $player1)
            ->setParameter('player2', $player2); 

        return $qb->getQuery()->getResult();
    }
    
    public function getGames($player, $roundNumber = null)
    {
        $qb = $this->createQueryBuilder('g');
        
        $qb->where($qb->expr()->orX(
            $qb->expr()->eq('g.white', ':player')),
            $qb->expr()->eq('g.black', ':player'))
            ->join('g.round', 'r')
            ->orderBy('r.number', 'ASC')
            ->setParameter('player', $player);

        if ($roundNumber != null) {
            $qb->andWhere($qb->expr()->lt('r.number', ':roundNumber'))
            ->setParameter('roundNumber', $roundNumber);
        }
        
        return $qb->getQuery()->getResult();
    }
    
    public function getLastGameColour($player)
    {
        $qb = $this->createQueryBuilder('g');
        
        $qb->where($qb->expr()->orX(
            $qb->expr()->eq('g.white', ':player')),
            $qb->expr()->eq('g.black', ':player'))
            ->join('g.round', 'r')
            ->orderBy('r.number', 'DESC')
            ->setMaxResults(1)
            ->setParameter('player', $player);

        $lastGame = $qb->getQuery()->getOneOrNullResult();

        if ($lastGame!= null && $lastGame->white == $player)
        {
            return 0;
        }
        else {
            return 1;
        }
    }

    public function getWonGames($player, $roundNumber = null)
    {
        $qb = $this->createQueryBuilder('g');
        
          $qb->where($qb->expr()->orX(
            $qb->expr()->andX($qb->expr()->eq('g.white', ':player'),$qb->expr()->eq('g.result', '1-0')),
            $qb->expr()->andX($qb->expr()->eq('g.black', ':player'),$qb->expr()->eq('g.result', '0-1'))
        ))
            ->setParameter('player', $player);

        if ($roundNumber != null) {
            $qb->andWhere($qb->expr()->lt('r.number', ':roundNumber'))
            ->setParameter('roundNumber', $roundNumber);
        }
          
        return $qb->getQuery()->getResult();
    }
    
    public function getWonGamesByForfeit($player, $roundNumber = null)
    {
        $qb = $this->createQueryBuilder('g');
        
          $qb->where($qb->expr()->orX(
            $qb->expr()->andX($qb->expr()->eq('g.white', ':player'),$qb->expr()->eq('g.result', ':result1')),
            $qb->expr()->andX($qb->expr()->eq('g.black', ':player'),$qb->expr()->eq('g.result', ':result2'))
        ))
            ->setParameter('result1', '1-F')
            ->setParameter('result2', 'F-1')

            ->setParameter('player', $player);

        if ($roundNumber != null) {
            $qb->andWhere($qb->expr()->lt('r.number', ':roundNumber'))
            ->setParameter('roundNumber', $roundNumber);
        }
          
        return $qb->getQuery()->getResult();
    }
    
    public function getDrawnGames($player, $roundNumber = null)
    {
        $qb = $this->createQueryBuilder('g');
        
        $qb->where($qb->expr()->orX(
            $qb->expr()->andX($qb->expr()->eq('g.white', ':player'),$qb->expr()->eq('g.result', ':result')),
            $qb->expr()->andX($qb->expr()->eq('g.black', ':player'),$qb->expr()->eq('g.result', ':result'))
        ))
            ->setParameter('result', 'X-X')
            ->setParameter('player', $player);
          
        if ($roundNumber != null) {
            $qb->andWhere($qb->expr()->lt('r.number', ':roundNumber'))
            ->setParameter('roundNumber', $roundNumber);
        }
        
        return $qb->getQuery()->getResult();
    }
    
    public function getLostGames($player, $roundNumber = null)
    {
        $qb = $this->createQueryBuilder('g');
        
          $qb->where($qb->expr()->orX(
            $qb->expr()->andX($qb->expr()->eq('g.white', ':player'),$qb->expr()->eq('g.result', '0-1')),
            $qb->expr()->andX($qb->expr()->eq('g.black', ':player'),$qb->expr()->eq('g.result', '1-0'))
        ))
            ->setParameter('player', $player);

        if ($roundNumber != null) {
            $qb->andWhere($qb->expr()->lt('r.number', ':roundNumber'))
            ->setParameter('roundNumber', $roundNumber);
        }
          
        return $qb->getQuery()->getResult();
    }
    
}
