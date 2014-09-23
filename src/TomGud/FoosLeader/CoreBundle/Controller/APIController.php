<?php
/**
 * User: Tomas
 * Date: 9/23/2014
 * Copyright Html24
 */

namespace TomGud\FoosLeader\CoreBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use TomGud\FoosLeader\CoreBundle\Entity\Result;

class APIController extends FOSRestController {

    public function createAvailableResultAction(Request $request)
    {
        $whiteScore = $request->get('white_score');
        $redScore = $request->get('red_score');
        $key = $request->get('key');
        $view = new View();
        $view->setFormat('json');

        if (null === $whiteScore || null === $redScore || null === $key) {
            $view
                ->setStatusCode(400)
                ->setData(array('message' => 'Malformed request. Missing either white score, red score or the key.'));
            return $this->handleView($view);
        }

        //TODO: Verify data

        $result = new Result();
        $result->setTeam1Score($redScore);
        $result->setTeam2Score($whiteScore);
        $result->setSubmitted(new \DateTime('now', new \DateTimeZone('UTC')));
        $em = $this->get('doctrine.orm.entity_manager');
        $em->persist($result);
        $em->flush();

        $result = array(
            'white' => $whiteScore,
            'red' => $redScore,
            'key' => $key,
            'message' => 'Thanks for the new result.',
        );



        $view->setStatusCode(200)->setData($result);
        return $this->handleView($view);

    }
}
