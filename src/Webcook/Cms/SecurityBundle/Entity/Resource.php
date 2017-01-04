<?php

/**
 * This file is part of Webcook security bundle.
 *
 * See LICENSE file in the root of the bundle. Webcook 
 */

namespace Webcook\Cms\SecurityBundle\Entity;

use Webcook\Cms\CoreBundle\Base\BasicEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * System resource entity.
 *
 * @ORM\Table(name="SecurityResource")
 * @ORM\Entity()
 */
class Resource extends BasicEntity
{
    /**
     * Resource name.
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64, unique=true)
     */
    private $name;

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
     * @param mixed $name the name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}
