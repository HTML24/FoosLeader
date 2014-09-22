<?php

namespace TomGud\FoosLeader\CoreBundle\Model;

use TomGud\FoosLeader\CoreBundle\Entity\Result;
use TomGud\FoosLeader\UserBundle\Entity\User;

class ELOPlayerResultModel
{
	/**
	 * Player
	 * @var User
	 */
	protected $player;

	/**
	 * Result
	 * @var Result
	 */
	protected $result;

	/**
	 * ELO rankings before the result
	 * @var integer
	 */
	protected $currentELO;

    /**
     * Player's K Value
     * @var integer
     */
    protected $playerK;
	/**
	 * ELO rankings after the result
	 * @var integer
	 */
	protected $newELO;

	/**
	 * Change in ELO ranking
	 * @var integer
	 */
	protected $change;

    public function __construct(Result $result, User $player)
    {
        $this->result = $result;
        $this->player = $player;
        $this->currentELO = $player->getELORanking();
        $this->playerK = $player->getELOKValue();
    }

    /**
     * Gets the Player.
     *
     * @return User
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * Gets the Result.
     *
     * @return Result
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Gets the ELO rankings before the result.
     *
     * @return integer
     */
    public function getCurrentELO()
    {
        return $this->currentELO;
    }

    /**
     * Gets the Player's K Value.
     *
     * @return integer
     */
    public function getPlayerK()
    {
        return $this->playerK;
    }

    /**
     * Gets the ELO rankings after the result.
     *
     * @return integer
     */
    public function getNewELO()
    {
        return $this->newELO;
    }

    /**
     * Gets the Change in ELO ranking.
     *
     * @return integer
     */
    public function getChange()
    {
        return $this->change;
    }

    /**
     * Sets the Player.
     *
     * @param User $player the player
     *
     * @return self
     */
    public function setPlayer(User $player)
    {
        $this->player = $player;

        return $this;
    }

    /**
     * Sets the Result.
     *
     * @param Result $result the result
     *
     * @return self
     */
    public function setResult(Result $result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * Sets the ELO rankings before the result.
     *
     * @param integer $currentELO the current elo
     *
     * @return self
     */
    public function setCurrentELO($currentELO)
    {
        $this->currentELO = $currentELO;

        return $this;
    }

    /**
     * Sets the Player's K Value.
     *
     * @param integer $playerK the player k
     *
     * @return self
     */
    public function setPlayerK($playerK)
    {
        $this->playerK = $playerK;

        return $this;
    }

    /**
     * Sets the ELO rankings after the result.
     *
     * @param integer $newELO the new elo
     *
     * @return self
     */
    public function setNewELO($newELO)
    {
        $this->newELO = $newELO;

        return $this;
    }

    /**
     * Sets the Change in ELO ranking.
     *
     * @param integer $change the change
     *
     * @return self
     */
    public function setChange($change)
    {
        $this->change = $change;

        return $this;
    }
}

