<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Entity\Tournament;

use App\Service\RankingCalculator;
use App\Service\FideRatingCalculator;

class ExportController extends AbstractController
{
    /**
     * @Route("/tournaments/{tournament_slug}/a-z", name="tournaments_az")
     */
    public function getAlphabeticalList(string $tournament_slug, RankingCalculator $rc)
    {    
        $em = $this->get('doctrine')->getManager();
        
        $tournament = $em->getRepository(Tournament::class)->findOneBySlug($tournament_slug);  
        
        $alphabeticalList = $rc->getAlphabeticalList($tournament);

        return $this->render("alphabeticalList.html.twig", array('tournament' => $tournament, 'alphabeticalList' => $alphabeticalList));
    }
    
    /**
     * @Route("/tournaments/{tournament_slug}/rounds/{round_number}/rk", name="tournaments_rk")
     */
    public function getRankingTable(string $tournament_slug, int $round_number, RankingCalculator $rc)
    {    
        $em = $this->get('doctrine')->getManager();
        
        $tournament = $em->getRepository(Tournament::class)->findOneBySlug($tournament_slug);
        
        $rankingTable = $rc->getRankingTable($tournament, $round_number);

        return $this->render("rankingTable.html.twig", array('tournament' => $tournament, 'roundNumber' => $round_number-1, 'rankingTable' => $rankingTable));
    }
    
    /**
     * @Route("/tournaments/{tournament_slug}/rounds/{round_number}/ag", name="tournaments_ag")
     */
    public function getRankingCrosstable(string $tournament_slug, int $round_number, RankingCalculator $rc)
    {    
        $em = $this->get('doctrine')->getManager();
        
        $tournament = $em->getRepository(Tournament::class)->findOneBySlug($tournament_slug);
        
        $rankingCrosstable = $rc->getRankingCrosstable($tournament, $round_number);
        
        return $this->render("rankingCrosstable.html.twig", array('tournament' => $tournament, 'roundNumber' => $round_number-1, 'rankingCrosstable' => $rankingCrosstable));
    }
    
    /**
     * @Route("/tournaments/{tournament_slug}/rounds/{round_number}/fide", name="tournaments_fide")
     */
    public function getFidePerfsTable(string $tournament_slug, int $round_number, FideRatingCalculator $rc)
    {    
        $em = $this->get('doctrine')->getManager();
        
        $tournament = $em->getRepository(Tournament::class)->findOneBySlug($tournament_slug);
        
        $fidePerfsTable = $rc->getFidePerfsTable($tournament, $round_number);

        return $this->render("fide.html.twig", array('tournament' => $tournament, 'roundNumber' => $round_number-1, 'fidePerfsTable' => $fidePerfsTable));
    }
}
