<?php

namespace TomGud\FoosLeader\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use TomGud\FoosLeader\UserBundle\Entity\User;

/**
 * ELOHistory
 *
 * @ORM\Table(name="elo_history")
 * @ORM\Entity(repositoryClass="TomGud\FoosLeader\CoreBundle\Entity\ELOHistoryRepository")
 */
class ELOHistory
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="TomGud\FoosLeader\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="player", referencedColumnName="id", nullable=false)
     */
    protected $player;

    /**
     * @var Result
     *
     * @ORM\ManyToOne(targetEntity="TomGud\FoosLeader\CoreBundle\Entity\Result")
     * @ORM\JoinColumn(name="result", referencedColumnName="id", nullable=false)
     */
    protected $result;

    /**
     * @var integer
     *
     * @ORM\Column(name="old_elo", type="integer")
     */
    protected $oldELO;

    /**
     * @var integer
     *
     * @ORM\Column(name="new_elo", type="integer")
     */
    protected $newELO;

    /**
     * @var ELOHistory
     *
     * @ORM\OneToOne(targetEntity="EloHistory")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     **/
    protected $parent;

    /**
     * Time registered
     * @var \DateTime
     *
     * @ORM\Column(name="registered", type="datetime")
     */
    protected $registered;

    public function __construct()
    {
        $this->registered = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set player
     *
     * @param User $player
     * @return ELOHistory
     */
    public function setPlayer(User $player)
    {
        $this->player = $player;

        return $this;
    }

    /**
     * Get player
     *
     * @return User
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * Gets the value of result.
     *
     * @return Result
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Sets the value of result.
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
     * Set oldELO
     *
     * @param integer $oldELO
     * @return ELOHistory
     */
    public function setOldElo($oldELO)
    {
        $this->oldELO = $oldELO;

        return $this;
    }

    /**
     * Get oldELO
     *
     * @return integer
     */
    public function getOldElo()
    {
        return $this->oldELO;
    }

    /**
     * Set newELO
     *
     * @param integer $newELO
     * @return ELOHistory
     */
    public function setNewElo($newELO)
    {
        $this->newELO = $newELO;

        return $this;
    }

    /**
     * Get newELO
     *
     * @return integer
     */
    public function getNewElo()
    {
        return $this->newELO;
    }

    /**
     * Set parent
     *
     * @param ELOHistory $parent
     * @return ELOHistory
     */
    public function setParent(ELOHistory $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return ELOHistory
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Gets the Time registered.
     *
     * @return \DateTime
     */
    public function getRegistered()
    {
        return $this->registered;
    }

    /**
     * Sets the Time registered.
     *
     * @param \DateTime $registered the registered
     *
     * @return self
     */
    public function setRegistered(\DateTime $registered)
    {
        $this->registered = $registered;

        return $this;
    }
}
