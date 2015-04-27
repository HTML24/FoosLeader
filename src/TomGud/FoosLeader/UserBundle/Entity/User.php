<?php

namespace TomGud\FoosLeader\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use TomGud\FoosLeader\CoreBundle\Entity\Result;

/**
 * User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="TomGud\FoosLeader\UserBundle\Entity\UserRepository")
 */
class User extends BaseUser
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
     * This user's current elo rating
     * @var integer
     *
     * @ORM\Column(name="elo_ranking", type="integer")
     */
    protected $ELORanking = 1000;

    /**
     * This user's current K value
     * @var integer
     *
     * @ORM\Column(name="elo_k_value", type="integer")
     */
    protected $ELOKValue = 50;

    /**
     * @var Result[]
     *
     * @ORM\OneToMany(targetEntity="TomGud\FoosLeader\CoreBundle\Entity\Result", mappedBy="player1")
     */
    protected $player1Results;

    /**
     * @var Result[]
     *
     * @ORM\OneToMany(targetEntity="TomGud\FoosLeader\CoreBundle\Entity\Result", mappedBy="player2")
     */
    protected $player2Results;

    /**
     * @var Result[]
     *
     * @ORM\OneToMany(targetEntity="TomGud\FoosLeader\CoreBundle\Entity\Result", mappedBy="player3")
     */
    protected $player3Results;

    /**
     * @var Result[]
     *
     * @ORM\OneToMany(targetEntity="TomGud\FoosLeader\CoreBundle\Entity\Result", mappedBy="player4")
     */
    protected $player4Results;

    /**
     * @var Result[]
     *
     * A collection of all the results
     */
    protected $allResults;


    public function __construct()
    {
        parent::__construct();

        $this->player1Results = new ArrayCollection();
        $this->player2Results = new ArrayCollection();
        $this->player3Results = new ArrayCollection();
        $this->player4Results = new ArrayCollection();
        $this->allResults = new ArrayCollection();
    }


    /**
     * Gets the value of id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the This user's current elo rating.
     *
     * @return integer
     */
    public function getELORanking()
    {
        return $this->ELORanking;
    }

    /**
     * Sets the This user's current elo rating.
     *
     * @param integer $ELORanking the e l o ranking
     *
     * @return self
     */
    public function setELORanking($ELORanking)
    {
        $this->ELORanking = $ELORanking;

        return $this;
    }

    /**
     * Gets the This user's current K value.
     *
     * @return integer
     */
    public function getELOKValue()
    {
        return $this->ELOKValue;
    }

    /**
     * Sets the This user's current K value.
     *
     * @param integer $ELOKValue the e l o k value
     *
     * @return self
     */
    public function setELOKValue($ELOKValue)
    {
        $this->ELOKValue = $ELOKValue;

        return $this;
    }

    /**
     * @return Result[]
     */
    public function getAllResults()
    {
        return array_merge(
            $this->player1Results->getValues(),
            $this->player2Results->getValues(),
            $this->player3Results->getValues(),
            $this->player4Results->getValues()
        );
    }

    /**
     * @return Result[]
     */
    public function getDisputedResults()
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("team1Confirmed", 0))
            ->orWhere(Criteria::expr()->eq("team2Confirmed", 0
            ));
        $matches = array_merge($this->player1Results->matching($criteria)->getValues(),
            $this->player2Results->matching($criteria)->getValues(),
            $this->player3Results->matching($criteria)->getValues(),
            $this->player4Results->matching($criteria)->getValues());
        return $matches;
    }
}
