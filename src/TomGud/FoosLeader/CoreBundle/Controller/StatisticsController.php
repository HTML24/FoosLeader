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

        $elo_history_all_dates = $this->getEloDates($elo_repo->findEloHistoryForAll());
        $elo_history_all_players = $this->getELODataPoints($elo_repo->findEloHistoryForAll());

        return $this->render('FoosLeaderCoreBundle:Statistics:global.html.twig',
            array(
                'elo_history_all_dates' => $elo_history_all_dates,
                'elo_history_all_players' => $elo_history_all_players,
            )
        );
    }
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


} 