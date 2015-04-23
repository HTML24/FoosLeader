<?php

namespace TomGud\FoosLeader\CoreBundle\Service;

use TomGud\FoosLeader\CoreBundle\Entity\ELOHistory;
use TomGud\FoosLeader\CoreBundle\Entity\Result;
use TomGud\FoosLeader\CoreBundle\Model\ELOPlayerResultModel;
use TomGud\FoosLeader\CoreBundle\Model\PossibleOutcomeModel;
use TomGud\FoosLeader\UserBundle\Entity\User;

class ELOCalculatorService
{
	/**
	 * Calculate new elo rankings for player participating in a result
	 * @param  Result $result       		The result needed for calculation
	 * @param  ELOHistory[] $eloHistories 	An array of elo history for each player
	 * @return ELOPlayerResultModel[]       An array of updated calculations
	 */
	public function calculateELOForResult(Result $result, $eloHistories)
	{
		$results = array();

		// Setting up the model
		$p1ELO = new ELOPlayerResultModel($result, $result->getPlayer1());
		$p2ELO = new ELOPlayerResultModel($result, $result->getPlayer2());
		$p1ELO->setCurrentELO($eloHistories[$p1ELO->getPlayer()->getId()]->getOldELO());
		$p2ELO->setCurrentELO($eloHistories[$p2ELO->getPlayer()->getId()]->getOldELO());
        if ($result->getPlayer3() === null || $result->getPlayer4() === null) {
            // 2 player ELO calculations
            $team1ChanceOfWin = 1 / (1 + pow(10, (($p2ELO->getCurrentELO() - $p1ELO->getCurrentELO())/400)));
            $team2ChanceOfWin = 1 - $team1ChanceOfWin;

            $player1Won = ($result->getTeam1Score() > $result->getTeam2Score()) ? 1 : 0;
            $player2Won = 1 - $player1Won;

            $p1ELO->setNewELO($p1ELO->getCurrentELO() + round($p1ELO->getPlayerK() * ($player1Won - $team1ChanceOfWin)));
            $p2ELO->setNewELO($p2ELO->getCurrentELO() + round($p2ELO->getPlayerK() * ($player2Won - $team2ChanceOfWin)));
        } else {
            // 4 player ELO calculations
			$p3ELO = new ELOPlayerResultModel($result, $result->getPlayer3());
			$p4ELO = new ELOPlayerResultModel($result, $result->getPlayer4());
			$p3ELO->setCurrentELO($eloHistories[$p3ELO->getPlayer()->getId()]->getOldELO());
			$p4ELO->setCurrentELO($eloHistories[$p4ELO->getPlayer()->getId()]->getOldELO());

            $team1ChanceOfWin = 1 / (1 + pow(10, (($p2ELO->getCurrentELO() + $p4ELO->getCurrentELO()
                - $p1ELO->getCurrentELO() - $p3ELO->getCurrentELO())/400)));
            $team2ChanceOfWin = 1 - $team1ChanceOfWin;

            $team1Won = ($result->getTeam1Score() > $result->getTeam2Score()) ? 1 : 0;
            $team2Won = 1 - $team1Won;

            $p1ELO->setNewELO($p1ELO->getCurrentELO() + round($p1ELO->getPlayerK() * ($team1Won - $team1ChanceOfWin)));
            $p3ELO->setNewELO($p3ELO->getCurrentELO() + round($p3ELO->getPlayerK() * ($team1Won - $team1ChanceOfWin)));
            $p2ELO->setNewELO($p2ELO->getCurrentELO() + round($p2ELO->getPlayerK() * ($team2Won - $team2ChanceOfWin)));
            $p4ELO->setNewELO($p4ELO->getCurrentELO() + round($p4ELO->getPlayerK() * ($team2Won - $team2ChanceOfWin)));

            $results[$p3ELO->getPlayer()->getId()] = $p3ELO;
            $results[$p4ELO->getPlayer()->getId()] = $p4ELO;
        }
        $results[$p1ELO->getPlayer()->getId()] = $p1ELO;
        $results[$p2ELO->getPlayer()->getId()] = $p2ELO;

        return $results;
	}

