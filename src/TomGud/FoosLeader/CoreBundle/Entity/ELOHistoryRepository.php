<?php

namespace TomGud\FoosLeader\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Exception\NoResultException;
use TomGud\FoosLeader\UserBundle\Entity\User;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * ELOHistoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ELOHistoryRepository extends EntityRepository
{
	public function findNewestRanking(User $player)
	{
		 $qb = $this
            ->createQueryBuilder('eh')
            ->where('eh.player = :player')
            ->orderBy('eh.parent', 'DESC')
            ->setParameter('player', $player);
        $results = $qb->getQuery()->getResult();
        if (count($results) > 0) {
        	return array_shift($results);
        } else {
        	return null;
        }
	}

    public function findPaginatedResultsForPlayer(User $player, $start, $pageSize)
    {
        $query = $this->createQueryBuilder('eh')    // Example - $qb->innerJoin('u.Group', 'g', 'WITH', 'u.status = ?1')
            ->leftJoin('eh.result', 'r')
            ->setFirstResult($start)
            ->setMaxResults($pageSize)
            ->where('eh.player = :p')
            ->setParameter('p', $player)
            ->orderBy('r.submitted', 'DESC');
        $paginator = new Paginator($query);
        return $paginator;
    }

    public function findEloHistoryForPlayer(User $player){
        $qb = $this
            ->createQueryBuilder('eh')
            ->where('eh.player = :player')
            ->orderBy('eh.registered', 'ASC')
            ->setParameter('player', $player)
            ->setMaxResults(30);
        $results = $qb->getQuery()->getResult();
        return $results;
    }

    public function findEloHistoryForAll(){
        $qb = $this
            ->createQueryBuilder('eh')
            ->where('eh.registered > :currentDate')
            ->orderBy('eh.registered', 'ASC')
            ->setParameter('currentDate', date('Y-m-d', strtotime("now -30 days") ));
        $results = $qb->getQuery()->getResult();
        return $results;
    }

}
