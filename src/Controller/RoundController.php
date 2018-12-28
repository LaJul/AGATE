<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use App\Entity\Tournament;
use App\Entity\Round;
use App\Service\SwissManager;

class RoundController extends Controller
{
     /**
     * @Route("/tournaments/{tournament}/rounds/{round}", name="round_show", requirements={"round"="\d+"})
     * @Method("GET")
     */
    public function getRound(Tournament $tournament, Round $round)
    {     
        return $this->render("fast_tournament.html.twig", array('tournament' => $tournament, 'round' => $round));
    }
    
    /**
    * @Route("/tournaments/{tournament}/rounds/", name="round_create")
    * @Method("GET")
    */
    public function createRound(Tournament $tournament, SwissManager $swissManager)
    {
        $round = $swissManager->createRound($tournament);
        
        return $this->redirectToRoute('round_show', array('tournament' => $tournament->getId(), 'round' => $round->getId()));
    }
    
    /**
    * @Route("/tournaments/{tournament}/rounds/pair", name="round_pair")
    * @Method("GET")
    */
    public function pairRound(Tournament $tournament, SwissManager $swissManager)
    {
        $swissManager->pairRound($tournament);

        return $this->redirectToRoute('round_show', array('tournament' => $tournament->getId(), 'round' => $tournament->getCurrentRound()->getId()));
    }
    
    /**
    * @Route("/tournaments/{tournament}/rounds/unpair", name="round_unpair")
    * @Method("GET")
    */
    public function unpairRound(Tournament $tournament, SwissManager $swissManager)
    {
        $swissManager->unpairRound($tournament);
        
        return $this->redirectToRoute('round_show', array('tournament' => $tournament->getId(), 'round' => $tournament->getCurrentRound()->getId()));

    }
}
