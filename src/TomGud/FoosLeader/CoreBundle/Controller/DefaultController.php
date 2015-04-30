<?php

namespace TomGud\FoosLeader\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TomGud\FoosLeader\CoreBundle\Entity\ELOHistory;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
    	$result_repo = $em->getRepository('FoosLeaderCoreBundle:Result');
    	$latest_for_user = $result_repo->getLatestForUser($this->getUser());
    	$latest_for_all = $result_repo->getLatestForAll();
    	$disputed_for_user = $result_repo->getUnconfirmedResultsForUser($this->getUser());

        $statistics = $this->get('foos_leader.statistics');
        $top_players = array();
        foreach ($statistics->getTopPlayers(2) as $player) {
            $top_players[] = $player;
        }

        $available_results = $result_repo->getAvailableResults();
        $available_results_remaining = array();

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        foreach ($available_results as $result) {
            $difference = $now->diff($result->getSubmitted());
            if ($difference->invert === 1 && $difference->days >= 30) {
                // Month old result not been claimed, let's delete
                $em->remove($result);
            } else {
                $available_results_remaining[] = $result;
            }
        }
        $em->flush();

        return $this->render('FoosLeaderCoreBundle:Default:index.html.twig',
        	array(
        		'latest_user' => $latest_for_user,
        		'latest_all' => $latest_for_all,
                'availables' => $available_results_remaining,
                'disputed' => $disputed_for_user,
                'total_games' => $statistics->getTotalGames(),
                'total_goals' => $statistics->getTotalGoals(),
                'top_players' => $top_players,
        		'notifications' => array()
        	)
        );
    }

    public function disputesAction()
    {
        return $this->render('FoosLeaderCoreBundle:Default:disputes.html.twig');
    }
}
