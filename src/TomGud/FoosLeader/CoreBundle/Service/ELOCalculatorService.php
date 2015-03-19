<?php

namespace TomGud\FoosLeader\CoreBundle\Service;

use TomGud\FoosLeader\CoreBundle\Entity\ELOHistory;
use TomGud\FoosLeader\CoreBundle\Entity\Result;
use TomGud\FoosLeader\CoreBundle\Model\ELOPlayerResultModel;

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
		$p1ELO->setCurrentELO($eloHistories[$p1ELO->getPlayer()->getId()]->getNewELO());
		$p2ELO->setCurrentELO($eloHistories[$p2ELO->getPlayer()->getId()]->getNewELO());
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
			$p3ELO->setCurrentELO($eloHistories[$p3ELO->getPlayer()->getId()]->getNewELO());
			$p4ELO->setCurrentELO($eloHistories[$p4ELO->getPlayer()->getId()]->getNewELO());

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
}

?>
