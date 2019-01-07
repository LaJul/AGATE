<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Entity\Tournament;
use App\Service\SwissManager;

class RoundController extends AbstractController
{
     /**
     * @Route("/tournaments/{tournament_id}/rounds/{round_number}", name="round_show", requirements={"round_number"="\d+"}, methods={"GET"})
     */
    public function getRound(int $tournament_id, int $round_number)
    {     
        $em = $this->get('doctrine')->getManager();
        
        $tournament = $em->getRepository(Tournament::class)->find($tournament_id);  
        $round = $tournament->getRound($round_number);  
        
        return $this->render("fast_tournament.html.twig", array('tournament' => $tournament, 'round' => $round));
    }
    
    /**
    * @Route("/tournaments/{tournament_id}/rounds/", name="round_create", methods={"GET"})
    */
    public function createRound(int $tournament_id, SwissManager $swissManager)
    {
        $em = $this->get('doctrine')->getManager();
        
        $tournament = $em->getRepository(Tournament::class)->find($tournament_id);  
        
        $round = $swissManager->createRound($tournament);
        
        return $this->redirectToRoute('round_show', array('tournament_id' => $tournament->getId(), 'round_number' => $round->getNumber()));
    }
    
    /**
    * @Route("/tournaments/{tournament_id}/rounds/{round_number}/pair", name="round_pair", methods={"GET"})
    */
    public function pairRound(int $tournament_id, int $round_number, SwissManager $swissManager)
    {
        $em = $this->get('doctrine')->getManager();
        
        $tournament = $em->getRepository(Tournament::class)->find($tournament_id);  
        $round = $tournament->getRound($round_number);  

        $swissManager->pairRound($tournament, $round);

        return $this->redirectToRoute('round_show', array('tournament_id' => $tournament->getId(), 'round_number' => $round->getNumber()));
    }
    
    /**
    * @Route("/tournaments/{tournament_id}/rounds/{round_number}/unpair", name="round_unpair", methods={"GET"})
    */
    public function unpairRound(int $tournament_id, int $round_number, SwissManager $swissManager)
    {
        $em = $this->get('doctrine')->getManager();
        
        $tournament = $em->getRepository(Tournament::class)->find($tournament_id);
        $round = $tournament->getRound($round_number);  

        $swissManager->unpairRound($round);
        
        return $this->redirectToRoute('round_show', array('tournament_id' => $tournament->getId(), 'round_number' => $round->getNumber()));

    }
}
