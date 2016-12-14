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
use Webcook\Cms\SecurityBundle\Entity\Role;
use Webcook\Cms\SecurityBundle\Entity\RoleResource;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Role fixtures for tests.
 */
class LoadRoleData implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * System container.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Entity manager.
     *
     * @var ObjectManager
     */
    private $manager;

    /**
     * Set container.
     *
     * {@inheritDoc}
     * @param ContainerInterface $container [description]
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load fixtures into db.
     *
     * @param ObjectManager $manager
     *                               {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $role = new Role();
        $role->setName('Administrator');
        $role->setRole('ROLE_ADMIN');

        $this->manager->persist($role);

        $this->addResources($role);

        $role = new Role();
        $role->setName('Editor');
        $role->setRole('ROLE_EDITOR');

        $this->manager->persist($role);

        $this->addResources($role, false);

        $this->manager->flush();
    }

    /**
     * Add resources into role object.
     *
     * @param Role    $role  [description]
     * @param boolean $admin [description]
     */
    private function addResources(Role $role, $admin = true)
    {
        $resources = $this->manager->getRepository('Webcook\Cms\SecurityBundle\Entity\Resource')->findAll();

        foreach ($resources as $resource) {
            $roleResource = new RoleResource();
            $roleResource->setRole($role);
            $roleResource->setResource($resource);
            $roleResource->setEdit($admin);
            $roleResource->setInsert($admin);
            $roleResource->setDelete($admin);

            $this->manager->persist($roleResource);
        }
    }

    /**
     * Get fixture order.
     *
     * {@inheritdoc}
     *
     * @return [type] [description]
     */
    public function getOrder()
    {
        return 1;
    }
}
