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

        $leaderboardService = $this->get('foos_leader.leader_board');

        $elo_history_all_dates = $leaderboardService->getEloDates($elo_repo->findEloHistoryForAll());
        $elo_history_all_players = $leaderboardService->getELODataPoints($elo_repo->findEloHistoryForAll());

        $all_players = $players_repo->getActiveUsers();


        $games_won_ratio = $leaderboardService->getGamesForAll($all_players,$result_repo, "ratio");
        $games_elo = $leaderboardService->getGamesForAll($all_players,$result_repo, "elo");
        $games_score_ratio = $leaderboardService->getGamesForAll($all_players,$result_repo, "scoreRatio");
        $games_avg_scored = $leaderboardService->getGamesForAll($all_players,$result_repo, "avgScored");
        $games_avg_conceded = $leaderboardService->getGamesForAll($all_players,$result_repo, "avgConceded", "asc");


        return $this->render('FoosLeaderCoreBundle:Statistics:global.html.twig',
            array(
                'elo_history_all_dates' => $elo_history_all_dates,
                'elo_history_all_players' => $elo_history_all_players,
                'games_won_ratio' => $games_won_ratio,
                'games_elo' => $games_elo,
                'games_score_ratio' => $games_score_ratio,
                'games_avg_scored' => $games_avg_scored,
                'games_avg_conceded' => $games_avg_conceded,
            )
        );
    }

}
