<?php
/**
 * User: Tomas
 * Date: 3/18/2015
 * Copyright Html24
 */

namespace TomGud\FoosLeader\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TomGud\FoosLeader\CoreBundle\Entity\ELOHistory;
use TomGud\FoosLeader\CoreBundle\Entity\Result;

class UserController extends Controller {
    public function profileAction($userId) {
        $em = $this->get('doctrine.orm.entity_manager');
        $userRepository = $em->getRepository('FoosLeaderUserBundle:User');

        $rival = $userRepository->find($userId);
        if ($rival === null) {
            throw new NotFoundHttpException('Uer not found');
        }

        $eloHistoryRepository = $em->getRepository('FoosLeaderCoreBundle:ELOHistory');
        $eloCalculatorService = $this->get('foos_leader.elo_calculator');
        $resultRepository = $em->getRepository('FoosLeaderCoreBundle:Result');

        $rivalLatestHistory = $eloHistoryRepository->findNewestRanking($rival);
        $userLatestHistory = $eloHistoryRepository->findNewestRanking($this->getUser());
        $duelResults = $resultRepository->getResultsIncludingPlayers($rival, $this->getUser());

        $totalGames = count($duelResults);
        $gamesRivalWon = 0;
        $gamesUserWon = 0;
        $totalGoals = 0;
        $goalsUserScored = 0;
        $goalsRivalScored = 0;

        foreach ($duelResults as $result) {
            if ($result->userInTeam1($rival)) {
                if ($result->getTeam1Score() > $result->getTeam2Score()) {
                    $gamesRivalWon++;
                } else {
                    $gamesUserWon++;
                }
                $goalsRivalScored += $result->getTeam1Score();
                $goalsUserScored += $result->getTeam2Score();
            } else {
                if ($result->getTeam1Score() < $result->getTeam2Score()) {
                    $gamesRivalWon++;
                } else {
                    $gamesUserWon++;
                }
                $goalsRivalScored += $result->getTeam2Score();
                $goalsUserScored += $result->getTeam1Score();
            }
            $totalGoals += $result->getTeam1Score() + $result->getTeam2Score();
        }

        $goalStatistics = $resultRepository->getGoalStatisticsForPlayer($rival);
        $gameStatistics = $resultRepository->getGameStatisticsForPlayer($rival);
        if((float)$gameStatistics->getGames() > 0){
            $winRatio = (((float)$gameStatistics->getWon() * 100) / (float)$gameStatistics->getGames());
        } else{
            $winRatio = 0;
        };

        if((float)$goalStatistics->getConceded() > 0){
            $ScoreRatio = ((float)$goalStatistics->getScored() / (float)$goalStatistics->getConceded());
        } else{
            $ScoreRatio = 0;
        };
        if((float)$gameStatistics->getGames() > 0){
            $AvgScore = ((float)$goalStatistics->getScored() / (float)$gameStatistics->getGames());
        } else{
            $AvgScore = 0;
        };
        if((float)$gameStatistics->getGames() > 0){
            $AvgConceded =  ((float)$goalStatistics->getConceded() / (float)$gameStatistics->getGames());
        } else{
            $AvgConceded = 0;
        };

        $eloHistories = array($rival->getId() => $rivalLatestHistory, $this->getUser()->getId() => $userLatestHistory);
        $possibleResult = new Result();
        $userClone = clone $this->getUser();
        $rivalClone = clone $rival;
        $possibleResult->setPlayer1($userClone)->setPlayer2($rivalClone);

        $possibleResult->setTeam1Score(10)->setTeam2Score(9);
        $possibleWinOutcome = $eloCalculatorService->calculateELOForResult($possibleResult, $eloHistories);

        $possibleResult->setTeam1Score(9)->setTeam2Score(10);
        $possibleLossOutcome = $eloCalculatorService->calculateELOForResult($possibleResult, $eloHistories);

        $paginator = $this->container->get('knp_paginator');
        $pagination =$paginator->paginate($duelResults, $this->container->get('request')->get('page', 1),  5); //

        return $this->render('FoosLeaderCoreBundle:User:rivalry.html.twig',
            array(
                'rival' => $rival,
                'duelResults' => $duelResults,
                'possibleWinOutcome' => $possibleWinOutcome,
                'possibleLossOutcome' => $possibleLossOutcome,
                'gamesUserWonRatio' => ($totalGames === 0) ? "-" : (int)100*($gamesUserWon/$totalGames) . "%",
                'gamesRivalWonRatio' => ($totalGames === 0) ? "-" : (int)100*($gamesRivalWon/$totalGames) . "%",
                'gamesUserWon' => $gamesUserWon,
                'gamesRivalWon' => $gamesRivalWon,
                'goalsUserScoredRatio' => ($totalGoals === 0) ? "-" : (int)(100*($goalsUserScored/$totalGoals)) . "%",
                'goalsRivalScoredRatio' => ($totalGoals === 0) ? "-" : (int)(100*($goalsRivalScored/$totalGoals))  . "%",
                'goalsUserScored' => $goalsUserScored,
                'goalsRivalScored' => $goalsRivalScored,
                'totalGames' => $totalGames,
                'totalGoals' => $totalGoals,
                'goalStatistics' => $goalStatistics,
                'gameStatistics' => $gameStatistics,
                'winRatio' => $winRatio,
                'AvgScore' => $AvgScore,
                'AvgConceded' => $AvgConceded,
                'ScoreRatio' => $ScoreRatio,
                'pagination' => $pagination,
            )
        );
    }
}
