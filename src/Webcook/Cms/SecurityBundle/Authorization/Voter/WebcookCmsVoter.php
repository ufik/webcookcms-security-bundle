<?php

/**
 * This file is part of Webcook security bundle.
 *
 * See LICENSE file in the root of the bundle. Webcook 
 */

namespace Webcook\Cms\SecurityBundle\Authorization\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Doctrine\ORM\EntityManager;

/**
 * Main voter for WebcookCms application.
 */
class WebcookCmsVoter implements VoterInterface
{
    /**
     * @var View action.
     */
    const ACTION_VIEW = 'view';

    /**
     * @var Edit action
     */
    const ACTION_INSERT = 'insert';

    /**
     * @var Edit action
     */
    const ACTION_EDIT = 'edit';

    /**
     * @var Delete action
     */
    const ACTION_DELETE = 'delete';

    /**
     * Entity manager.
     *
     * @var EntityManager
     */
    private $em;

    /**
     * Class constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $attribute
     *
     * @return bool
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, array(
            self::ACTION_VIEW,
            self::ACTION_INSERT,
            self::ACTION_EDIT,
            self::ACTION_DELETE,
        ));
    }

    /**
     * {@inheritdoc}
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param TokenInterface $token
     * @param string         $resource
     * @param array          $attributes
     *
     * @return int
     */
    public function vote(TokenInterface $token, $resource, array $attributes)
    {
        if (1 !== count($attributes)) {
            throw new \InvalidArgumentException(
                'Only one attribute is allowed for view, edit or delete.'
            );
        }

        // set the attribute to check against
        $attribute = $attributes[0];

        // check if the given attribute is covered by this voter
        if (!$this->supportsAttribute($attribute)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $user = $token->getUser();

        foreach ($user->getRoles() as $role) {
            $roleResource = $role->getResource($resource);
            if ($roleResource->isGranted($attribute)) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
