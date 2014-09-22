<?php

namespace TomGud\FoosLeader\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use TomGud\FoosLeader\UserBundle\Entity\User;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface
{
    const _FBD_DEFAULT_PASSWORD_ = "password";
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {

        $userAdmin = new User();
        $userAdmin
            ->setUsername('admin')
            ->setPlainPassword(LoadUserData::_FBD_DEFAULT_PASSWORD_)
            ->setEnabled(true)
            ->addRole('ROLE_ADMIN')
            ->setEmail('userAdmin@example.com');

        $manager->persist($userAdmin);
        $manager->flush();

        $user1 = new User();
        $user1
            ->setUsername('user1')
            ->setPlainPassword(LoadUserData::_FBD_DEFAULT_PASSWORD_)
            ->setEnabled(true)
            ->addRole('ROLE_PLAYER')
            ->setEmail('user1@example.com');

        $manager->persist($user1);
        $manager->flush();

        $user2 = new User();
        $user2
            ->setUsername('user2')
            ->setPlainPassword(LoadUserData::_FBD_DEFAULT_PASSWORD_)
            ->setEnabled(true)
            ->addRole('ROLE_PLAYER')
            ->setEmail('user2@example.com');

        $manager->persist($user2);
        $manager->flush();

        $user3 = new User();
        $user3
            ->setUsername('user3')
            ->setPlainPassword(LoadUserData::_FBD_DEFAULT_PASSWORD_)
            ->setEnabled(true)
            ->addRole('ROLE_PLAYER')
            ->setEmail('user3@example.com');

        $manager->persist($user3);
        $manager->flush();

        $user4 = new User();
        $user4
            ->setUsername('user4')
            ->setPlainPassword(LoadUserData::_FBD_DEFAULT_PASSWORD_)
            ->setEnabled(true)
            ->addRole('ROLE_PLAYER')
            ->setEmail('user4@example.com');

        $manager->persist($user4);
        $manager->flush();

        $user5 = new User();
        $user5
            ->setUsername('user5')
            ->setPlainPassword(LoadUserData::_FBD_DEFAULT_PASSWORD_)
            ->setEnabled(true)
            ->addRole('ROLE_PLAYER')
            ->setEmail('user5@example.com');

        $manager->persist($user5);
        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1;
    }
}
