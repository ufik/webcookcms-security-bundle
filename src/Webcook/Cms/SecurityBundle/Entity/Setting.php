<?php

/**
 * This file is part of Webcook security bundle.
 *
 * See LICENSE file in the root of the bundle. Webcook 
 */

namespace Webcook\Cms\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Webcook\Cms\CoreBundle\Base\BasicEntity;


/**
 * System setting entity.
 *
 * @ORM\Table(name="Setting", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="uniqueSetting", columns={"key1", "section", "user_id"})
 * })
 * @ORM\Entity()
 */
class Setting extends BasicEntity
{
    /**
     * Name of the setting.
     * 
     * @ORM\Column(type="string", length=25)
     */
    private $name;

    /**
     * Key of the setting.
     *
     * @ORM\Column(name="key1", type="string", length=64, nullable=false)
     */
    private $key;

    /**
     * Value of the setting.
     *
     * @ORM\Column(type="string", length=60, nullable=false)
     */
    private $value;

    /**
     * Section of the setting.
     *
     * @ORM\Column(type="string", length=60, nullable=false)
     */
    private $section;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="settings")
     */
    protected $user;


    /**
     * Get name.
     *
     * @inheritDoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name of setting.
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

    /**
     * Get key.
     *
     * @inheritDoc
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Sets the key of setting.
     *
     * @param mixed $key the key
     *
     * @return self
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }


    /**
     * Get value.
     *
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the value of setting.
     *
     * @param mixed $value the value
     *
     * @return self
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get section.
     *
     * @inheritDoc
     */
    public function getSection()
    {
        return $this->section;
    }   

    /**
     * Sets the value of section.
     *
     * @param mixed $section the section
     *
     * @return self
     */
    public function setSection($section)
    {
        $this->section = $section;

        return $this;
    }

    /**
     * Gets the value of user.
     *
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Sets the value of user.
     *
     * @param mixed $user the user
     *
     * @return self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }
    
}
