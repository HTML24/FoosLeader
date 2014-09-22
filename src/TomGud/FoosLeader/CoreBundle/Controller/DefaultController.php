<?php

namespace TomGud\FoosLeader\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
    	$result_repo = $this->getDoctrine()->getManager()->getRepository('FoosLeaderCoreBundle:Result');
    	$latest_for_user = $result_repo->getLatestForUser($this->getUser());
    	$latest_for_all = $result_repo->getLatestForAll();
    	$unconfirmed_for_user = $result_repo->getUnconfirmedResultsForUser($this->getUser());

        $elo_repo = $this->getDoctrine()->getManager()->getRepository('FoosLeaderCoreBundle:ELOHistory');
        $elo_history = $elo_repo->findEloHistoryForPlayer($this->getUser());

        $elo_history_all = $elo_repo->findEloHistoryForAll();  // move this

        return $this->render('FoosLeaderCoreBundle:Default:index.html.twig',
        	array(
        		'latest_user' => $latest_for_user,
        		'latest_all' => $latest_for_all,
        		'unconfirmed' => $unconfirmed_for_user,
        		'notifications' => array(),
                'elo_history' => $elo_history,
                'elo_history_all' => $elo_history_all, // move this
        	)
        );
    }
}
