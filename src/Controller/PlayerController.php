<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Affiliate;
use App\Entity\Tournament;
use App\Entity\Player;

class PlayerController extends AbstractController
{
    /**
    * @Route("/tournaments/{tournament}/players", name="players_index", methods={"GET"})
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
    * @Route("/tournaments/{tournament_slug}/players/new", name="player_create")
    */
    public function createPlayer(Request $request, string $tournament_slug)
    {
        $em = $this->get('doctrine')->getManager();

        $nameAndRating = explode(" ", $request->query->get('name-with-rating'));
        $tournament = $em->getRepository(Tournament::class)->findOneBySlug($tournament_slug);  

        $player = $tournament->addPlayer();
        $player->setLastName("")->setFirstName($nameAndRating[0]);
        $player->setRating(isset($nameAndRating[1]) ? $nameAndRating[1] : 1000);
        
        $em->persist($player);
        $em->flush();
        
        return $this->redirectToRoute('tournaments_show', array('tournament_slug' => $tournament->getSlug()));
    }
 
    /**
    * @Route("/tournaments/{tournament_slug}/players/destroy/{player}", name="player_destroy", methods={"GET"})
    */
    public function destroyPlayer(string $tournament_slug, Player $player)
    {
        $em = $this->get('doctrine')->getManager();
        $em->remove($player);
        $em->flush();
        
        return $this->redirectToRoute('tournaments_show', array('tournament_slug' => $tournament_slug));
    }
    
    
    /**
    * @Route("/tournaments/{tournament}/round/{round_number}/players/{player}?action=roundOut", name="player_round_out", methods={"GET"})
    */
    public function roundOutlayer(Tournament $tournament, int $round_number, Player $player)
    {        
        $round = $tournament->getRound($round_number);  
        
        $round->remove($player);
       
        $em = $this->get('doctrine')->getManager();
        $em->flush();
        
        return $this->redirectToRoute('round_show', array('tournament_id' => $tournament->getId(), 'round_number' => $round_number));
    }
}
