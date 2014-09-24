<?php

namespace TomGud\FoosLeader\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use TomGud\FoosLeader\CoreBundle\Entity\Result;
use TomGud\FoosLeader\CoreBundle\Entity\ELOHistory;

class ResultController extends Controller
{
    public function newAction()
    {
    	// Show all users in a drop down
    	$user_repo = $this->get('doctrine.orm.entity_manager')->getRepository('FoosLeaderUserBundle:User');
    	$users = $user_repo->findAll();

        $result = new Result();
        $form = $this->createFormBuilder($result)
            ->getForm();

        if (count($users) < 2) {
            $this->get('session')->getFlashBag()->add('error', "There aren't enough players to register a result.
                Please wait for more users to join");
        } else if (count($users) < 4) {
            $this->get('session')->getFlashBag()->add('warning', "There aren't enough players to register a team result.
                Please wait for more users to join");
        }

        return $this->render('FoosLeaderCoreBundle:Result:new.html.twig',
        	array(
        		'users' => $users,
        		'form' => $form->createView()
        		)
        	);
    }

    public function createAction(Request $request)
    {

    	$p_player_1 = $request->get('player_1');
		$p_player_2 = $request->get('player_2');
		$p_player_3 = $request->get('player_3');
		$p_player_4 = $request->get('player_4');
		$p_score_1 = $request->get('score_1');
		$p_score_2 = $request->get('score_2');

		// Validation
		if (intval($p_score_1) < 0 || intval($p_score_1) > 10  ||
			intval($p_score_2) < 0 || intval($p_score_2) > 10) {
			// Not a valid score was entered
			// Set flash bag error message
			$this->get('session')->getFlashBag()->add(
		        'error',
		        'Scores must be positive integers between 0 and 10 (inclusive)'
		    );
			return $this->redirect($this->generateUrl('new_result'));
		}
		if ($p_player_1 === $p_player_2 || $p_player_1 === $p_player_3 || $p_player_1 === $p_player_4 ||
			$p_player_2 === $p_player_3 || $p_player_2 === $p_player_4 ||
			($p_player_3 === $p_player_4 && $p_player_3 !== 'null')) {
			// Player 1 and Player 2 are the same player
			// Player 1 and Player 3 or Player 4 are the same player
			// Player 2 and Player 3 or Player 4 are the same player
			// Player 3 and Player 4 are the same player if they have been set
			// Set flash bag error message
			$this->get('session')->getFlashBag()->add(
		        'error',
		        'You can not choose the same player twice.'
		    );
			return $this->redirect($this->generateUrl('new_result'));
		}
		// Create the Result
		$new_result = new Result();

		// Find the users
		$user_repo = $this->getDoctrine()->getManager()->getRepository('FoosLeaderUserBundle:User');
    	$player_1 = $user_repo->find($p_player_1);
    	$player_2 = $user_repo->find($p_player_2);
    	$player_3 = $player_4 = null;
    	if ($p_player_3 !== 'none-3' && $p_player_4 !== 'none-4') {
    		$player_3 = $user_repo->find($p_player_3);
    		$player_4 = $user_repo->find($p_player_4);
    		if ($player_3 === null || $player_4 === null) {
    			// Set flash bag error message
    			$this->get('session')->getFlashBag()->add(
			        'error',
			        'Could not find player 3 or player 4.'
			    );
    			return $this->redirect($this->generateUrl('new_result'));
    		} else {
    			$new_result->setPlayer3($player_3);
    			$new_result->setPlayer4($player_4);
    		}
    	}
    	if ($player_1 === null || $player_2 === null) {
   			// Set flash bag error message
   			$this->get('session')->getFlashBag()->add(
		        'error',
		        'Could not find player 1 or 2.'
		    );
    		return $this->redirect($this->generateUrl('new_result'));
    	}

        $user = $this->getUser();
        if ($player_1 !== $user && $player_2 !== $user && $player_3 !== $user && $player_4 !== $user) {
            $this->get('session')->getFlashBag()->add(
                'error',
                'Can not create a match which you (' . $user->getUsername() . ') did not participate in.'
            );
            return $this->redirect($this->generateUrl('new_result'));
        }

		// Set the values for the Result
		$new_result->setPlayer1($player_1);
		$new_result->setPlayer2($player_2);
		$new_result->setTeam1Score($p_score_1);
		$new_result->setTeam2Score($p_score_2);
		$new_result->setSubmitted(new \Datetime('now', new \DateTimeZone('UTC')));
        $new_result->setTeam1Confirmed(true);
        $new_result->setTeam2Confirmed(true);
        $this->getDoctrine()->getManager()->persist($new_result);
        $this->getDoctrine()->getManager()->flush();
        $this->createEloHistory($new_result);

		// Redirect to detail page for Result
		return $this->redirect($this->generateUrl('detail_result', array('id' => $new_result->getId())));
    }

    public function detailAction($id) {
        $user = $this->getUser();
    	$result_repo = $this->getDoctrine()->getManager()->getRepository('FoosLeaderCoreBundle:Result');
    	$result = $result_repo->find($id);
        $messageService = $this->get('foos_leader.message_service');

    	if ($result === null) {
    		throw new NotFoundHttpException('Result does not exist');
    	}

        $userConfirmed = ($result->userInTeam1($user) && $result->getTeam1Confirmed()) ||
            ($result->userInTeam2($user) && $result->getTeam2Confirmed());
        $canBeDeleted = !$result->getTeam1Confirmed() && !$result->getTeam2Confirmed();

        //MessageService
        $game_description = $messageService->getMessageForScore($result);

    	return $this->render('FoosLeaderCoreBundle:Result:detail.html.twig',
        	array(
        		'game_description' => $game_description,
        		'result' => $result,
                'userParticipated' => $result->userParticipating($user),
                'userConfirmed' => $userConfirmed,
                'canBeDeleted' => $canBeDeleted,
        		)
        	);
    }

    public function invalidateResultAction($id) {
        $user = $this->getUser();
        if (null === $user) {
            throw new AccessDeniedException('Must be logged in for this route!');
        }
        $em = $this->get('doctrine.orm.entity_manager');
        $result_repo = $em->getRepository('FoosLeaderCoreBundle:Result');

        $result = $result_repo->find($id);
        if (null === $result) {
            throw new NotFoundHttpException('Result not found');
        }

        if ($result->userParticipating($user)) {
            if ($result->userInTeam1($user)) {
                $result->setTeam1Confirmed(false);
            } else {
                $result->setTeam2Confirmed(false);
            }
            $em->persist($result);
            $em->flush();

            return new JsonResponse(true);
        } else {
            throw new AccessDeniedException('User did not participate in this result.');
        }
    }

    public function deleteAction($id) {
        $user = $this->getUser();
        if (null === $user) {
            throw new AccessDeniedException('Must be logged in for this route!');
        }
        $em = $this->get('doctrine.orm.entity_manager');
        $result_repo = $em->getRepository('FoosLeaderCoreBundle:Result');

        $result = $result_repo->find($id);
        if (null === $result) {
            throw new NotFoundHttpException('Result not found');
        }

        if ($result->userParticipating($user) && !$result->getTeam1Confirmed() && !$result->getTeam2Confirmed()) {
            // Request performed by logged in user who participated in this result, and both team have
            // invalidated the result
            $this->updateELOScores($result);
            return $this->redirect($this->generateUrl('home'));
        } else {
            throw new AccessDeniedException('Can not delete: conditions not met. User participating, both teams disputed');
        }
    }

    public function confirmAction($id) {
        $em = $this->get('doctrine.orm.entity_manager');
        $result_repo = $em->getRepository('FoosLeaderCoreBundle:Result');

        if ($this->getUser() === null) {
            throw new \Exception('Must be logged in for this route!');
        }

        // Find the actual result
        $result = $result_repo->find($id);
        if (!$result) {
            throw new \Exception('Result not found');
        }

        // Check if the user participated in this result
        if ($result->userParticipating($this->getUser())) {
            // Found a result that this user can confirm!
            if ($result->userInTeam1($this->getUser())) {
                $result->setTeam1Confirmed(true);
            } else {
                $result->setTeam2Confirmed(true);
            }
            $em->persist($result);
            $em->flush();
            return new JsonResponse(true);
        } else {
            throw new \Exception('User did not participate in this game');
        }
    }

    public function createEloHistory(Result $result)
    {
        $eloCalculator = $this->get('foos_leader.elo_calculator');
        $em = $this->get('doctrine.orm.entity_manager');
        $eloHistoryRepo = $em->getRepository('FoosLeaderCoreBundle:ELOHistory');

        $allPlayers = array($result->getPlayer1(), $result->getPlayer2(), $result->getPlayer3(), $result->getPlayer4());
        $eloHistories = array();
        foreach ($allPlayers as $player) {
            if ($player === null) {
                continue;
            }

            $parentELOHistory = $eloHistoryRepo->findNewestRanking($player);
            $ELOHistory = new ELOHistory();
            $ELOHistory->setPlayer($player);
            $ELOHistory->setParent($parentELOHistory);
            $ELOHistory->setResult($result);
            $ELOHistory->setRegistered((new \DateTime('now', new \DateTimeZone('UTC'))));
            if ($parentELOHistory === null) {
                $ELOHistory->setOldElo($player->getELORanking());
            } else {
                $ELOHistory->setOldElo($parentELOHistory->getNewELO());
            }
            $eloHistories[$player->getId()] = $ELOHistory;
        }

        $newELORankings = $eloCalculator->calculateELOForResult($result, $eloHistories);
        foreach ($allPlayers as $player) {
            if ($player === null) {
                continue;
            }
            $eloHistories[$player->getId()]->setNewElo($newELORankings[$player->getId()]->getNewELO());
            $player->setELORanking($newELORankings[$player->getId()]->getNewELO());
            $em->persist($eloHistories[$player->getId()]);
        }

        $em->flush();

        // send request to leaderboard

        $leaderboardService = $this->get('foos_leader.leader_board');
        $leaderboardService->sendLeaderBoardRequest();
    }

    public function updateELOScores(Result $result)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $elo_history_repo = $em->getRepository('FoosLeaderCoreBundle:ELOHistory');
        $result_repo = $em->getRepository('FoosLeaderCoreBundle:Result');
        $elo_histories = $result->getELOHistories();
        $results = $result_repo->getAllAfter($result);

        if (count($elo_histories) > 0) {
            // Have some elo histories to go through
            $lowest_id_elo_history = $elo_histories[0];
            $invalid_elo_histories = $elo_history_repo->getAllAfter($lowest_id_elo_history->getId());
            $players_found = array();
            // Make sure all elo histories are deleted
            foreach ($elo_histories as $elo_history) {
                // Update the players ELO history to what it was before
                $elo_history->getPlayer()->setELORanking($elo_history->getOldElo());
                $players_found[$elo_history->getPlayer()->getId()] = true;
                $em->persist($elo_history->getPlayer());
                $em->remove($elo_history);
            }
            // Delete all older elo histories
            foreach ($invalid_elo_histories as $elo_history) {
                $player = $elo_history->getPlayer();
                if (!array_key_exists($player->getId(), $players_found)) {
                    $players_found[$player->getId()] = true;
                    $player->setELORanking($elo_history->getOldElo());
                }
                $em->remove($elo_history);
            }

        }
        $em->remove($result);
        $em->flush();

        foreach ($results as $key => $current_result) {
            $this->createEloHistory($current_result);
        }
    }

    public function claimShowAction($id) {
        // Show all users in a drop down
        $user_repo = $this->get('doctrine.orm.entity_manager')->getRepository('FoosLeaderUserBundle:User');
        $result_repo = $this->get('doctrine.orm.entity_manager')->getRepository('FoosLeaderCoreBundle:Result');
        $users = $user_repo->findAll();

        $result = $result_repo->find($id);

        if (count($users) < 2) {
            $this->get('session')->getFlashBag()->add('error', "There aren't enough players to register a result.
                Please wait for more users to join");
        } else if (count($users) < 4) {
            $this->get('session')->getFlashBag()->add('warning', "There aren't enough players to register a team result.
                Please wait for more users to join");
        }

        return $this->render('FoosLeaderCoreBundle:Result:claim.html.twig',
            array(
                'users' => $users,
                'result' => $result,
            )
        );
    }

    public function claimAction($id, Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $result_repo = $em->getRepository('FoosLeaderCoreBundle:Result');
        $user_repo = $em->getRepository('FoosLeaderUserBundle:User');

        $p_player_1 = $request->get('player_1');
        $p_player_2 = $request->get('player_2');
        $p_player_3 = $request->get('player_3');
        $p_player_4 = $request->get('player_4');

        if ($p_player_1 === $p_player_2 || $p_player_1 === $p_player_3 || $p_player_1 === $p_player_4 ||
            $p_player_2 === $p_player_3 || $p_player_2 === $p_player_4 ||
            ($p_player_3 === $p_player_4 && $p_player_3 !== 'null')
        ) {
            // Player 1 and Player 2 are the same player
            // Player 1 and Player 3 or Player 4 are the same player
            // Player 2 and Player 3 or Player 4 are the same player
            // Player 3 and Player 4 are the same player if they have been set
            // Set flash bag error message
            $this->get('session')->getFlashBag()->add(
                'error',
                'You can not choose the same player twice.'
            );
            return $this->redirect($this->generateUrl('new_result'));
        }

        // Find the result
        $result = $result_repo->find($id);
        if ($result === null) {
            // Not found exception?
            throw new NotFoundHttpException('Result not found. Can not claim it.');
        }

        // Find the users
        $player_1 = $user_repo->find($p_player_1);
        $player_2 = $user_repo->find($p_player_2);
        $player_3 = $player_4 = null;
        if ($p_player_3 !== 'none-3' && $p_player_4 !== 'none-4') {
            $player_3 = $user_repo->find($p_player_3);
            $player_4 = $user_repo->find($p_player_4);
            if ($player_3 === null || $player_4 === null) {
                // Set flash bag error message
                $this->get('session')->getFlashBag()->add(
                    'error',
                    'Could not find player 3 or player 4.'
                );
                return $this->redirect($this->generateUrl('claim_result_show', array('id' => $id)));
            } else {
                $result->setPlayer3($player_3);
                $result->setPlayer4($player_4);
            }
        }
        if ($player_1 === null || $player_2 === null) {
            // Set flash bag error message
            $this->get('session')->getFlashBag()->add(
                'error',
                'Could not find player 1 or 2.'
            );
            return $this->redirect($this->generateUrl('claim_result_show', array('id' => $id)));
        }

        $user = $this->getUser();
        if ($player_1 !== $user && $player_2 !== $user && $player_3 !== $user && $player_4 !== $user) {
            $this->get('session')->getFlashBag()->add(
                'error',
                'Can not create a match which you (' . $user->getUsername() . ') did not participate in.'
            );
            return $this->redirect($this->generateUrl('claim_result_show', array('id' => $id)));
        }

        // Set the values for the Result
        $result->setPlayer1($player_1);
        $result->setPlayer2($player_2);
        $result->setSubmitted(new \Datetime('now', new \DateTimeZone('UTC')));
        $result->setTeam1Confirmed(true);
        $result->setTeam2Confirmed(true);
        $em->persist($result);
        $em->flush();
        $this->createEloHistory($result);

        // Redirect to detail page for Result
        return $this->redirect($this->generateUrl('detail_result', array('id' => $result->getId())));
    }
}
