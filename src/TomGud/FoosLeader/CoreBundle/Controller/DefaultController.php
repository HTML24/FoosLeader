<?php

namespace TomGud\FoosLeader\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TomGud\FoosLeader\CoreBundle\Entity\ELOHistory;

class DefaultController extends Controller
{
    public function indexAction()
    {
    	$result_repo = $this->get('doctrine.orm.entity_manager')->getRepository('FoosLeaderCoreBundle:Result');
    	$latest_for_user = $result_repo->getLatestForUser($this->getUser());
    	$latest_for_all = $result_repo->getLatestForAll();
    	$unconfirmed_for_user = $result_repo->getUnconfirmedResultsForUser($this->getUser());

        $available_results = $result_repo->getAvailableResults();

        return $this->render('FoosLeaderCoreBundle:Default:index.html.twig',
        	array(
        		'latest_user' => $latest_for_user,
        		'latest_all' => $latest_for_all,
        		'unconfirmed' => $unconfirmed_for_user,
                'availables' => $available_results,
        		'notifications' => array()
        	)
        );
    }
}
