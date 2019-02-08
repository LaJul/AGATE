<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Tournament;

class RankingCalculator {
    
    public static $POINTS_BY_WIN = 1.0;
    public static $POINTS_BY_DRAW = 0.5;
    public static $POINTS_BY_LOSS = 0.0;
    
    private $em;
    private $pt;
    private $ct;
    private $frc;
    
    /*
     * Public methods
     */
     
    public function __construct(EntityManagerInterface $em, PerformanceTiebreak $pt, CumulativeTiebreak $ct) {
        $this->em = $em;
        $this->pt = $pt;
        $this->ct = $ct;
    }
  
    public function getAlphabeticalList(Tournament $tournament)
    {
        $players = $tournament->getPlayers()->toArray();
        
        usort($players, array($this, "cmpPlayersAlpha"));
        
        return $players;
    }
    
    public function getRankingTable(Tournament $tournament, int $roundNumber)
    {     
        $players = $tournament->getPlayers()->toArray();
        
        $table = array();
        
        foreach ($players as $key => $player)
        {
            $line = array();
                        
            $line['title'] = $player->getTitle();
            $line['lastName'] = $player->getLastName();
            $line['firstName'] = $player->getFirstName();
            $line['rating'] =$player->getRating();
            $line['category'] =$player->getCategory();
            $line['gender'] = $player->getGender();
            $line['federation'] =$player->getFederation();
            $line['league'] = $player->getLeague();
            $line['club'] = $player->getClub();
            $line['points'] = $this->getPoints($player, $roundNumber);
            
            foreach ($tournament->getTiebreaks() as $tiebreak)
            {
                switch ($tiebreak->getName())
                {
                    case "Performance" :
                        $line['Perf'] = $this->pt->setCriteria($player);
                        break;
                    case "Cumulative" :
                        $line['Cu.'] = $this->ct->setCriteria($player);
                        break;
                }
            }
            
            array_push($table, $line);
        }
        
        usort($table, array($this, "cmpPlayers"));
        
        return $table;
    }
    
    public function getRankingCrosstable(Tournament $tournament, int $roundNumber)
    {        
        $players = $tournament->getPlayers()->toArray();
        
        $table = array();
        
        foreach ($players as $key => $player)
        {
            $line = array();
                        
            $line['title'] = $player->getTitle();
            $line['lastName'] = $player->getLastName();
            $line['firstName'] = $player->getFirstName();
            $line['rating'] =$player->getRating();
            $line['category'] =$player->getCategory();
            $line['gender'] = $player->getGender();
            $line['federation'] =$player->getFederation();
            $line['league'] = $player->getLeague();
            
            $i = 0;
            
            while ($i < $roundNumber)
            {
                $line['r'.$i] = 1;
                $i++;
            }
            
            $line['points'] = $player->getPoints();
            
            foreach ($tournament->getTiebreaks() as $tiebreak)
            {
                switch ($tiebreak->getName())
                {
                    case "Performance" :
                        $line['Perf'] = $this->pt->setCriteria($player);
                        break;
                    case "Cumulative" :
                        $line['Cu.'] = $this->ct->setCriteria($player);
                        break;
                }
            }
            
            array_push($table, $line);
        }
        
         usort($table, array($this, "cmpPlayers"));
        
        return $table;
    }
    
   
    public function getResults() {
        // 1 -> result, opponent ranking, colour

        $results = array();

        foreach ($this->whiteGames as $game) {

            switch ($game->getResult()) {
                case "1-0" :
                    $result = "+";
                    break;
                case "X-X" :
                    $result = "=";
                    break;
                case "0-1" :
                    $result = "-";
                    break;
                case "1-F" :
                    $result = ">";
                    break;
                case "F-1" :
                    $result = "<";
                    break;
            }

            array_push($results, array("result" => $result, "opponent" => $game->getBlack()->getRanking(), "colour" => "B"));
        }
        
        foreach ($this->blackGames as $game) {

            switch ($game->getResult()) {
                case "1-0" :
                    $result = "-";
                    break;
                case "X-X" :
                    $result = "=";
                    break;
                case "0-1" :
                    $result = "+";
                    break;
                case "1-F" :
                    $result = "<";
                    break;
                case "F-1" :
                    $result = ">";
                    break;
            }

            array_push($results, array("result" => $result, "opponent" => $game->getWhite()->getRanking(), "colour" => "N"));
        }
        
        return $results;
    }

    public function getPoints($player, $roundNumber =  null)
    {
        $gameRepository = $this->em->getRepository('App\Entity\Game');

        $wins = $gameRepository->getWonGames($player, $roundNumber);
        $draws = $gameRepository->getDrawnGames($player, $roundNumber);

        return (float) count($wins) * RankingCalculator::$POINTS_BY_WIN 
                + count($draws) * RankingCalculator::$POINTS_BY_DRAW;
    }
    
    /*
     * Private methods
     */
    
     private static function cmpPlayersAlpha($a, $b) {

        if ($a->getLastName() == $b->getLastName()) {
          
            return $a->getFirstName() > $b->getFirstName() ? 1 : -1;
        }
        return $a->getLastName() > $b->getLastName() ? 1 : -1;
    }
    
    
    private static function cmpPlayers($a, $b) {

        if ($a['points'] == $b['points']) {
          
            return $a['Perf'] < $b['Perf'] ? 1 : -1;
        }
        return $a['points'] < $b['points'] ? 1 : -1;
    }
}
