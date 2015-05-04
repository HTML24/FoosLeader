<?php
/**
 * Created by PhpStorm.
 * User: tomas
 * Date: 30/04/15
 * Time: 22:05
 */

namespace TomGud\FoosLeader\CoreBundle\Model;


use TomGud\FoosLeader\UserBundle\Entity\User;

class PlayerStatisticsModel {
    /**
     * @var User
     */
    protected $player;

    /**
     * @var int
     */
    protected $gamesWon;

    /**
     * @var int
     */
    protected $gamesLost;

    /**
     * @var int
     */
    protected $goalsScored;

    /**
     * @var int
     */
    protected $goalsConceded;

    /**
     * Construction
     *
     * @param User $player
     */
    public function __construct(User $player)
    {
        $this->player = $player;
        $this->gamesLost = 0;
        $this->gamesWon = 0;
        $this->goalsConceded = 0;
        $this->goalsScored = 0;
    }

    /**
     * @param int $gamesWon
     * @return PlayerStatisticsModel
     */
    public function addGamesWon($gamesWon)
    {
        $this->gamesWon += $gamesWon;
        return $this;
    }

    /**
     * @param int $gamesLost
     * @return PlayerStatisticsModel
     */
    public function addGamesLost($gamesLost)
    {
        $this->gamesLost += $gamesLost;
        return $this;
    }

    /**
     * @param int $goalsScored
     * @return PlayerStatisticsModel
     */
    public function addGoalsScored($goalsScored)
    {
        $this->goalsScored += $goalsScored;
        return $this;
    }

    /**
     * @param int $goalsConceded
     * @return PlayerStatisticsModel
     */
    public function addGoalsConceded($goalsConceded)
    {
        $this->goalsConceded += $goalsConceded;
        return $this;
    }

    /**
     * @return int
     */
    public function getGamesWon()
    {
        return $this->gamesWon;
    }

    /**
     * @return int
     */
    public function getGamesLost()
    {
        return $this->gamesLost;
    }

    /**
     * @return int
     */
    public function getGoalsScored()
    {
        return $this->goalsScored;
    }

    /**
     * @return int
     */
    public function getGoalsConceded()
    {
        return $this->goalsConceded;
    }

    public function getTotalGames()
    {
        return $this->gamesWon + $this->gamesLost;
    }

    /**
     * @return double
     */
    public function getWinRatio()
    {
        if ($this->getTotalGames() === 0) {
            return 0.0;
        }
        return ($this->gamesWon/($this->getTotalGames()));
    }

    /**
     * @return double
     */
    public function getGoalRatio()
    {
        if ($this->goalsConceded === 0) {
            return (double)$this->goalsScored;
        }
        return ($this->goalsScored/$this->goalsConceded);
    }

    /**
     * @return double
     */
    public function getLossRatio()
    {
        if ($this->getTotalGames() === 0) {
            return 0.0;
        }
        return ($this->gamesLost/($this->getTotalGames()));
    }

    /**
     * @return double
     */
    public function getAverageGoalsScored()
    {
        if ($this->getTotalGames() === 0) {
            return 0.0;
        }
        return ($this->goalsScored/$this->getTotalGames());
    }

    /**
     * @return double
     */
    public function getAverageGoalsConceded()
    {
        if ($this->getTotalGames() === 0) {
            return 0.0;
        }
        return ($this->goalsConceded/$this->getTotalGames());
    }

    /**
     * @return User
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * @param PlayerStatisticsModel $playerStatistics
     */
    public function addPlayerStatistic(PlayerStatisticsModel $playerStatistics)
    {
        if ($this->player->getId() !== $playerStatistics->getPlayer()->getId()) {
            return;
        }
        $this->gamesWon = $this->gamesWon + $playerStatistics->getGamesWon();
        $this->gamesLost = $this->gamesLost + $playerStatistics->getGamesLost();
        $this->goalsScored = $this->goalsScored + $playerStatistics->getGoalsScored();
        $this->goalsConceded = $this->goalsConceded + $playerStatistics->getGoalsConceded();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $string = $this->player->getUsername() . " [" . $this->player->getId() . "] ";
        $string .= "Won: " . $this->gamesWon . " Lost: " . $this->gamesLost . " ";
        $string .= "Scored: " . $this->goalsScored . " Conceded: " . $this->goalsConceded;
        return $string;
    }
}