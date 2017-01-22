<?php

/**
 * This file is part of Webcook security bundle.
 *
 * See LICENSE file in the root of the bundle. Webcook
 */

namespace Webcook\Cms\SecurityBundle\Entity;

use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Webcook\Cms\CoreBundle\Base\BasicEntity;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * System role entity.
 *
 * @ApiResource
 * @ORM\Table(name="SecurityRole")
 * @ORM\Entity()
 */
class Role extends BasicEntity implements RoleInterface
{
    /**
     * Role name.
     *
     * @ORM\Column(name="name", type="string", length=30)
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $name;

    /**
     * Role identification.
     *
     * @ORM\Column(name="role", type="string", length=20, unique=true)
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $role;

    /**
     * Resources of the role and their permissions.
     *
     * @ORM\OneToMany(targetEntity="RoleResource", mappedBy="role", cascade={"remove"}, fetch="EAGER"))
     * @ApiProperty(readable=false)
     **/
    private $resources;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->resources = new ArrayCollection();
    }

    /**
     * Get role object.
     *
     * @see RoleInterface
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Gets the value of name.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the value of name.
     *
     * @param string $name the name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Sets the value of role.
     *
     * @param string $role the role
     *
     * @return self
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get resource by name.
     *
     * @param string $name [description]
     *
     * @return Resource [description]
     */
    public function getResource($name)
    {
        foreach ($this->resources as $resource) {
            if ($resource->getResource()->getName() == $name) {
                return $resource;
            }
        }

        return false;
    }

    /**
     * Gets the value of resources.
     *
     * @return mixed
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Sets the value of resources.
     *
     * @param mixed $resources the resources
     *
     * @return self
     */
    public function setResources($resources)
    {
        $this->resources = $resources;

        return $this;
    }
}
