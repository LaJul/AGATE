<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

use App\Entity\Tournament;
use App\Entity\Round;

class TournamentController extends AbstractController
{
    /**
    * @Route("/", name="home")
    */
    public function index()
    {
        $em = $this->get('doctrine')->getManager();
                
        $tournament = $em->getRepository(Tournament::class)->findOneByName("InterZonal");

        return $this->redirectToRoute('tournaments_show', array('tournament_slug' => $tournament->getSlug()));
    }
    
    /**
    * @Route("/tournaments", name="tournaments_fast", methods={"GET"})
    */
    public function createTournament(Request $request)
    {    
        $tournament = new Tournament;
        $tournament->setName("AGATE-" . date('Y-m-d H:i:s'));
                
        $em = $this->get('doctrine')->getManager();  
                
        $round = new Round($tournament, 1);
        $em->persist($round);
       
        $tournament->setCurrentRound($round);
                
        $em->persist($tournament);
        $em->flush();
                
        return $this->redirectToRoute('tournaments_show', array('tournament' => $tournament->getId()));
    }
    
    /**
    * @Route("/tournaments_long", name="tournaments_new", methods={"GET", "POST"})
    * @Route("/tournaments_long", name="tournaments_create", methods={"GET", "POST"})
    */
    public function createLongTournament(Request $request)
    {    
        $form = $this->createFormBuilder()
                ->add('name', TextType::class)
                ->add('location', TextType::class)
                ->add('startDate', DateType::class)
                ->add('endDate', DateType::class)
                ->add('nbRounds', NumberType::class)
                
                ->add('timeControlType', ChoiceType::class, array(
                    'choices' =>array(
                        'Classique' => 0,
                        'Rapide' => 1,
                        'Blitz' => 2,
                    ),
                    'multiple' => false,
                    'expanded' => true,    
                ))
                ->add('timeControl', TextType::class)
                ->add('valider', SubmitType::class)
                ->getForm();
                
        $form->handleRequest($request);
        
        if ($form->isSubmitted()){
            if ($form->isValid()){
                $tournament = new Tournament;
                $tournament->setName($form->get('name')->getData());
                $tournament->setStartDate($form->get('startDate')->getData());
                $tournament->setNbRounds($form->get('nbRounds')->getData());
                $tournament->setTimeControlType($form->get('timeControlType')->getData());
                $tournament->setTimeControl($form->get('timeControl')->getData());
                
                $em = $this->get('doctrine')->getManager();  
                
                for ($i = $form->get('nbRounds')->getData(); $i > 0; $i--) {
                    $round = new Round($tournament, $i);
                    $em->persist($round);
                } 
                
                $tournament->setCurrentRound($round);
          
                $em->persist($tournament);
                $em->flush();
                
                return $this->redirectToRoute('tournament', array('tournament' => $tournament->getId()));
            }
        }
        
        return array('form' => $form->createView());
    }
   
    /**
     * @Route("/tournaments/{tournament_slug}", name="tournaments_show")
     */
    public function getTournament(string $tournament_slug)
    {    
        $em = $this->get('doctrine')->getManager();

        $tournament = $em->getRepository(Tournament::class)->findOneBySlug($tournament_slug);  
        
        return $this->redirectToRoute('round_show', array('tournament_slug' => $tournament->getSlug(), 'round_number' => $tournament->getCurrentRound()->getNumber()));
    }
}
