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


        // move and change into nice code these lines bellow
        if((float)$gameStatistics->getGames() > 0){
            $WinRatio = (((float)$gameStatistics->getWon() * 100) / (float)$gameStatistics->getGames());
        } else{
            $WinRatio = 0;
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


        return $this->container->get('templating')->renderResponse(
            'FOSUserBundle:Profile:show.html.twig',
        	array(
                'user' => $user,
                'pagination' => $pagination,
                'goalStatistics' => $goalStatistics,
                'gameStatistics' => $gameStatistics,
                'elo_history' => $elo_history,
                // move and change into nice code these lines bellow
                'WinRatio' =>  $WinRatio,
                'ScoreRatio' => $ScoreRatio,
                'AvgScore' => $AvgScore,
                'AvgConceded' => $AvgConceded,
            )
        );
    }
}
