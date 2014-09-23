<?php

namespace TomGud\FoosLeader\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use TomGud\FoosLeader\UserBundle\Entity\User;

/**
 * Result
 *
 * @ORM\Table(name="results")
 * @ORM\Entity(repositoryClass="TomGud\FoosLeader\CoreBundle\Entity\ResultRepository")
 */
class Result
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
     * @ORM\JoinColumn(name="player_1", referencedColumnName="id", nullable=false)
     */
    protected $player1;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="TomGud\FoosLeader\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="player_2", referencedColumnName="id", nullable=false)
     */
    protected $player2;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="TomGud\FoosLeader\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="player_3", referencedColumnName="id", nullable=true)
     */
    protected $player3;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="TomGud\FoosLeader\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="player_4", referencedColumnName="id", nullable=true)
     */
    protected $player4;

    /**
     * @var integer
     *
     * @ORM\Column(name="team_1_score", type="integer")
     */
    protected $team1Score;

    /**
     * @var integer
     *
     * @ORM\Column(name="team_2_score", type="integer")
     */
    protected $team2Score;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="submitted", type="datetime")
     */
    protected $submitted;

    /**
     * @var boolean
     *
     * @ORM\Column(name="team_1_confirmed", type="boolean")
     */
    protected $team1Confirmed = false;

    /**
     * @var integer
     *
     * @ORM\Column(name="team_2_confirmed", type="boolean")
     */
    protected $team2Confirmed = false;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ELOHistory", mappedBy="result")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $eloHistories;

    public function __construct() {
        $this->eloHistories = new ArrayCollection();
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
     * Set player1
     *
     * @param User $player1
     * @return Result
     */
    public function setPlayer1($player1)
    {
        $this->player1 = $player1;

        return $this;
    }

    /**
     * Get player1
     *
     * @return User
     */
    public function getPlayer1()
    {
        return $this->player1;
    }

    /**
     * Set player2
     *
     * @param User $player2
     * @return Result
     */
    public function setPlayer2($player2)
    {
        $this->player2 = $player2;

        return $this;
    }

    /**
     * Get player2
     *
     * @return User
     */
    public function getPlayer2()
    {
        return $this->player2;
    }

    /**
     * Set player3
     *
     * @param User $player3
     * @return Result
     */
    public function setPlayer3($player3)
    {
        $this->player3 = $player3;

        return $this;
    }

    /**
     * Get player3
     *
     * @return User
     */
    public function getPlayer3()
    {
        return $this->player3;
    }

    /**
     * Set player4
     *
     * @param User $player4
     * @return Result
     */
    public function setPlayer4($player4)
    {
        $this->player4 = $player4;

        return $this;
    }

    /**
     * Get player4
     *
     * @return User
     */
    public function getPlayer4()
    {
        return $this->player4;
    }

    /**
     * Set team1Score
     *
     * @param integer $team1Score
     * @return Result
     */
    public function setTeam1Score($team1Score)
    {
        $this->team1Score = $team1Score;

        return $this;
    }

    /**
     * Get team1Score
     *
     * @return integer
     */
    public function getTeam1Score()
    {
        return $this->team1Score;
    }

    /**
     * Set team2Score
     *
     * @param integer $team2Score
     * @return Result
     */
    public function setTeam2Score($team2Score)
    {
        $this->team2Score = $team2Score;

        return $this;
    }

    /**
     * Get team2Score
     *
     * @return integer
     */
    public function getTeam2Score()
    {
        return $this->team2Score;
    }

    /**
     * Set submitted
     *
     * @param \DateTime $submitted
     * @return Result
     */
    public function setSubmitted($submitted)
    {
        $this->submitted = $submitted;

        return $this;
    }

    /**
     * Get submitted
     *
     * @return \DateTime
     */
    public function getSubmitted()
    {
        return $this->submitted;
    }

    /**
     * Set team1Confirmed
     *
     * @param boolean $confirmed
     * @return Result
     */
    public function setTeam1Confirmed($confirmed) {
        $this->team1Confirmed = $confirmed;

        return $this;
    }

    /**
     * Set team2Confirmed
     *
     * @param boolean $confirmed
     * @return Result
     */
    public function setTeam2Confirmed($confirmed) {
        $this->team2Confirmed = $confirmed;

        return $this;
    }

    /**
     * Get team1Confirmed
     *
     * @return boolean
     */
    public function getTeam1Confirmed() {
        return $this->team1Confirmed;
    }

    /**
     * Get team1Confirmed
     *
     * @return boolean
     */
    public function getTeam2Confirmed() {
        return $this->team2Confirmed;
    }

    /**
     * Get confirmed
     *
     * @return boolean
     */
    public function isConfirmed() {
        return $this->team1Confirmed && $this->team2Confirmed;
    }

    /**
     * getVictors
     *
     * @return User[]
     */
    public function getVictors() {
        $players = array();
        if ($this->team1Score > $this->team2Score) {
            $players[] = $this->player1;
            if ($this->player3 !== null) {
                $players[] = $this->player3;
            }
        } else {
            $players[] = $this->player2;
            if ($this->player4 !== null) {
                $players[] = $this->player4;
            }
        }
        return $players;
    }

    /**
     * getLosers
     *
     * @return User[]
     */
    public function getLosers() {
        $players = array();
        if ($this->team1Score < $this->team2Score) {
            $players[] = $this->player1;
            if ($this->player3 !== null) {
                $players[] = $this->player3;
            }
        } else {
            $players[] = $this->player2;
            if ($this->player4 !== null) {
                $players[] = $this->player4;
            }
        }
        return $players;
    }

    public function userParticipating(User $user) {
        return ($this->player1 === $user ||
            $this->player2 === $user ||
            $this->player3 === $user ||
            $this->player4=== $user
        );
    }

    public function userInTeam1(User $user) {
        return ($this->player1 === $user || $this->player3 === $user);
    }

    public function userInTeam2(User $user) {
        return ($this->player2 === $user || $this->player4 === $user);
    }


    /**
     * @param ELOHistory[] $eloHistories
     * @return Result
     */
    public function setAnswers($eloHistories)
    {
        $this->eloHistories = $eloHistories;

        return $this;
    }

    /**
     * @return ELOHistory[]
     */
    public function getELOHistories()
    {
        return $this->eloHistories;
    }

    /**
     * @param ELOHistory $eloHistory
     * @return Result
     */
    public function addELOHistory(ELOHistory $eloHistory) {
        $eloHistory->setResult($this);
        $this->eloHistories->add($eloHistory);

        return $this;
    }

    /**
     * @param ELOHistory $eloHistory
     * @return bool
     */
    public function removeELOHistory(ELOHistory $eloHistory) {
        $eloHistory->setResult(null);
        return $this->eloHistories->removeElement($eloHistory);
    }

}
