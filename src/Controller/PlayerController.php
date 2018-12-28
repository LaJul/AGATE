<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Affiliate;
use App\Entity\Tournament;
use App\Entity\Player;

class PlayerController extends Controller
{
    /**
    * @Route("/tournaments/{tournament}/players", name="players_index")
    * @Method("GET")
    */
    public function getPlayers(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        
        $repository = $em->getRepository(Tournament::class);
        $tournaments = $repository->findAll();
        
        return $this->render("index.html.twig", array('tournaments' => $tournaments));
    }
    
    /**
    * @Route("/tournaments/{tournament}/players/{affiliate}", name="player_create_from_affiliate", requirements={"affiliate"="\d+"})
    */
    public function createPlayerFromAffiliate(Tournament $tournament, Affiliate $affiliate)
    {
        $player = new Player($tournament, $affiliate);
        $em = $this->get('doctrine')->getManager();
        $em->persist($player);
        $em->flush();
        
        return $this->redirectToRoute('tournaments_show', array('tournament' => $tournament->getId()));
    }
    
    /**
    * @Route("/tournaments/{tournament}/players/new", name="player_create")
    */
    public function createPlayer(Request $request, Tournament $tournament)
    {
        $nameAndRating = explode(" ", $request->query->get('name-with-rating'));
        
        $player = new Player($tournament);
        $player->setName($nameAndRating[0]);
        $player->setRating(isset($nameAndRating[1]) ? $nameAndRating[1] : 1000);
        
        $em = $this->get('doctrine')->getManager();
        $em->persist($player);
        $em->flush();
        
        return $this->redirectToRoute('tournaments_fast_show', array('tournament' => $tournament->getId()));
    }
 
    /**
    * @Route("/tournaments/{tournament}/players/destroy/{player}", name="player_destroy")
    * @Method("GET")
    */
    public function destroyPlayer(Tournament $tournament, Player $player)
    {
        $em = $this->get('doctrine')->getManager();
        $em->remove($player);
        $em->flush();
        
        return $this->redirectToRoute('tournaments_fast_show', array('tournament' => $tournament->getId()));
    }
    
    /**
    * @Route("/tournaments/{tournament}/players/{player}?action=pairWhite", name="player_pair_white")
    * @Method("GET")
    */
    public function pairWhitePlayer(Tournament $tournament, Player $player)
    {        
        $game = new Game($tournament, $tournament->getCurrentRound(), $player, null);
       
        $em = $this->get('doctrine')->getManager();
        $em->persist($game);
        $em->flush();
        
        return $this->redirectToRoute('tournaments_fast_show', array('tournament' => $tournament->getId()));
    }
    
     /**
    * @Route("/tournaments/{tournament}/players/{player}?action=pairBlack", name="player_pair_black")
    * @Method("GET")
    */
    public function pairBlackPlayer(Tournament $tournament, Player $player)
    {        
        $game = new Game($tournament, $tournament->getCurrentRound(), null, $player);
       
        $em = $this->get('doctrine')->getManager();
        $em->persist($game);
        $em->flush();
        
        return $this->redirectToRoute('tournaments_fast_show', array('tournament' => $tournament->getId()));
    }
    
}
