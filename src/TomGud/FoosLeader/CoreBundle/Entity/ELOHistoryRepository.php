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
            ->orderBy('eh.registered', 'DESC')
            ->setParameter('currentDate', date('Y-m-d', strtotime("now -30 days") ));
        $results = $qb->getQuery()->getResult();
        return $results;
    }

    /**
     * @param int $id
     * @return ELOHistory[]
     */
    public function getAllAfter($id)
    {
        $qb = $this->createQueryBuilder('eh')
            ->where('eh.id > :id')
            ->orderBy('eh.id', 'ASC')
            ->setParameter('id', $id);
        $results = $qb->getQuery()->getResult();
        return $results;

    }

}
