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

        $games_played = $this->getGamesPlayedForAll($all_players,$result_repo, "games");
        $games_won = $this->getGamesPlayedForAll($all_players,$result_repo, "won");

        return $this->render('FoosLeaderCoreBundle:Statistics:global.html.twig',
            array(
                'elo_history_all_dates' => $elo_history_all_dates,
                'elo_history_all_players' => $elo_history_all_players,
                'games_played' => $games_played,
                'games_won' => $games_won,
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
    public function getGamesPlayedForAll($players, $result_repo, $sort = "games"){

        $dataPoints = array();

        foreach($players as $player){
            $stats = $result_repo->getGameStatisticsForPlayer($player);
            if($stats != null && $stats->getGames() > 0){
                $dataPoints[$player->getUsername()]["games"] = $stats->getGames();
                $dataPoints[$player->getUsername()]["won"] = $stats->getWon();
                $dataPoints[$player->getUsername()]["name"] = $player->getUsername();
            }
        }

        // this mesess name as key
        usort($dataPoints, function($a, $b) use ($sort){
            return $b[$sort] - $a[$sort];
        });

        return $dataPoints;
    }

} 