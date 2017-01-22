<?php

namespace Webcook\Cms\SecurityBundle\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use Webcook\Cms\SecurityBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class UserControllerSubscriber implements EventSubscriberInterface
{
    private $em;

    private $encoderFactory;

    public function __construct($em, $encoderFactory)
    {
        $this->em             = $em;
        $this->encoderFactory = $encoderFactory;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => [['postWrite', EventPriorities::POST_WRITE]],
        ];
    }

    public function postWrite(GetResponseForControllerResultEvent $event)
    {
        $user   = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$user instanceof User) {
            return;
        }

        $data = json_decode($event->getRequest()->getContent());

        if (isset($data->password) && !empty($data->password)) {
            if ($method === 'PUT') {
                $oldPassword = $user->getPassword();
            }

            $plainPassword = $user->getPassword();

            if (!empty($plainPassword)) {
                $factory  = $this->encoderFactory;
                $encoder  = $factory->getEncoder($user);
                $password = $encoder->encodePassword($plainPassword, $user->getSalt());
                $user->setPassword($password);
            } else {
                $user->setPassword($oldPassword);
            }

            $this->em->persist($user);
            $this->em->flush();
        }
    }
}
