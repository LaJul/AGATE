<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Tournament;
use App\Entity\Game;

class GameController extends Controller
{
   /**
     * @Route("/tournaments/{tournament}/games/{game}", name="set_game_result")
     */
    public function setGameResult(Request $request, Tournament $tournament, Game $game)
    {
        $em = $this->get('doctrine')->getManager(); 

        $game->setResult($request->query->get('result'));
        
        $em->persist($game);
        $em->flush();
        
        return $this->redirectToRoute('tournaments_show', array('tournament' => $tournament->getId()));
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
