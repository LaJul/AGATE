<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;
use App\Entity\Affiliate;
use App\Entity\Tournament;
use App\Entity\Player;
use App\Entity\Round;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');
        
        $tournament = new Tournament();
        $tournament->setName("InterZonal");
        
        for ($i = 1; $i < 6; $i++) {
            $round = new Round($tournament, $i);
            $manager->persist($round);
        }
       
        //$tournament->setCurrentRound($round);
        
        $manager->persist($tournament);
        
        for ($i = 0; $i < 20; $i++) {
            $affiliate = new Affiliate();
            $affiliate->setName($faker->name);
            $affiliate->setRating($faker->numberBetween(1000, 2900));
            $player = new Player($tournament, $affiliate);
                    
           
            $manager->persist($player);
        }

        $manager->flush();
    }
}