    /**
     * @param ELOHistory $player1
     * @param ELOHistory $player2
     * @param ELOHistory $player3
     * @param ELOHistory $player4
     * @return PossibleOutcomeModel
     */
    public function possibleOutcomes(ELOHistory $player1History, ELOHistory $player2History,
                                     ELOHistory $player3History = null, ELOHistory $player4History = null)
    {
        $team1Victorious = new Result();
        $team2Victorious = new Result();
        $team1VictoriousELO = null;
        $team2VictoriousELO = null;
        $possibleOutcome = new PossibleOutcomeModel();

        $team1Victorious
            ->setPlayer1($player1History->getPlayer())
            ->setPlayer2($player2History->getPlayer())
            ->setTeam1Score(10)
            ->setTeam2Score(0);
        $team2Victorious
            ->setPlayer1($player1History->getPlayer())
            ->setPlayer2($player2History->getPlayer())
            ->setTeam1Score(0)
            ->setTeam2Score(10);

        if ($player3History === null && $player4History === null) {
            $team1VictoriousELO = $this->calculateELOForResult($team1Victorious, array(
                $player1History->getPlayer()->getId() => $player1History,
                $player2History->getPlayer()->getId() => $player2History
            ));
            $team2VictoriousELO = $this->calculateELOForResult($team2Victorious, array(
                $player1History->getPlayer()->getId() => $player1History,
                $player2History->getPlayer()->getId() => $player2History
            ));

            $possibleOutcome->setTeam1VictoriousELOHistory($team1VictoriousELO[$player1History->getPlayer()->getId()]);
            $possibleOutcome->setTeam1VictoriousELOHistory($team1VictoriousELO[$player2History->getPlayer()->getId()]);

            $possibleOutcome->setTeam2VictoriousELOHistory($team2VictoriousELO[$player1History->getPlayer()->getId()]);
            $possibleOutcome->setTeam2VictoriousELOHistory($team2VictoriousELO[$player2History->getPlayer()->getId()]);
        } else {
            $team1Victorious
                ->setPlayer3($player3History->getPlayer())
                ->setPlayer4($player4History->getPlayer());
            $team2Victorious
                ->setPlayer3($player3History->getPlayer())
                ->setPlayer4($player4History->getPlayer());
            $team1VictoriousELO = $this->calculateELOForResult($team1Victorious, array(
                $player1History->getPlayer()->getId() => $player1History,
                $player2History->getPlayer()->getId() => $player2History,
                $player3History->getPlayer()->getId() => $player3History,
                $player4History->getPlayer()->getId() => $player4History
            ));
            $team2VictoriousELO = $this->calculateELOForResult($team2Victorious, array(
                $player1History->getPlayer()->getId() => $player1History,
                $player2History->getPlayer()->getId() => $player2History,
                $player3History->getPlayer()->getId() => $player3History,
                $player4History->getPlayer()->getId() => $player4History
            ));

            $possibleOutcome->setTeam1VictoriousELOHistory($team1VictoriousELO[$player1History->getPlayer()->getId()]);
            $possibleOutcome->setTeam1VictoriousELOHistory($team1VictoriousELO[$player2History->getPlayer()->getId()]);
            $possibleOutcome->setTeam1VictoriousELOHistory($team1VictoriousELO[$player3History->getPlayer()->getId()]);
            $possibleOutcome->setTeam1VictoriousELOHistory($team1VictoriousELO[$player4History->getPlayer()->getId()]);

            $possibleOutcome->setTeam2VictoriousELOHistory($team2VictoriousELO[$player1History->getPlayer()->getId()]);
            $possibleOutcome->setTeam2VictoriousELOHistory($team2VictoriousELO[$player2History->getPlayer()->getId()]);
            $possibleOutcome->setTeam2VictoriousELOHistory($team2VictoriousELO[$player3History->getPlayer()->getId()]);
            $possibleOutcome->setTeam2VictoriousELOHistory($team2VictoriousELO[$player4History->getPlayer()->getId()]);
        }
        return $possibleOutcome;
    }
}

?>

