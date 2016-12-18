<?php

/**
 * This file is part of Webcook security bundle.
 *
 * See LICENSE file in the root of the bundle. Webcook 
 */

namespace Webcook\Cms\SecurityBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

/**
 * User entity repository.
 */
class UserRepository extends EntityRepository implements UserLoaderInterface
{
    /**
     * {@inheritdoc}
     *
     * @param [type] $username [description]
     *
     * @return [type] [description]
     */
    public function loadUserByUsername($username)
    {
        $q = $this
            ->createQueryBuilder('u')
            ->where('(u.username = :username OR u.email = :email) AND u.isActive = :active')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->setParameter('active', true)
            ->getQuery();

        try {
            $user = $q->getSingleResult();
        } catch (NoResultException $e) {
            $message = sprintf(
                'Unable to find an active admin object identified by "%s".',
                $username
            );
            throw new UsernameNotFoundException($message, 0, $e);
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     *
     * @param UserInterface $user [description]
     *
     * @return [type] [description]
     */
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    $class
                )
            );
        }
        return $this
            ->createQueryBuilder('u')
            ->where('u.id = :id AND u.isActive = :active')
            ->setParameter('id', $user->getId())
            ->setParameter('active', true)
            ->getQuery()
            ->getSingleResult();

        //return $this->find($user->getId());
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $class [description]
     * @return [type] [description]
     */
    public function supportsClass($class)
    {
        return $this->getEntityName() === $class
            || is_subclass_of($class, $this->getEntityName());
    }
}
