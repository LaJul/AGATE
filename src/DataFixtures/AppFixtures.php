<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;
use App\Entity\Club;
use App\Entity\Affiliate;
use App\Entity\Tournament;
use App\Entity\Tiebreak;
use App\Entity\Round;

class AppFixtures extends Fixture {

    public function load(ObjectManager $manager) {
        $faker = Faker\Factory::create('fr_FR');
        $faker->seed(1985);
        
        $BordeauxAspomEchecs = new Club("Bordeaux Aspom Echecs", "NAQ", "FRA");
        $manager->persist($BordeauxAspomEchecs);

        $AGJA = new Club("AGJA-Caudéran", "NAQ", "FRA");
        $manager->persist($AGJA);

        $RoyRene = new Club("Echiquier du Roy René", "PAC", "FRA");
        $manager->persist($RoyRene);

        $Chalons = new Club("Echiquier Chalonnais", "EST", "FRA");
        $manager->persist($Chalons);

        $tournament = new Tournament();
        $tournament->setName("InterZonal");
        $tournament->setStartDate(new \DateTime("01-01-2018"));
        $tournament->setEndDate(new \DateTime("02-02-2018"));

        $performanceTiebreak = new Tiebreak("Performance", "Perf");
        $manager->persist($performanceTiebreak);

        $cumulativeTiebreak = new Tiebreak("Cumulative", "Cu.");
        $manager->persist($cumulativeTiebreak);

        $tournament->addTiebreak($performanceTiebreak);
        $tournament->addTiebreak($cumulativeTiebreak);

        for ($i = 1; $i < 6; $i++) {
            $tournament->addRound(new Round($tournament, $i));
        }
        
        for ($i = 0; $i < 11; $i++) {
            $affiliate = new Affiliate();
            $affiliate->setFirstName($faker->firstNameFemale);
            $affiliate->setLastName($faker->lastname);
            $affiliate->setBirthDate($faker->dateTimeBetween('-80 years', '-5 years'));
            $affiliate->setRating($faker->numberBetween(1000, 2900));
            $affiliate->setTitle($faker->randomElement(array('gf', 'mf', 'ff', 'cf', ' ')));
            $affiliate->setGender("F");
            $affiliate->setClub($faker->randomElement(array($BordeauxAspomEchecs, $AGJA, $RoyRene, $Chalons)));
            $manager->persist($affiliate);
            
            $tournament->addPlayer($affiliate);
        }

        for ($i = 0; $i < 10; $i++) {
            $affiliate = new Affiliate();
            $affiliate->setFirstName($faker->firstNameMale);
            $affiliate->setLastName($faker->lastname);
            $affiliate->setBirthDate($faker->dateTimeBetween('-80 years', '-5 years'));
            $affiliate->setRating($faker->numberBetween(1000, 2900));
            $affiliate->setTitle($faker->randomElement(array('g', 'm', 'f', 'c', ' ')));
            $affiliate->setGender("M");
            $affiliate->setClub($faker->randomElement(array($BordeauxAspomEchecs, $AGJA, $RoyRene, $Chalons)));
            $manager->persist($affiliate);

            $tournament->addPlayer($affiliate);
        }
      
        $manager->persist($tournament);
        
        $manager->flush();
    }

}
