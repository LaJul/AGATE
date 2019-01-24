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
        
        for ($i = 0; $i < 10; $i++) {
            $affiliate = new Affiliate();
            $affiliate->setName($faker->lastname + " " + $faker->firstNameFemale);
            $affiliate->setRating($faker->numberBetween(1000, 2900));
            $affiliate->setTitle($faker->randomElement(array('gf', 'mf', 'ff', 'cf')));
            $player = new Player($tournament, $affiliate);
           
            $manager->persist($player);
        }
        
         for ($i = 0; $i < 10; $i++) {
            $affiliate = new Affiliate();
            $affiliate->setName($faker->lastname + " " + $faker->firstNameMale);
            $affiliate->setRating($faker->numberBetween(1000, 2900));
            $affiliate->setTitle($faker->randomElement(array('g', 'm', 'f', 'c')));
            $affiliate->setGender("M");
            $player = new Player($tournament, $affiliate);
           
            $manager->persist($player);
        }

        $manager->flush();
    }
}
