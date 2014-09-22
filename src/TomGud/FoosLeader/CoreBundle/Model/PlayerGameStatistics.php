<?php

namespace TomGud\FoosLeader\CoreBundle\Model;

use TomGud\FoosLeader\UserBundle\Entity\User;

class PlayerGameStatistics
{
	/**
	 * Player related to statistics
	 * @var User
	 */
	protected $player;

	/**
	 * games played by this user
	 * @var integer
	 */
	protected $games;

	/**
	 * games won by this user
	 * @var integer
	 */
	protected $won;

	/**
	 * games lost by this user
	 * @var integer
	 */
	protected $lost;

	public function __construct(User $player, $games, $won, $lost = null)
	{
		if ($lost !== null && ($won + $lost) !== $games)
		{
			// Error as lost and won count do not equal total amount of games
			return null;
		}

		$this->player = $player;
		$this->games = $games;
		$this->won = $won;
		$this->lost = ($lost === null) ? ($games - $won) : $lost;
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
     * Gets the games played by this user.
     *
     * @return integer
     */
    public function getGames()
    {
        return $this->games;
    }

    /**
     * Sets the games played by this user.
     *
     * @param integer $games the games
     *
     * @return self
     */
    public function setGames($games)
    {
        $this->games = $games;

        return $this;
    }

    /**
     * Gets the games won by this user.
     *
     * @return integer
     */
    public function getWon()
    {
        return $this->won;
    }

    /**
     * Sets the games won by this user.
     *
     * @param integer $won the won
     *
     * @return self
     */
    public function setWon($won)
    {
        $this->won = $won;

        return $this;
    }

    /**
     * Gets the games lost by this user.
     *
     * @return integer
     */
    public function getLost()
    {
        return $this->lost;
    }

    /**
     * Sets the games lost by this user.
     *
     * @param integer $lost the lost
     *
     * @return self
     */
    public function setLost($lost)
    {
        $this->lost = $lost;

        return $this;
    }
}
