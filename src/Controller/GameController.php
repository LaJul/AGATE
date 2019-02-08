<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Tournament;
use App\Entity\Game;

use App\Service\SwissPairingCalculator;

class GameController extends AbstractController
{
   /**
     * @Route("/tournaments/{tournament_slug}/rounds/{round_number}/games/{game_number}", name="set_game_result")
     */
    public function setGameResult(Request $request, string $tournament_slug, int $round_number, int $game_number, SwissPairingCalculator $spc)
    {
        $em = $this->get('doctrine')->getManager(); 

        $tournament = $em->getRepository(Tournament::class)->findOneBySlug($tournament_slug);
        $round = $tournament->getRound($round_number);
        $game = $round->getGame($game_number);
        
        $spc->setGameResult($game, $request->query->get('result'));
        
        $em->persist($game);
        $em->flush();
        
        return $this->redirectToRoute('round_show', array('tournament_slug' => $tournament->getSlug(), 'round_number' => $round->getNumber()));
    }
    
     /**
     * @Route("/tournaments/{tournament_slug}/games/{game}", name="game_destroy")
     */
    public function unpairGame(string $tournament_slug, Game $game)
    {
        $em = $this->get('doctrine')->getManager(); 

        $em->remove($game);
        
        $em->persist($game);
        $em->flush();
        
        return $this->redirectToRoute('tournaments_show', array('tournament_slug' => $tournament_slug));
    }
   
}
