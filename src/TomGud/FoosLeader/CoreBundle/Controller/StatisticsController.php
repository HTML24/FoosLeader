<?php
/**
 * Created by PhpStorm.
 * User: Vilius
 * Date: 2014-09-23
 * Time: 10:19
 */

namespace TomGud\FoosLeader\CoreBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TomGud\FoosLeader\CoreBundle\Entity\ELOHistory;

class StatisticsController  extends Controller {

    public function globalAction() {

        $elo_repo = $this->getDoctrine()->getManager()->getRepository('FoosLeaderCoreBundle:ELOHistory');
        $players_repo = $this->getDoctrine()->getManager()->getRepository('FoosLeaderUserBundle:User');
        $result_repo = $this->getDoctrine()->getManager()->getRepository('FoosLeaderCoreBundle:Result');

        $elo_history_all_dates = $this->getEloDates($elo_repo->findEloHistoryForAll());
        $elo_history_all_players = $this->getELODataPoints($elo_repo->findEloHistoryForAll());

        $all_players = $players_repo->getAllUsers();

        $games_played = $this->getGamesForAll($all_players,$result_repo, "games");
        $games_won = $this->getGamesForAll($all_players,$result_repo, "won");
        $games_won_ratio = $this->getGamesForAll($all_players,$result_repo, "ratio");
        $games_elo = $this->getGamesForAll($all_players,$result_repo, "elo");
        $games_score_ratio = $this->getGamesForAll($all_players,$result_repo, "scoreRatio");
        $games_score = $this->getGamesForAll($all_players,$result_repo, "scored");
        $games_conceded = $this->getGamesForAll($all_players,$result_repo, "conceded", "asc");
        $games_avg_scored = $this->getGamesForAll($all_players,$result_repo, "avgScored");
        $games_avg_conceded = $this->getGamesForAll($all_players,$result_repo, "avgConceded", "asc");


        return $this->render('FoosLeaderCoreBundle:Statistics:global.html.twig',
            array(
                'elo_history_all_dates' => $elo_history_all_dates,
                'elo_history_all_players' => $elo_history_all_players,
                'games_played' => $games_played,
                'games_won' => $games_won,
                'games_won_ratio' => $games_won_ratio,
                'games_elo' => $games_elo,
                'games_score_ratio' => $games_score_ratio,
                'games_score' => $games_score,
                'games_conceded' => $games_conceded,
                'games_avg_scored' => $games_avg_scored,
                'games_avg_conceded' => $games_avg_conceded,
            )
        );
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