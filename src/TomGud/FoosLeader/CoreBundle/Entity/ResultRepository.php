<?php

namespace TomGud\FoosLeader\CoreBundle\Entity;


use Doctrine\ORM\EntityRepository;
use TomGud\FoosLeader\UserBundle\Entity\User;
use TomGud\FoosLeader\CoreBundle\Model\PlayerGoalStatistics;
use TomGud\FoosLeader\CoreBundle\Model\PlayerGameStatistics;

/**
 * ResultRepository
 */
class ResultRepository extends EntityRepository
{
	public function getLatestForUser(User $user) {
        $q = $this
            ->createQueryBuilder('r')
            ->where('(r.player1 = :user OR r.player2 = :user OR r.player3 = :user OR r.player4 = :user)
                AND (r.team1Confirmed = true AND r.team2Confirmed = true)')
            ->orderBy('r.submitted', 'DESC')
            ->setParameter('user', $user)
            ->getQuery();

		$results = $q->getResult();
        return $results;

	}
	public function getLatestForAll() {
		$q = $this
            ->createQueryBuilder('r')
            ->where('r.team1Confirmed = true and r.team2Confirmed = true')
            ->orderBy('r.submitted', 'DESC')
            ->getQuery();

		$results = $q->getResult();
        return $results;
	}

    public function getUnconfirmedResultsForUser(User $user) {
        $qb = $this
            ->createQueryBuilder('r')
            ->where('((r.player1 = :user OR r.player3 = :user) AND r.team1Confirmed = false) OR
                    ((r.player2 = :user OR r.player4 = :user) AND r.team2Confirmed = false)')
            ->orderBy('r.submitted', 'DESC')
            ->setParameter('user', $user);

        $results = $qb->getQuery()->getResult();
        return $results;
    }


    /**
     * Get goal statistics for a player
     * @param  User   $player
     * @return PlayerGoalStatistics
     */
    public function getGoalStatisticsForPlayer(User $player) {
        $qT1 = $this
            ->createQueryBuilder('r')
            ->add('select', 'SUM(r.team1Score) as scored, SUM(r.team2Score) as conceded')
            ->add('from', 'FoosLeaderCoreBundle:Result r')
            ->add('where', '((r.player1 = :user OR r.player3 = :user) AND r.team1Confirmed = true AND r.team2Confirmed = true)')
            ->setParameter('user', $player)
            ->getQuery();
        $qT2 = $this
            ->createQueryBuilder('r')
            ->add('select', 'SUM(r.team2Score) as scored, SUM(r.team1Score) as conceded')
            ->add('from', 'FoosLeaderCoreBundle:Result r')
            ->add('where', '((r.player2 = :user OR r.player4 = :user) AND r.team1Confirmed = true AND r.team2Confirmed = true)')
            ->setParameter('user', $player)
            ->getQuery();

        $resultT1 = $qT1->getResult();
        $resultT2 = $qT2->getResult();

        if ((is_array($resultT1) && count($resultT1) === 0) || (is_array($resultT2) && count($resultT2) === 0)) {
            return null;
        } else {
            $statistics = new PlayerGoalStatistics(
                $player,
                ($resultT1[0]['scored'] + $resultT2[0]['scored']),
                ($resultT1[0]['conceded'] + $resultT2[0]['conceded'])
            );
            return $statistics;
        }
    }

    public function getGameStatisticsForPlayer(User $player)
    {
        $qT1 = $this
            ->createQueryBuilder('r')
            ->add('select', 'COUNT(r.id) as won')
            ->add('from', 'FoosLeaderCoreBundle:Result r')
            ->add('where', '((r.player1 = :user OR r.player3 = :user) AND r.team1Confirmed = true AND r.team2Confirmed = true AND r.team1Score > r.team2Score) OR ' .
                '((r.player2 = :user OR r.player4 = :user) AND r.team1Confirmed = true AND r.team2Confirmed = true AND r.team2Score > r.team1Score)')
            ->setParameter('user', $player)
            ->getQuery();
        $qT2 = $this
            ->createQueryBuilder('r')
            ->add('select', 'COUNT(r.id) as games')
            ->add('from', 'FoosLeaderCoreBundle:Result r')
            ->add('where', '((r.player2 = :user OR r.player4 = :user OR r.player1 = :user OR r.player3 = :user) AND r.team1Confirmed = true AND r.team2Confirmed = true)')
            ->setParameter('user', $player)
            ->getQuery();

        $resultT1 = $qT1->getResult();
        $resultT2 = $qT2->getResult();

        if ((is_array($resultT1) && count($resultT1) === 0) || (is_array($resultT2) && count($resultT2) === 0)) {
            return null;
        } else {
            $statistics = new PlayerGameStatistics(
                $player,
                $resultT2[0]['games'],
                $resultT1[0]['won']
            );
            return $statistics;
        }
    }

    /**
     * @param Result $result
     * @return Result[]
     */
    public function getAllAfter(Result $result) {
        $qb = $this->createQueryBuilder('r')
            ->where('r.id > :id')
            ->andWhere('r.player1 IS NOT NULL OR r.player2 IS NOT NULL OR r.player3 IS NOT NULL OR r.player4 IS NOT NULL')
            ->orderBy('r.id', 'ASC')
            ->setParameter('id', $result->getId());
        $results = $qb->getQuery()->getResult();
        return $results;
    }

    /**
     * @return Result[]
     */
    public function getAvailableResults() {
        $qb = $this->createQueryBuilder('r')
            ->where('r.player1 IS NULL AND r.player2 IS NULL AND r.player3 IS NULL AND r.player4 IS NULL')
            ->andWhere('r.team1Confirmed = 0 AND r.team2Confirmed = 0')
            ->orderBy('r.submitted', 'DESC');
        return $qb->getQuery()->getResult();
    }
}
