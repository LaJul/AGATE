<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Tournament;
use App\Entity\Game;

class GameController extends AbstractController
{
   /**
     * @Route("/tournaments/{tournament_id}/games/{game_id}", name="set_game_result")
     */
    public function setGameResult(Request $request, int $tournament_id, int $game_id)
    {
        $em = $this->get('doctrine')->getManager(); 

        $tournament = $em->getRepository(Tournament::class)->find($tournament_id);
        $game = $em->getRepository(Game::class)->find($game_id);
        
        $game->setResult($request->query->get('result'));
        
        $em->persist($game);
        $em->flush();
        
        return $this->redirectToRoute('tournaments_show', array('tournament_id' => $tournament->getId()));
    }
    
     /**
     * @Route("/tournaments/{tournament}/games/{game}", name="game_destroy")
     */
    public function unpairGame(Request $request, Tournament $tournament, Game $game)
    {
        $em = $this->get('doctrine')->getManager(); 

        $em->remove($game);
        
        $em->persist($game);
        $em->flush();
        
        return $this->redirectToRoute('tournaments_show', array('tournament' => $tournament->getId()));
    }
   
}
