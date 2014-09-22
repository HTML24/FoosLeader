<?php

namespace TomGud\FoosLeader\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TomGud\FoosLeader\CoreBundle\Entity\ELOHistory;

class DefaultController extends Controller
{
    public function indexAction()
    {
    	$result_repo = $this->getDoctrine()->getManager()->getRepository('FoosLeaderCoreBundle:Result');
    	$latest_for_user = $result_repo->getLatestForUser($this->getUser());
    	$latest_for_all = $result_repo->getLatestForAll();
    	$unconfirmed_for_user = $result_repo->getUnconfirmedResultsForUser($this->getUser());

        $elo_repo = $this->getDoctrine()->getManager()->getRepository('FoosLeaderCoreBundle:ELOHistory');

        // move this all somewhere
        $elo_history_all_dates = $this->getEloDates($elo_repo->findEloHistoryForAll());
        $elo_history_all_players = $this->getELODataPoints($elo_repo->findEloHistoryForAll());

        return $this->render('FoosLeaderCoreBundle:Default:index.html.twig',
        	array(
        		'latest_user' => $latest_for_user,
        		'latest_all' => $latest_for_all,
        		'unconfirmed' => $unconfirmed_for_user,
        		'notifications' => array(),
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

            if ($dataPoints[$user->getUsername()][$date] < $eloHistory->getNewElo()) {
                $dataPoints[$user->getUsername()][$date]  = $eloHistory->getNewElo();
            }
        }

        return $dataPoints;
    }

}
