<?php

namespace TomGud\FoosLeader\UserBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Controller\ProfileController as BaseController;

class ProfileController extends BaseController
{
	public function showAction()
	{
		$user = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $eloHistoryRepo = $this->container->get('doctrine.orm.entity_manager')->getRepository('FoosLeaderCoreBundle:ELOHistory');
        $resultRepo = $this->container->get('doctrine.orm.entity_manager')->getRepository('FoosLeaderCoreBundle:Result');
        $elo_repo = $this->container->get('doctrine.orm.entity_manager')->getRepository('FoosLeaderCoreBundle:ELOHistory');

        $eloHistories = $eloHistoryRepo->findBy(array('player' => $user), array('id' => 'DESC'));
        $goalStatistics = $resultRepo->getGoalStatisticsForPlayer($user);
        $gameStatistics = $resultRepo->getGameStatisticsForPlayer($user);
        $elo_history = $elo_repo->findEloHistoryForPlayer($user);

        $paginator = $this->container->get('knp_paginator');
        $pagination =$paginator->paginate($eloHistories, $this->container->get('request')->get('page', 1),  5); //

        return $this->container->get('templating')->renderResponse(
            'FOSUserBundle:Profile:show.html.twig',
        	array(
                'user' => $user,
                'pagination' => $pagination,
                'goalStatistics' => $goalStatistics,
                'gameStatistics' => $gameStatistics,
                'elo_history' => $elo_history,
            )
        );
    }
}
