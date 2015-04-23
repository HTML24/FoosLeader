<?php
/**
 * Created by PhpStorm.
 * User: tomas
 * Date: 23/04/15
 * Time: 17:25
 */

namespace TomGud\FoosLeader\CoreBundle\Model;


use TomGud\FoosLeader\CoreBundle\Entity\ELOHistory;
use TomGud\FoosLeader\UserBundle\Entity\User;

class PossibleOutcomeModel {
    /**
     * @var ELOPlayerResultModel[]
     */
    protected $team1Victorious;
    /**
     * @var ELOPlayerResultModel[]
     */
    protected $team2Victorious;

    public function __construct()
    {
        $this->team1Victorious = array();
        $this->team2Victorious = array();
    }

    /**
     * @param User $player
     * @return ELOPlayerResultModel
     */
    public function getTeam1VictoriousELOHistory(User $player) {
        if (array_key_exists($player->getId(), $this->team1Victorious)) {
            return $this->team1Victorious[$player->getId()];
        }
        return null;
    }

    /**
     * @param User $player
     * @return ELOPlayerResultModel
     */
    public function getTeam2VictoriousELOHistory(User $player) {
        if (array_key_exists($player->getId(), $this->team2Victorious)) {
            return $this->team2Victorious[$player->getId()];
        }
        return null;
    }

    /**
     * @param ELOPlayerResultModel $history
     */
    public function setTeam1VictoriousELOHistory(ELOPlayerResultModel $history) {
        $this->team1Victorious[$history->getPlayer()->getId()] = $history;
    }

    /**
     * @param ELOPlayerResultModel $history
     */
    public function setTeam2VictoriousELOHistory(ELOPlayerResultModel$history) {
        $this->team2Victorious[$history->getPlayer()->getId()] = $history;
    }
}