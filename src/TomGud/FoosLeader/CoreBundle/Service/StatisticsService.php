<?php
/**
 * Created by PhpStorm.
 * User: tomas
 * Date: 28/04/15
 * Time: 17:31
 */

namespace TomGud\FoosLeader\CoreBundle\Service;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use TomGud\FoosLeader\CoreBundle\Entity\Result;
use TomGud\FoosLeader\CoreBundle\Model\PlayerStatisticsModel;
use TomGud\FoosLeader\CoreBundle\Model\PlayerStatisticsCollection;
use TomGud\FoosLeader\UserBundle\Entity\User;

class StatisticsService {
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var PlayerStatisticsCollection
     */
    protected $playerStatistics;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
        $this->playerStatistics = new PlayerStatisticsCollection();
        $this->loadGameStatistics();
    }

    public function getTotalGames()
    {
        $resultQueryBuilder = $this->em->getRepository('FoosLeaderCoreBundle:Result')->createQueryBuilder('result');
        $resultQueryBuilder
            ->select("COUNT(result.id) as totalGames" )
            ->where("result.team1Confirmed = TRUE")
            ->andWhere("result.team2Confirmed = TRUE");
        $result = $resultQueryBuilder->getQuery()->getSingleResult();
        return $result['totalGames'];
    }

    public function getTotalGoals()
    {
        $resultQueryBuilder = $this->em->getRepository('FoosLeaderCoreBundle:Result')->createQueryBuilder('result');
        $resultQueryBuilder
            ->select('SUM(result.team1Score) as totalScore1, SUM(result.team2Score) as totalScore2')
            ->where("result.team1Confirmed = TRUE")
            ->andWhere("result.team2Confirmed = TRUE");
        $result = $resultQueryBuilder->getQuery()->getSingleResult();
        return $result['totalScore1'] + $result['totalScore2'];
    }

    /**
     * @param int $numberOfTopPlayers
     * @return Paginator
     */
    public function getTopPlayers($numberOfTopPlayers = 5)
    {
        $monthAgo = new \DateTime('now', new \DateTimeZone('UTC'));
        $monthAgo->sub(\DateInterval::createFromDateString('1 month'));

        $userQueryBuilder = $this->em->getRepository('FoosLeaderUserBundle:User')->createQueryBuilder('user');
        $userQueryBuilder
            ->leftJoin('TomGud\FoosLeader\CoreBundle\Entity\Result', 'r', \Doctrine\ORM\Query\Expr\Join::WITH,
                'r.player1 = user.id OR r.player2 = user.id OR r.player3 = user.id OR r.player4 = user.id')
            ->where('r.team1Confirmed = TRUE AND r.team1Confirmed = TRUE')
            ->andWhere('r.submitted > :monthAgo')
            ->orderBy('user.ELORanking', 'DESC')
            ->setParameter('monthAgo', $monthAgo)
            ->setMaxResults($numberOfTopPlayers);
        return new Paginator($userQueryBuilder->getQuery(), $fetchJoin = true);
    }

    /**
     * @param int $numberOfTopPlayers
     * @return PlayerStatisticsModel[]
     */
    public function getTopWonRatios($numberOfTopPlayers = 5)
    {
        $this->playerStatistics->sortPlayers(PlayerStatisticsCollection::SORT_WIN_RATIO);
        $topPlayers = array();
        foreach ($this->playerStatistics as $statistic) {
            $topPlayers[] = $statistic;
            if (count($topPlayers) >= $numberOfTopPlayers) {
                break;
            }
        }
        return $topPlayers;
    }

    /**
     * @param $numberOfTopPlayers
     * @return PlayerStatisticsModel[]
     */
    public function getTopScoreRatios($numberOfTopPlayers)
    {
        $this->playerStatistics->sortPlayers(PlayerStatisticsCollection::SORT_GOAL_RATIO);
        $topPlayers = array();
        foreach ($this->playerStatistics as $statistic) {
            $topPlayers[] = $statistic;
            if (count($topPlayers) >= $numberOfTopPlayers) {
                break;
            }
        }
        return $topPlayers;
    }

    /**
     * @param $numberOfTopPlayers
     * @return PlayerStatisticsModel[]
     */
    public function getTopAverageScored($numberOfTopPlayers)
    {
        $this->playerStatistics->sortPlayers(PlayerStatisticsCollection::SORT_AVERAGE_SCORE);
        $topPlayers = array();
        foreach ($this->playerStatistics as $statistic) {
            $topPlayers[] = $statistic;
            if (count($topPlayers) >= $numberOfTopPlayers) {
                break;
            }
        }
        return $topPlayers;
    }

    /**
     * @param $numberOfTopPlayers
     * @return PlayerStatisticsModel[]
     */
    public function getTopAverageConceded($numberOfTopPlayers)
    {
        $this->playerStatistics->sortPlayers(PlayerStatisticsCollection::SORT_AVERAGE_CONCEDED);
        $topPlayers = array();
        foreach ($this->playerStatistics as $statistic) {
            $topPlayers[] = $statistic;
            if (count($topPlayers) >= $numberOfTopPlayers) {
                break;
            }
        }
        return $topPlayers;
    }

    /**
     * Fetching all statistics necessary for players in most optimal way I know how. Doing this once only takes up
     * four queries. Due to the way the result table is built up, we need to do in uch a backwards way. If you
     * dear reader know of a better way, please contribute.
     *
     * We need the sum of all goals, and won games and loss games for every player in each position 1-4 of each result.
     */
    private function loadGameStatistics()
    {
        $monthAgo = new \DateTime('now', new \DateTimeZone('UTC'));
        $monthAgo->sub(\DateInterval::createFromDateString('1 month'));

        $usersQueryBuilder = $this->em->getRepository('FoosLeaderUserBundle:User')->createQueryBuilder('user');
        $usersQueryBuilder
            ->select('DISTINCT user.id')
            ->leftJoin('TomGud\FoosLeader\CoreBundle\Entity\Result', 'result', Join::LEFT_JOIN,
                'user.id = result.player1 OR user.id = result.player2 OR user.id = result.player3 OR user.id = result.player4')
            ->where('result.submitted > :monthAgo')
            ->setParameter('monthAgo', $monthAgo);
        $activeUsers = $usersQueryBuilder->getQuery()->getResult();
        $activeUsersIds = array();
        foreach ($activeUsers as $activeUserArray) {
            // We expect each result to be an array
            $activeUsersIds[] = $activeUserArray['id'];
        }

        $player1WinCountsQB = $this->em->getRepository('FoosLeaderCoreBundle:Result')->createQueryBuilder('r');
        $player1WinCountsQB
            ->select('r', 'COUNT(r.player1) AS victories',
                'SUM(r.team1Score AS team1_goals, SUM(r.team2Score) AS team2_goals',
                'COUNT(r) as totalResults')
            ->where('r.team1Score > r.team2Score')
            ->andWhere('r.team1Confirmed = TRUE AND r.team2Confirmed = TRUE')
            ->groupBy('r.player1, r');

        $player1LossCountsQB = $this->em->getRepository('FoosLeaderCoreBundle:Result')->createQueryBuilder('r');
        $player1LossCountsQB
            ->select('r', 'COUNT(r.player1) AS losses',
                'SUM(r.team1Score AS team1_goals, SUM(r.team2Score) AS team2_goals',
                'COUNT(r) as totalResults')
            ->where('r.team1Score < r.team2Score')
            ->andWhere('r.team1Confirmed = TRUE AND r.team2Confirmed = TRUE')
            ->groupBy('r.player1, r');

        $player2WinCountsQB = $this->em->getRepository('FoosLeaderCoreBundle:Result')->createQueryBuilder('r');
        $player2WinCountsQB
            ->select('r', 'COUNT(r.player2) AS victories',
                'SUM(r.team1Score AS team1_goals, SUM(r.team2Score) AS team2_goals',
                'COUNT(r) as totalResults')
            ->where('r.team1Score < r.team2Score')
            ->andWhere('r.team1Confirmed = TRUE AND r.team2Confirmed = TRUE')
            ->groupBy('r.player2, r');

        $player2LossCountsQB = $this->em->getRepository('FoosLeaderCoreBundle:Result')->createQueryBuilder('r');
        $player2LossCountsQB
            ->select('r', 'COUNT(r.player2) AS losses',
                'SUM(r.team1Score AS team1_goals, SUM(r.team2Score) AS team2_goals',
                'COUNT(r) as totalResults')
            ->where('r.team1Score > r.team2Score')
            ->andWhere('r.team1Confirmed = TRUE AND r.team2Confirmed = TRUE')
            ->groupBy('r.player2, r');

        $player3WinCountsQB = $this->em->getRepository('FoosLeaderCoreBundle:Result')->createQueryBuilder('r');
        $player3WinCountsQB
            ->select('r', 'COUNT(r.player3) AS victories',
                'SUM(r.team1Score AS team1_goals, SUM(r.team2Score) AS team2_goals',
                'COUNT(r) as totalResults')
            ->where('r.team1Score > r.team2Score')
            ->andWhere('r.team1Confirmed = TRUE AND r.team2Confirmed = TRUE')
            ->andWhere('r.player3 IS NOT NULL')
            ->groupBy('r.player3, r');

        $player3LossCountsQB = $this->em->getRepository('FoosLeaderCoreBundle:Result')->createQueryBuilder('r');
        $player3LossCountsQB
            ->select('r', 'COUNT(r.player3) AS losses',
                'SUM(r.team1Score AS team1_goals, SUM(r.team2Score) AS team2_goals',
                'COUNT(r) as totalResults')
            ->where('r.team1Score < r.team2Score')
            ->andWhere('r.team1Confirmed = TRUE AND r.team2Confirmed = TRUE')
            ->andWhere('r.player3 IS NOT NULL')
            ->groupBy('r.player3, r');

        $player4WinCountsQB = $this->em->getRepository('FoosLeaderCoreBundle:Result')->createQueryBuilder('r');
        $player4WinCountsQB
            ->select('r', 'COUNT(r.player4) AS victories',
                'SUM(r.team1Score AS team1_goals, SUM(r.team2Score) AS team2_goals',
                'COUNT(r) as totalResults')
            ->where('r.team1Score < r.team2Score')
            ->andWhere('r.team1Confirmed = TRUE AND r.team2Confirmed = TRUE')
            ->andWhere('r.player4 IS NOT NULL')
            ->groupBy('r.player4, r');

        $player4LossCountsQB = $this->em->getRepository('FoosLeaderCoreBundle:Result')->createQueryBuilder('r');
        $player4LossCountsQB
            ->select('r', 'COUNT(r.player4) AS losses',
                'SUM(r.team1Score AS team1_goals, SUM(r.team2Score) AS team2_goals',
                'COUNT(r) as totalResults')
            ->where('r.team1Score > r.team2Score')
            ->andWhere('r.team1Confirmed = TRUE AND r.team2Confirmed = TRUE')
            ->andWhere('r.player4 IS NOT NULL')
            ->groupBy('r.player4, r');

        $player1WinCountsResults = $player1WinCountsQB->getQuery()->getResult();
        $player1LossCountsResults = $player1LossCountsQB->getQuery()->getResult();
        $player2WinCountsResults = $player2WinCountsQB->getQuery()->getResult();
        $player2LossCountsResults = $player2LossCountsQB->getQuery()->getResult();
        $player3WinCountsResults = $player3WinCountsQB->getQuery()->getResult();
        $player3LossCountsResults = $player3LossCountsQB->getQuery()->getResult();
        $player4WinCountsResults = $player4WinCountsQB->getQuery()->getResult();
        $player4LossCountsResults = $player4LossCountsQB->getQuery()->getResult();

        foreach ($player1WinCountsResults as $statisticsResult) {
            /* @var Result $result */
            $result = $statisticsResult[0];
            $this->playerStatistics->addPlayerStatistics($result->getPlayer1(), $statisticsResult['victories'], 0,
                $statisticsResult['team1_goals'], $statisticsResult['team2_goals']);
        }

        foreach ($player1LossCountsResults as $statisticsResult) {
            /* @var Result $result */
            $result = $statisticsResult[0];
            $this->playerStatistics->addPlayerStatistics($result->getPlayer1(), 0, $statisticsResult['losses'],
                $statisticsResult['team1_goals'], $statisticsResult['team2_goals']);
        }

        foreach ($player2WinCountsResults as $statisticsResult) {
            /* @var Result $result */
            $result = $statisticsResult[0];
            $this->playerStatistics->addPlayerStatistics($result->getPlayer2(), $statisticsResult['victories'], 0,
                $statisticsResult['team2_goals'], $statisticsResult['team1_goals']);
        }

        foreach ($player2LossCountsResults as $statisticsResult) {
            /* @var Result $result */
            $result = $statisticsResult[0];
            $this->playerStatistics->addPlayerStatistics($result->getPlayer2(), 0, $statisticsResult['losses'],
                $statisticsResult['team2_goals'], $statisticsResult['team1_goals']);
        }

        foreach ($player3WinCountsResults as $statisticsResult) {
            /* @var Result $result */
            $result = $statisticsResult[0];
            $this->playerStatistics->addPlayerStatistics($result->getPlayer3(), $statisticsResult['victories'], 0,
                $statisticsResult['team1_goals'], $statisticsResult['team2_goals']);
        }

        foreach ($player3LossCountsResults as $statisticsResult) {
            /* @var Result $result */
            $result = $statisticsResult[0];
            $this->playerStatistics->addPlayerStatistics($result->getPlayer3(), 0, $statisticsResult['losses'],
                $statisticsResult['team1_goals'], $statisticsResult['team2_goals']);
        }

        foreach ($player4WinCountsResults as $statisticsResult) {
            /* @var Result $result */
            $result = $statisticsResult[0];
            $this->playerStatistics->addPlayerStatistics($result->getPlayer4(), $statisticsResult['victories'], 0,
                $statisticsResult['team2_goals'], $statisticsResult['team1_goals']);
        }

        foreach ($player4LossCountsResults as $statisticsResult) {
            /* @var Result $result */
            $result = $statisticsResult[0];
            $this->playerStatistics->addPlayerStatistics($result->getPlayer4(), 0, $statisticsResult['losses'],
                $statisticsResult['team2_goals'], $statisticsResult['team1_goals']);
        }

        $this->playerStatistics->filter($activeUsersIds);
    }
}
