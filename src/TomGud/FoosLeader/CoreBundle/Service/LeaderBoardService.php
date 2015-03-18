<?php

namespace TomGud\FoosLeader\CoreBundle\Service;

use Doctrine\ORM\EntityManager;
use TomGud\FoosLeader\CoreBundle\Entity\ELOHistory;
use TomGud\FoosLeader\CoreBundle\Entity\Result;
use TomGud\FoosLeader\CoreBundle\Model\ELOPlayerResultModel;

class LeaderBoardService
{
    const INFO_SCREEN_API_LEADERBOARD = "http://infoscreen.html24-dev.dk/api/foosball/leaderboard";
    protected $em;

    public function __construct(EntityManager $entityManager) {
        $this->em = $entityManager;
    }

    public function sendLeaderBoardRequest()
    {
        $players_repo = $this->em->getRepository('FoosLeaderUserBundle:User');
        $all_players = $players_repo->getActiveUsers();
        $result_repo = $this->em->getRepository('FoosLeaderCoreBundle:Result');

        $players_elo = $this->getGamesForAll($all_players,$result_repo, "elo");

        $post_array = array("html24_infoscreenbundle_foosballboard" => array());
        foreach ($players_elo as $index => $player) {
            $post_array['html24_infoscreenbundle_foosballboard']['scores'][] = array(
                'position' => $index + 1,
                'name' => $player["name"],
                'elo' => $player["elo"],
            );
            if ($index > 3) {
                // Only include first 5 players
                break;
            }
        }


        //open connection
        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL, LeaderBoardService::INFO_SCREEN_API_LEADERBOARD);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: multipart/form-data"));
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($post_array));

        //execute post
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);
    }


    // graph
    public function getEloDates($eloHistories){
        $dataPoints = array();

        foreach ($eloHistories as $eloHistory) {
            $date = $eloHistory->getRegistered();
            $date = $date->format("Y-m-d");

            if (!array_key_exists($date, $dataPoints)) {
                $dataPoints[$date] = $date;
            }
        }
        return $dataPoints;
    }

    public function getELODataPoints($eloHistories) {
        $dataPoints = array();
        $allDates = $this->getEloDates($eloHistories);
        $allDates = array_flip($allDates);
        foreach ($allDates as $key => $date) {
            $allDates[$key] = -1;
        }

        foreach ($eloHistories as $eloHistory) {
            $user = $eloHistory->getPlayer();
            $date = $eloHistory->getRegistered();

            $date = $date->format("Y-m-d");

            if (!array_key_exists($user->getUsername(), $dataPoints)) {
                $dataPoints[$user->getUsername()] = $allDates;
            }

            /* highest
            if ($dataPoints[$user->getUsername()][$date] < $eloHistory->getNewElo()) {
                $dataPoints[$user->getUsername()][$date]  = $eloHistory->getNewElo();
            }
            */

            // latest
            $dataPoints[$user->getUsername()][$date]  = $eloHistory->getNewElo();

        }

        return $dataPoints;
    }

    // tables
    public function getGamesForAll($players, $result_repo, $sort = "games", $direction = "desc"){

        $dataPoints = array();

        foreach($players as $player){
            $stats = $result_repo->getGameStatisticsForPlayer($player);
            $goalStats = $result_repo->getGoalStatisticsForPlayer($player);
            if($stats != null && $stats->getGames() > 0){
                $dataPoints[$player->getUsername()]["games"] = $stats->getGames();
                $dataPoints[$player->getUsername()]["won"] = $stats->getWon();
                $dataPoints[$player->getUsername()]["name"] = $player->getUsername();
                $dataPoints[$player->getUsername()]["elo"] = $player->getELORanking();
                $dataPoints[$player->getUsername()]["scored"] =  (int)$goalStats->getScored();
                $dataPoints[$player->getUsername()]["conceded"] =  (int)$goalStats->getConceded();
                if((int)$stats->getGames() != 0){
                    $dataPoints[$player->getUsername()]["ratio"] = ((float)$stats->getWon() * 100) / (float)$stats->getGames();
                }else{
                    $dataPoints[$player->getUsername()]["ratio"] = 0;
                }
                if((int)$goalStats->getScored() != 0 && (int)$goalStats->getConceded() != 0){
                    $dataPoints[$player->getUsername()]["scoreRatio"] = (float)$goalStats->getScored() / (float)$goalStats->getConceded();
                }else{
                    $dataPoints[$player->getUsername()]["scoreRatio"] = 0;
                }
                if((int)$stats->getGames() != 0 && (int)$goalStats->getScored() != 0){
                    $dataPoints[$player->getUsername()]["avgScored"] = (float)$goalStats->getScored() / (float)$stats->getGames();
                }else{
                    $dataPoints[$player->getUsername()]["avgScored"] = 0;
                }
                if((int)$stats->getGames() != 0 && (int)$goalStats->getConceded() != 0){
                    $dataPoints[$player->getUsername()]["avgConceded"] = (float)$goalStats->getConceded() / (float)$stats->getGames();
                }else{
                    $dataPoints[$player->getUsername()]["avgConceded"] = 0;
                }
            }
        }

        // this mesess name as key
        if($direction == "desc"){
            usort($dataPoints, function($a, $b) use ($sort){
                $result = 0;
                if ($b[$sort] > $a[$sort]) {
                    $result = 1;
                } else if ($b[$sort] < $a[$sort]) {
                    $result = -1;
                }
                return $result; //$b[$sort] - $a[$sort];
            });
        }else{
            usort($dataPoints, function($a, $b) use ($sort){
                $result = 0;
                if ($a[$sort] > $b[$sort]) {
                    $result = 1;
                } else if ($a[$sort] < $b[$sort]) {
                    $result = -1;
                }
                return $result; //$a[$sort] - $b[$sort];
            });
        }
        return $dataPoints;
    }

}

?>
