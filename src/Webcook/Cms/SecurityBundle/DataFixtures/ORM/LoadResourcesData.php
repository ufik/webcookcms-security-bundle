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
use Webcook\Cms\SecurityBundle\Entity\Resource;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webcook\Cms\SecurityBundle\Common\SecurityHelper;

/**
 * Resources fixtures for tests.
 */
class LoadResourcesData implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * System container.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Set container
     *
     * {@inheritDoc}
     *
     * @param ContainerInterface $container [description]
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
        $resources = SecurityHelper::getResourcesNames($this->container->getParameter('resources_path'));

        foreach ($resources as $resourceName) {
            $resource = new Resource();
            $resource->setName($resourceName);

            $manager->persist($resource);
        }

        $manager->flush();
    }

    /**
     * Get order of fixture.
     *
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 0;
    }
}
