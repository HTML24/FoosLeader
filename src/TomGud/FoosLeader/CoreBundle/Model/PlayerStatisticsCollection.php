<?php
/**
 * Created by PhpStorm.
 * User: tomas
 * Date: 30/04/15
 * Time: 21:36
 */

namespace TomGud\FoosLeader\CoreBundle\Model;


use Countable;
use TomGud\FoosLeader\UserBundle\Entity\User;
use Traversable;

class PlayerStatisticsCollection implements  Countable, \IteratorAggregate {
    /**
     * @var PlayerStatisticsModel[]
     */
    protected $playerStatistics;

    /**
     * @var PlayerStatisticsModel[]
     */
    protected $sortedPlayerStatistics;

    /**
     * Sort player statistics by number of wins
     */
    const SORT_WIN = 0;

    /**
     * Sort player statistics by goals scored
     */
    const SORT_GOALS = 1;

    /**
     * Sort player statistics by win ratio
     */
    const SORT_WIN_RATIO = 2;

    /**
     * Sort player statistics by goal ratio
     */
    const SORT_GOAL_RATIO = 3;

    /**
     * Sort player statistics by average goals scored
     */
    const SORT_AVERAGE_SCORE = 4;

    /**
     * Sort player statistics by average goals conceded
     */
    const SORT_AVERAGE_CONCEDED = 5;

    public function __construct()
    {
        $this->playerStatistics = array();
        $this->sortedPlayerStatistics = array();
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->playerStatistics);
    }

    /**
     * @param User $player
     * @param $gamesWon
     * @param $gamesLost
     * @param $goalsScored
     * @param $goalsConceded
     */
    public function addPlayerStatistics(User $player, $gamesWon, $gamesLost, $goalsScored, $goalsConceded)
    {
        $playerStatistics = new PlayerStatisticsModel($player);
        $playerStatistics
            ->addGamesWon($gamesWon)
            ->addGamesLost($gamesLost)
            ->addGoalsScored($goalsScored)
            ->addGoalsConceded($goalsConceded);

        if (array_key_exists($player->getId(), $this->playerStatistics)) {
            $existingStatistics = $this->playerStatistics[$player->getId()];
            $existingStatistics->addPlayerStatistic($playerStatistics);
        } else {
            $this->playerStatistics[$player->getId()] = $playerStatistics;
        }
    }

    /**
     * @param $sortFlag
     * @throws \InvalidArgumentException
     */
    public function sortPlayers($sortFlag)
    {
        $this->sortedPlayerStatistics = $this->playerStatistics;
        switch ($sortFlag)
        {
            case PlayerStatisticsCollection::SORT_WIN:
                uasort($this->sortedPlayerStatistics, array($this, "sortPlayersByWin"));
                break;
            case PlayerStatisticsCollection::SORT_GOALS:
                uasort($this->sortedPlayerStatistics, array($this, "sortPlayersByGoals"));
                break;
            case PlayerStatisticsCollection::SORT_WIN_RATIO:
                uasort($this->sortedPlayerStatistics, array($this, "sortPlayersByWinRatio"));
                break;
            case PlayerStatisticsCollection::SORT_GOAL_RATIO:
                uasort($this->sortedPlayerStatistics, array($this, "sortPlayersByGoalRatio"));
                break;
            case PlayerStatisticsCollection::SORT_AVERAGE_SCORE:
                uasort($this->sortedPlayerStatistics, array($this, "sortPlayersByAvgScored"));
                break;
            case PlayerStatisticsCollection::SORT_AVERAGE_CONCEDED:
                uasort($this->sortedPlayerStatistics, array($this, "sortPlayersByAvgConceded"));
                break;
            default:
                throw new \InvalidArgumentException("Only sort constants from PlayerStatisticsCollection are valid.");
        }
    }

    /**
     * Removes all users that are not in the array $activeUsersIds from player statistics
     *
     * @param int[] $activeUsersIds
     */
    public function filter($activeUsersIds)
    {
        $this->playerStatistics = array_filter($this->playerStatistics, (function($value) use ($activeUsersIds) {
            /** @var PlayerStatisticsModel $value */
            return in_array($value->getPlayer()->getId(), $activeUsersIds);
        }));
        // In case we have some inactive players in the sorted array
        $this->sortedPlayerStatistics = array();
    }

    /**
     * @param PlayerStatisticsModel $a
     * @param PlayerStatisticsModel $b
     * @return bool
     */
    private function sortPlayersByWin($a, $b)
    {
        if ($a instanceof PlayerStatisticsModel && $b instanceof PlayerStatisticsModel) {
            if ($a->getGamesWon() === $b->getGamesWon()) {
                return 0;
            }
            return ($a->getGamesWon() > $b->getGamesWon()) ? -1 : 1;
        }
        return false;
    }

    /**
     * @param PlayerStatisticsModel $a
     * @param PlayerStatisticsModel $b
     * @return bool
     */
    private function sortPlayersByGoals($a, $b)
    {
        if ($a instanceof PlayerStatisticsModel && $b instanceof PlayerStatisticsModel) {
            if ($a->getGoalsScored() === $b->getGoalsScored()) {
                return 0;
            }
            return ($a->getGoalsScored() > $b->getGoalsScored()) ? -1 : 1;
        }
        return false;
    }

    /**
     * @param PlayerStatisticsModel $a
     * @param PlayerStatisticsModel $b
     * @return bool
     */
    private function sortPlayersByWinRatio($a, $b)
    {
        if ($a instanceof PlayerStatisticsModel && $b instanceof PlayerStatisticsModel) {
            if ($a->getGoalRatio() === $b->getGoalRatio()) {
                return 0;
            }
            return ($a->getGoalRatio() > $b->getGoalRatio()) ? -1 : 1;
        }
        return false;
    }

    /**
     * @param PlayerStatisticsModel $a
     * @param PlayerStatisticsModel $b
     * @return bool
     */
    private function sortPlayersByGoalRatio($a, $b)
    {
        if ($a instanceof PlayerStatisticsModel && $b instanceof PlayerStatisticsModel) {
            if ($a->getWinRatio() === $b->getWinRatio()) {
                return 0;
            }
            return ($a->getWinRatio() > $b->getWinRatio()) ? -1 : 1;
        }
        return false;
    }

    /**
     * @param PlayerStatisticsModel $a
     * @param PlayerStatisticsModel $b
     * @return bool
     */
    private function sortPlayersByAvgScored($a, $b)
    {
        if ($a instanceof PlayerStatisticsModel && $b instanceof PlayerStatisticsModel) {
            if ($a->getAverageGoalsScored() === $b->getAverageGoalsScored()) {
                return 0;
            }
            return ($a->getAverageGoalsScored() > $b->getAverageGoalsScored()) ? -1 : 1;
        }
        return false;
    }

    /**
     * @param PlayerStatisticsModel $a
     * @param PlayerStatisticsModel $b
     * @return bool
     */
    private function sortPlayersByAvgConceded($a, $b)
    {
        if ($a instanceof PlayerStatisticsModel && $b instanceof PlayerStatisticsModel) {
            if ($a->getAverageGoalsConceded() === $b->getAverageGoalsConceded()) {
                return 0;
            }
            return ($a->getAverageGoalsConceded() > $b->getAverageGoalsConceded()) ? -1 : 1;
        }
        return false;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->sortedPlayerStatistics);
    }
}