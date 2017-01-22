<?php

namespace Webcook\Cms\SecurityBundle\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use Webcook\Cms\SecurityBundle\Entity\Role;
use Webcook\Cms\SecurityBundle\Entity\RoleResource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RoleControllerSubscriber implements EventSubscriberInterface
{
    private $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => [['postWrite', EventPriorities::POST_WRITE]],
        ];
    }

    public function postWrite(GetResponseForControllerResultEvent $event)
    {
        $role   = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$role instanceof Role) {
            return;
        }

        if ($method == 'POST') {
            $this->addResources($role);
        }
    }

    /**
     * Add all resources from the system.
     *
     * @param Role $role
     */
    private function addResources(Role $role)
    {
        $resources = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\Resource')->findAll();
        foreach ($resources as $resource) {
            $roleResource = new RoleResource();
            $roleResource->setRole($role);
            $roleResource->setResource($resource);
            $this->em->persist($roleResource);
        }

        $this->em->flush();
    }
}
