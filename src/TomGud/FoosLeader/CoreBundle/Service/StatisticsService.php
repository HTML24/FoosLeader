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
            ->where("result.team1Confirmed = 1")
            ->andWhere("result.team2Confirmed = 1");
        $result = $resultQueryBuilder->getQuery()->getSingleResult();
        return $result['totalGames'];
    }

    public function getTotalGoals()
    {
        $resultQueryBuilder = $this->em->getRepository('FoosLeaderCoreBundle:Result')->createQueryBuilder('result');
        $resultQueryBuilder
            ->select('SUM(result.team1Score) as totalScore1, SUM(result.team2Score) as totalScore2')
            ->where("result.team1Confirmed = 1")
            ->andWhere("result.team2Confirmed = 1");
        $result = $resultQueryBuilder->getQuery()->getSingleResult();
        return $result['totalScore1'] + $result['totalScore2'];
    }

    /**
     * @param int $numberOfTopPlayers
     * @return Paginator
     */
    public function getTopPlayers($numberOfTopPlayers = 5)
    {
        $userQueryBuilder = $this->em->getRepository('FoosLeaderUserBundle:User')->createQueryBuilder('user');
        $userQueryBuilder
            ->leftJoin('TomGud\FoosLeader\CoreBundle\Entity\Result', 'r', \Doctrine\ORM\Query\Expr\Join::WITH,
                'r.player1 = user.id OR r.player2 = user.id OR r.player3 = user.id OR r.player4 = user.id')
            ->where('r.team1Confirmed = true AND r.team1Confirmed = true')
            ->orderBy('user.ELORanking', 'DESC')
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
        $team1WinCountsQB = $this->em->getRepository('FoosLeaderCoreBundle:Result')->createQueryBuilder('r');
        $team1WinCountsQB
            ->select('r', 'COUNT(r.player1) AS p1_victories',
                'COUNT(r.player3) AS p3_victories',
                'SUM(r.team1Score AS team1_goals, SUM(r.team2Score) AS team2_goals',
                'COUNT(r) as totalResults')
            ->leftJoin('TomGud\FoosLeader\UserBundle\Entity\User', 'user', Join::WITH,
                'r.player1 = user.id OR r.player2 = user.id OR r.player3 = user.id or r.player4 = user.id')
            ->where('r.team1Score > r.team2Score')
            ->andWhere('r.team1Confirmed = 1 AND r.team2Confirmed = 1')
            ->andWhere('user.lastLogin > :monthAgo')
            ->having('totalResults > 5')
            ->setParameter('monthAgo', $monthAgo)
            ->groupBy('r.player1', 'r.player3');

        $team1LossCountsQB = $this->em->getRepository('FoosLeaderCoreBundle:Result')->createQueryBuilder('r');
        $team1LossCountsQB
            ->select('r', 'COUNT(r.player1) AS p1_losses',
                'COUNT(r.player3) AS p3_losses',
                'SUM(r.team1Score AS team1_goals, SUM(r.team2Score) AS team2_goals',
                'COUNT(r) as totalResults')
            ->leftJoin('TomGud\FoosLeader\UserBundle\Entity\User', 'user', Join::WITH,
                'r.player1 = user.id OR r.player2 = user.id OR r.player3 = user.id or r.player4 = user.id')
            ->where('r.team1Score < r.team2Score')
            ->andWhere('r.team1Confirmed = 1 AND r.team2Confirmed = 1')
            ->andWhere('user.lastLogin > :monthAgo')
            ->having('totalResults > 5')
            ->setParameter('monthAgo', $monthAgo)
            ->groupBy('r.player1', 'r.player3');

        $team2WinCountsQB = $this->em->getRepository('FoosLeaderCoreBundle:Result')->createQueryBuilder('r');
        $team2WinCountsQB
            ->select('r', 'COUNT(r.player2) AS p2_victories',
                'COUNT(r.player4) AS p4_victories',
                'SUM(r.team1Score AS team1_goals, SUM(r.team2Score) AS team2_goals',
                'COUNT(r) as totalResults')
            ->leftJoin('TomGud\FoosLeader\UserBundle\Entity\User', 'user', Join::WITH,
                'r.player1 = user.id OR r.player2 = user.id OR r.player3 = user.id or r.player4 = user.id')
            ->where('r.team1Score < r.team2Score')
            ->andWhere('r.team1Confirmed = 1 AND r.team2Confirmed = 1')
            ->andWhere('user.lastLogin > :monthAgo')
            ->having('totalResults > 5')
            ->setParameter('monthAgo', $monthAgo)
            ->groupBy('r.player2', 'r.player4');

        $team2LossCountsQB = $this->em->getRepository('FoosLeaderCoreBundle:Result')->createQueryBuilder('r');
        $team2LossCountsQB
            ->select('r', 'COUNT(r.player2) AS p2_losses',
                'COUNT(r.player4) AS p4_losses',
                'SUM(r.team1Score AS team1_goals, SUM(r.team2Score) AS team2_goals',
                'COUNT(r) as totalResults')
            ->leftJoin('TomGud\FoosLeader\UserBundle\Entity\User', 'user', Join::WITH,
                'r.player1 = user.id OR r.player2 = user.id OR r.player3 = user.id or r.player4 = user.id')
            ->where('r.team1Score > r.team2Score')
            ->andWhere('r.team1Confirmed = 1 AND r.team2Confirmed = 1')
            ->andWhere('user.lastLogin > :monthAgo')
            ->having('totalResults > 5')
            ->setParameter('monthAgo', $monthAgo)
            ->groupBy('r.player2', 'r.player4');

        $team1WinCountsResults = $team1WinCountsQB->getQuery()->getResult();
        $team1LossCountsResults = $team1LossCountsQB->getQuery()->getResult();
        $team2WinCountsResults = $team2WinCountsQB->getQuery()->getResult();
        $team2LossCountsResults = $team2LossCountsQB->getQuery()->getResult();

        foreach ($team1WinCountsResults as $statisticsResult) {
            /* @var Result $result */
            $result = $statisticsResult[0];
            $this->playerStatistics->addPlayerStatistics($result->getPlayer1(), $statisticsResult['p1_victories'], 0,
                $statisticsResult['team1_goals'], $statisticsResult['team2_goals']);
            if ($result->getPlayer3() !== null && $result->getPlayer4() !== null) {
                $this->playerStatistics->addPlayerStatistics($result->getPlayer3(), $statisticsResult['p3_victories'],
                    0,$statisticsResult['team1_goals'], $statisticsResult['team2_goals']);
            }
        }

        foreach ($team2WinCountsResults as $statisticsResult) {
            /* @var Result $result */
            $result = $statisticsResult[0];
            $this->playerStatistics->addPlayerStatistics($result->getPlayer2(), $statisticsResult['p2_victories'], 0,
                $statisticsResult['team2_goals'], $statisticsResult['team1_goals']);
            if ($result->getPlayer4() !== null) {
                $this->playerStatistics->addPlayerStatistics($result->getPlayer4(), $statisticsResult['p2_victories'], 0,
                    $statisticsResult['team2_goals'], $statisticsResult['team1_goals']);
            }
        }

        foreach ($team1LossCountsResults as $statisticsResult) {
            /* @var Result $result */
            $result = $statisticsResult[0];
            $this->playerStatistics->addPlayerStatistics($result->getPlayer1(), 0, $statisticsResult['p1_losses'],
                $statisticsResult['team1_goals'], $statisticsResult['team2_goals']);
            if ($result->getPlayer3() !== null) {
                $this->playerStatistics->addPlayerStatistics($result->getPlayer3(), 0, $statisticsResult['p1_losses'],
                    $statisticsResult['team1_goals'], $statisticsResult['team2_goals']);
            }
        }

        foreach ($team2LossCountsResults as $statisticsResult) {
            /* @var Result $result */
            $result = $statisticsResult[0];
            $this->playerStatistics->addPlayerStatistics($result->getPlayer2(), 0, $statisticsResult['p2_losses'],
                $statisticsResult['team2_goals'], $statisticsResult['team1_goals']);
            if ($result->getPlayer4() !== null) {
                $this->playerStatistics->addPlayerStatistics($result->getPlayer4(), 0, $statisticsResult['p2_losses'],
                    $statisticsResult['team2_goals'], $statisticsResult['team1_goals']);
            }
        }
    }
}