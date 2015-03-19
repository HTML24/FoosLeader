<?php

namespace TomGud\FoosLeader\CoreBundle\Model;

use TomGud\FoosLeader\UserBundle\Entity\User;

class PlayerGoalStatistics
{
	/**
	 * Player related to statistics
	 * @var User
	 */
	protected $player;

	/**
	 * How many goals the player has scored
	 * @var integer
	 */
	protected $scored;

	/**
	 * How many goals the player has conceded
	 * @var integer
	 */
	protected $conceded;

	/**
	 * Difference in scored and conceded
	 * @var integer
	 */
	protected $difference;

	public function __construct(User $player, $scored, $conceded)
	{
		$this->player = $player;
		$this->scored = $scored;
		$this->conceded = $conceded;
		$this->difference = ($scored - $conceded);
	}



    /**
     * Gets the Player related to statistics.
     *
     * @return User
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * Sets the Player related to statistics.
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
     * Gets how many goals the player has scored.
     *
     * @return integer
     */
    public function getScored()
    {
        return $this->scored;
    }

    /**
     * Sets how many goals the player has scored.
     *
     * @param integer $scored the scored
     *
     * @return self
     */
    public function setScored($scored)
    {
        $this->scored = $scored;

        return $this;
    }

    /**
     * Gets how many goals the player has conceded.
     *
     * @return integer
     */
    public function getConceded()
    {
        return $this->conceded;
    }

    /**
     * Sets how many goals the player has conceded.
     *
     * @param integer $conceded the conceded
     *
     * @return self
     */
    public function setConceded($conceded)
    {
        $this->conceded = $conceded;

        return $this;
    }

    /**
     * Gets the difference in scored and conceded.
     *
     * @return integer
     */
    public function getDifference()
    {
        return $this->difference;
    }

    /**
     * Sets the difference in scored and conceded.
     *
     * @param integer $difference the difference
     *
     * @return self
     */
    public function setDifference($difference)
    {
        $this->difference = $difference;

        return $this;
    }
}
