<?php

namespace TomGud\FoosLeader\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use TomGud\FoosLeader\CoreBundle\Entity\Result;
use TomGud\FoosLeader\CoreBundle\Entity\ELOHistory;
use TomGud\FoosLeader\CoreBundle\Entity\ResultConfirmation;

class ResultController extends Controller
{
    public function newAction()
    {
    	// Show all users in a drop down
    	$user_repo = $this->getDoctrine()->getManager()->getRepository('FoosLeaderUserBundle:User');
    	$users = $user_repo->findByRole('ROLE_PLAYER');

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
    	if ($result === null) {
    		throw new NotFoundHttpException('Result does not exist');
    	}

        $userConfirmed = ($result->userInTeam1($user) && $result->getTeam1Confirmed()) ||
            ($result->userInTeam2($user) && $result->getTeam2Confirmed());
        $canBeDeleted = !$result->getTeam1Confirmed() && !$result->getTeam2Confirmed();

        $victors = $result->getVictors();
    	$losers = $result->getLosers();

    	// TODO: Create a service that generates random messages that describe games
    	$two_player_message = 'After a very tight match, where __LOSER__ put up a good fight, __VICTOR__ emerged victorious.';
    	$four_player_message = '__VICTOR_ONE__ and __VICTOR_TWO__ proved their worth by showing __LOSER_ONE__ and __LOSER_TWO__ '
    		. 'how this game is played.';
    	if (count($victors) === 1) {
    		// Two player result
    		$game_description = $two_player_message;
    		$game_description = str_replace('__VICTOR__', $victors[0]->getUsername(), $game_description);
    		$game_description = str_replace('__LOSER__', $losers[0]->getUsername(), $game_description);
    	} else {
    		// Four player result
    		$game_description = $four_player_message;
    		$game_description = str_replace('__VICTOR_ONE__', $victors[0]->getUsername(), $game_description);
    		$game_description = str_replace('__VICTOR_TWO__', $victors[1]->getUsername(), $game_description);
    		$game_description = str_replace('__LOSER_ONE__', $losers[0]->getUsername(), $game_description);
    		$game_description = str_replace('__LOSER_TWO__', $losers[1]->getUsername(), $game_description);
    	}


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
            $elo_history_repo = $em->getRepository('FoosLeaderCoreBundle:ELOHistory');
            $elo_histories = $elo_history_repo->findBy(array('result' => $result));
            foreach ($elo_histories as $elo_history) {
                $em->remove($elo_history);
            }

            $em->remove($result);
            $em->flush();
            return new JsonResponse(true);
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
    }

    public function updateELOScores(Result $result)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $historyRepo = $em->getRepository('FoosLeaderCoreBundle:ELOHistory');

        $players = array($result->getPlayer1(), $result->getPlayer2(), $result->getPlayer3(), $result->getPlayer4());
        foreach ($players as $player)
        {
            if ($player === null) {
                continue;
            }

            $history = $historyRepo->findOneBy(array('player' => $player, 'result' => $result));
            if ($history !== null) {
                // Start by checking in the past
                $parentResult = $history->getParent();
                if ($parentResult !== null && !$parentResult->getResult()->isConfirmed()) {
                    // Parent history's result is not confirmed so we can't update scores for this player
                    continue;
                } else if ($parentResult !== null && $parentResult->getResult()->isConfirmed()) {
                    // Parent history's result is confirmed, let's update current history
                    // Let's find out if there are newer elo history records that have a confirmed result
                    $childHistory = $historyRepo->findOneBy(array('parent' => $history));
                    while ($childHistory !== null) {
                        if ($childHistory->getResult()->isConfirmed()) {
                            $childHistory = $historyRepo->findOneBy(array('parent' => $childHistory));
                        } else {
                            break;
                        }
                    }
                    // Check on if the child history we arrived at is confirmed
                    if ($childHistory !== null) {
                        if ($childHistory->getResult->isConfirmed()) {
                            // Found a child history and it has been confirmed
                            $player->setELORanking($history->getNewELO());
                        }
                    } else if ($history->getResult()->isConfirmed()) {
                        // no confirmed child history found, check if the current result's history is confirmed
                        $player->setELORanking($history->getNewELO());
                    }
                    $em->persist($player);
                }
            }
        }
        $em->flush();
    }
}
