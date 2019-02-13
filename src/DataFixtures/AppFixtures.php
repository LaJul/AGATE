<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;
use App\Entity\Club;
use App\Entity\Affiliate;
use App\Entity\Tournament;
use App\Entity\Player;
use App\Entity\Tiebreak;
use App\Entity\Round;

class AppFixtures extends Fixture {

     public function load(ObjectManager $manager) {
        $faker = Faker\Factory::create('fr_FR');
        $faker->seed(1985);

        $tournament = new Tournament();
        $tournament->setName("LivreArbitre");
        $tournament->setStartDate(new \DateTime("01-01-2018"));
        $tournament->setEndDate(new \DateTime("02-02-2018"));

        for ($i = 1; $i < 6; $i++) {
            $tournament->addRound(new Round($tournament, $i));
        }
        
        $alice = new Player();
        $alice->setFirstName("Alice");
        $alice->setLastName("");
        $alice->setTitle("g");
        $alice->setRating(2500);
        $tournament->addPlayer($alice);
        $manager->persist($alice);
        
        $bruno = new Player();
        $bruno->setFirstName("Bruno");
        $bruno->setLastName("");
        $bruno->setTitle("m");
        $bruno->setRating(2500);
        $tournament->addPlayer($bruno);
        $manager->persist($bruno);
        
        $carla = new Player();
        $carla->setFirstName("Carla");
        $carla->setLastName("");
        $carla->setTitle("wg");
        $carla->setRating(2400);
        $tournament->addPlayer($carla);
        $manager->persist($carla);
      
        $david = new Player();
        $david->setFirstName("David");
        $david->setLastName("");
        $david->setTitle("f");
        $david->setRating(2400);
        $tournament->addPlayer($david);
        $manager->persist($david);
        
        $eloise = new Player();
        $eloise->setFirstName("Eloise");
        $eloise->setLastName("");
        $eloise->setTitle("wm");
        $eloise->setRating(2350);
        $tournament->addPlayer($eloise);
        $manager->persist($eloise);
        
        $finn = new Player();
        $finn->setFirstName("Finn");
        $finn->setLastName("");
        $finn->setTitle("f");
        $finn->setRating(2300);
        $tournament->addPlayer($finn);
        $manager->persist($finn);
        
        $giorgia = new Player();
        $giorgia->setFirstName("Giorgia");
        $giorgia->setLastName("");
        $giorgia->setTitle("f");
        $giorgia->setRating(2250);
        $tournament->addPlayer($giorgia);
        $manager->persist($giorgia);
        
        $kevin = new Player();
        $kevin->setFirstName("Kevin");
        $kevin->setLastName("");
        $kevin->setTitle("f");
        $kevin->setRating(2250);
        $tournament->addPlayer($kevin);
        $manager->persist($kevin);
        
        $louise = new Player();
        $louise->setFirstName("Louise");
        $louise->setLastName("");
        $louise->setTitle("wm");
        $louise->setRating(2150);
        $tournament->addPlayer($louise);
        $manager->persist($louise);
        
        $marco = new Player();
        $marco->setFirstName("Marco");
        $marco->setLastName("");
        $marco->setTitle("wf");
        $marco->setRating(2150);
        $tournament->addPlayer($marco);
        $manager->persist($marco);
        
        $nancy = new Player();
        $nancy->setFirstName("Nancy");
        $nancy->setLastName("");
        $nancy->setTitle("wf");
        $nancy->setRating(2150);
        $tournament->addPlayer($nancy);
        $manager->persist($nancy);
        
        $oskar = new Player();
        $oskar->setFirstName("Oskar");
        $oskar->setLastName("");
        $oskar->setRating(2100);
        $tournament->addPlayer($oskar);
        $manager->persist($oskar);
        
        $patricia = new Player();
        $patricia->setFirstName("Patricia");
        $patricia->setLastName("");
        $patricia->setRating(2050);
        $tournament->addPlayer($patricia);
        $manager->persist($patricia);
        
        $robert = new Player();
        $robert->setFirstName("Robert");
        $robert->setLastName("");
        $robert->setRating(2050);
        $tournament->addPlayer($robert);
        $manager->persist($robert);
        
        $manager->persist($tournament);
        
        $manager->flush();
    }
    
    /*
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
*/
}
