<?php
/**
 * Created by PhpStorm.
 * User: tomas
 * Date: 28/04/15
 * Time: 17:31
 */

namespace TomGud\FoosLeader\CoreBundle\Service;


use Doctrine\ORM\EntityManagerInterface;

class StatisticsService {
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
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
}