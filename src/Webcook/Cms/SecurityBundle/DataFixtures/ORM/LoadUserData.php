<?php

/**
 * This file is part of Webcook security bundle.
 *
 * See LICENSE file in the root of the bundle. Webcook 
 */

namespace Webcook\Cms\SecurityBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Webcook\Cms\SecurityBundle\Entity\User;
use Webcook\Cms\SecurityBundle\Entity\Role;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * User fixtures.
 */
class LoadUserData implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * System container.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Set container.
     *
     * @param ContainerInterface $container
     *                                      {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load fixtures into db.
     *
     * {@inheritDoc}
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->addAdmin($manager);
        $this->addEditor($manager);
        $this->addTestUser($manager);

        $manager->flush();
    }

    /**
     * Add admin user.
     *
     * @param [type] $manager [description]
     */
    private function addAdmin($manager)
    {
        $user = new User();
        
        $user->setUsername('admin');
        $user->setEmail('info@Webcook.com');

        $factory = $this->container->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);
        $password = $encoder->encodePassword('test', $user->getSalt());
        $user->setPassword($password);

        $role = $manager->getRepository('Webcook\Cms\SecurityBundle\Entity\Role')->findAll();
        $user->addRole($role[0]);

        $manager->persist($user);
    }

    /**
     * Add editor user.
     *
     * @param [type] $manager [description]
     */
    private function addEditor($manager)
    {
        $user = new User();

        $user->setUsername('editor');
        $user->setEmail('editor@Webcook.com');

        $factory = $this->container->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);
        $password = $encoder->encodePassword('test', $user->getSalt());
        $user->setPassword($password);

        $role = $manager->getRepository('Webcook\Cms\SecurityBundle\Entity\Role')->findAll();
        $user->addRole($role[1]);

        $manager->persist($user);
    }

    /**
     * Add Test user.
     *
     * @param [type] $manager [description]
     */
    private function addTestUser($manager)
    {
        $user = new User();

        $user->setUsername('test');
        $user->setEmail('testUser@Webcook.com');

        $factory = $this->container->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);
        $password = $encoder->encodePassword('test', $user->getSalt());
        $user->setPassword($password);

        $role = $manager->getRepository('Webcook\Cms\SecurityBundle\Entity\Role')->findAll();
        $user->addRole($role[0]);

        $manager->persist($user);
    }


    /**
     * Get fixture order.
     *
     * @return [type] [description]
     */
    public function getOrder()
    {
        return 2;
    }
}
