<?php

namespace TomGud\FoosLeader\UserBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

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

    public function __construct()
    {
        parent::__construct();
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
}
