<?php

namespace App\Controller;

use App\Entity\Tournament;
use App\Entity\Player;
use App\Entity\Game;

use App\Service\SwissPairingCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class RoundController extends AbstractController
{
     /**
     * @Route("/tournaments/{tournament_slug}/rounds/{round_number}", name="round_show", requirements={"round_number"="\d+"}, methods={"GET"})
     */
    public function getRound(string $tournament_slug, int $round_number)
    {     
        $em = $this->get('doctrine')->getManager();
        
        $tournament = $em->getRepository(Tournament::class)->findOneBySlug($tournament_slug);  
        $round = $tournament->getRound($round_number);
        dump($round);

        return $this->render("fast_tournament.html.twig", array('tournament' => $tournament, 'round' => $round));
    }
    
    /**
    * @Route("/tournaments/{tournament_slug}/rounds/", name="round_create", methods={"GET"})
    */
    public function createRound(string $tournament_slug, SwissPairingCalculator $swissManager)
    {
        $em = $this->get('doctrine')->getManager();
        
        $tournament = $em->getRepository(Tournament::class)->findOneBySlug($tournament_slug);  
        
        $round = $swissManager->createRound($tournament);
        
        return $this->redirectToRoute('round_show', array('tournament_slug' => $tournament->getSlug(), 'round_number' => $round->getNumber()));
    }
    
    /**
    * @Route("/tournaments/{tournament_slug}/rounds/{round_number}/pair", name="round_pair", methods={"GET"})
    */
    public function pairRound(string $tournament_slug, int $round_number, SwissPairingCalculator $swissManager)
    {
        $em = $this->get('doctrine')->getManager();
        
        $tournament = $em->getRepository(Tournament::class)->findOneBySlug($tournament_slug);  
        $round = $tournament->getRound($round_number);  

        $swissManager->pairRound($round);

        return $this->redirectToRoute('round_show', array('tournament_slug' => $tournament->getSlug(), 'round_number' => $round->getNumber()));
    }
    
    /**
    * @Route("/tournaments/{tournament_slug}/rounds/{round_number}/unpair", name="round_unpair", methods={"GET"})
    */
    public function unpairRound(string $tournament_slug, int $round_number, SwissPairingCalculator $swissManager)
    {
        $em = $this->get('doctrine')->getManager();
        
        $tournament = $em->getRepository(Tournament::class)->findOneBySlug($tournament_slug);
        $round = $tournament->getRound($round_number);  

        $swissManager->unpairRound($round);
        
        return $this->redirectToRoute('round_show', array('tournament_slug' => $tournament->getSlug(), 'round_number' => $round->getNumber()));
    }
    
      /**
    * @Route("/tournaments/{tournament_slug}/round/{round_number}/players/{player}?action=pairWhite", name="player_pair_white", methods={"GET"})
    */
    public function pairWhitePlayer(string $tournament_slug, int $round_number, Player $player)
    {     
        $em = $this->get('doctrine')->getManager();
        
        $tournament = $em->getRepository(Tournament::class)->findOneBySlug($tournament_slug);
        $round = $tournament->getRound($round_number);  

        $game = new Game($tournament, $round, $player, null);
       
        $em->persist($game);
        $em->flush();
        
        return $this->redirectToRoute('round_show', array('tournament_id' => $tournament->getId(), 'round_number' => $round_number));
    }
    
     /**
    * @Route("/tournaments/{tournament_slug}/round/{round_number}/players/{player}?action=pairBlack", name="player_pair_black", methods={"GET"})
    */
    public function pairBlackPlayer(string $tournament_slug, int $round_number, Player $player)
    {        
        $em = $this->get('doctrine')->getManager();
        
        $tournament = $em->getRepository(Tournament::class)->findOneBySlug($tournament_slug);
        $round = $tournament->getRound($round_number);  

        $game = new Game($tournament, $round, null, $player);
       
        $em->persist($game);
        $em->flush();
        
        return $this->redirectToRoute('round_show', array('tournament_id' => $tournament->getId(), 'round_number' => $round_number));
    }
    
    
    /**
     * @Route("/tournaments/{tournament_slug}/rounds/{round_number}?action=quick", name="set_round_result")
     */
    public function setRoundResult(string $tournament_slug, int $round_number, SwissPairingCalculator $spc)
    {
        $em = $this->get('doctrine')->getManager(); 

        $tournament = $em->getRepository(Tournament::class)->findOneBySlug($tournament_slug);
        $round = $tournament->getRound($round_number);

        foreach ($round->getGames() as $game)
        {
            if ($game->getBlack() == null)
            {
                continue;
            }
            if ($game->getWhite()->getRating() > $game->getBlack()->getRating())
            {
                $result = Game::$WHITE_WINS;
            }
            else if ($game->getWhite()->getRating() < $game->getBlack()->getRating())
            {
                $result = Game::$BLACK_WINS;
            }
            else
            {
                $result = Game::$DRAW;
            }
            
            $spc->setGameResult($game, $result);
        
            $em->persist($game);
        }
        
        $em->flush();
        
        return $this->redirectToRoute('round_show', array('tournament_slug' => $tournament->getSlug(), 'round_number' => $round->getNumber()));
    }
    
    
}
