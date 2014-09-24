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
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class APIController extends FOSRestController {
    /**
     *
     * @ApiDoc(
     *   resource=false,
     *   description="Create an available result in FoosLeader. The available result will be presented in the
                   system for users to claim once they have logged in. Made for incorporation of systems that
                   are unaware of users in the FoosLeader such as a goal counting foosball table.",
     *   parameters = {
     *    {"name"="whiteScore", "dataType"="numbers", "required"=true, "description"="The score of the white team", "format"="[0-9]+"},
     *    {"name"="redScore", "dataType"="numbers", "required"=true, "description"="The score of the red team", "format"="[0-9]+"},
     *    {"name"="hash", "dataType"="string", "required"=true, "description"="md5(date(Y-m-d) + key + whiteScore + redScore)"}
     *   },
     *   statusCodes = {
     *      200 = "Returned when successful",
     *      400 = "Returned when bad request is made and parameters are not correct."
     *   }
     *  )
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAvailableResultAction(Request $request)
    {
        $whiteScore = $request->get('whiteScore');
        $redScore = $request->get('redScore');
        $hashRequest = $request->get('hash');
        $view = new View();
        $view->setFormat('json');

        if (null === $whiteScore || null === $redScore || null === $hashRequest) {
            $view
                ->setStatusCode(400)
                ->setData(array('message' => 'Malformed request. Missing either white score, red score or the key.'));
            return $this->handleView($view);
        }


        if (is_numeric($whiteScore) && is_numeric($redScore)) {
            $whiteScore = (int) $whiteScore;
            $redScore = (int) $redScore;
        } else {
            $view
                ->setStatusCode(400)
                ->setData(array('message' => 'Malformed request. Red score or white score are non numeric.'));
            return $this->handleView($view);
        }
        $apiKey = $this->container->getParameter('api_key');
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $hashInput = $date->format('Y-m-d') . $apiKey . $whiteScore . $redScore;
        $hash = md5($hashInput);
        if ($hashRequest !== $hash) {
            $view
                ->setStatusCode(400)
                ->setData(array('message' => 'Malformed request. Hash is not correct.'));
            return $this->handleView($view);
        }

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
            'key' => $hashRequest,
            'message' => 'Thanks for the new result.',
        );



        $view->setStatusCode(200)->setData($result);
        return $this->handleView($view);

    }
}
