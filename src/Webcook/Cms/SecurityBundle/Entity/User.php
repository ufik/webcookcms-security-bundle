<?php

/**
 * This file is part of Webcook security bundle.
 *
 * See LICENSE file in the root of the bundle. Webcook 
 */

namespace Webcook\Cms\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Webcook\Cms\CommonBundle\Base\BasicEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use JMS\Serializer\Annotation\Groups;

/**
 * System user entity.
 *
 * @ORM\Table(name="SecurityUser")
 * @ORM\Entity(repositoryClass="Webcook\Cms\SecurityBundle\Entity\UserRepository")
 */
class User extends BasicEntity implements UserInterface, TwoFactorInterface,\Serializable
{
    /**
     * Username of the user.
     * @Groups({"general"})
     * 
     * @ORM\Column(type="string", length=25, unique=true)
     */
    private $username;

    /**
     * Password of the user.
     *
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $password;

    /**
     * Password Reset Token of the agent.
     *
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $passwordResetToken;

    /**
     * Password Reset Expiration of the agent.
     * @ORM\Column(name="passwordResetExpiration", type="datetime", nullable=true)
     */
    private $passwordResetExpiration;

    /**
     * Email of the user.
     *
     * @ORM\Column(type="string", length=60, unique=true)
     */
    private $email;

    /**
     * User's roles.
     *
     * @ORM\ManyToMany(targetEntity="Role")
     */
    private $roles;

    /**
     * Tells whether user account is active or not.
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=true)
     */
    private $isActive;

    /**
     * Google authentication code
     * @ORM\Column(type="string", nullable=true)
     */
    private $googleAuthenticatorSecret;

    /**
     * @ORM\OneToMany(targetEntity="Setting", mappedBy="user", cascade={"persist"})
     */
    private $settings;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->isActive = true;
        $this->settings = new ArrayCollection();

        $setting = new Setting();

        $setting->setName('Timezone');
        $setting->setKey('timezone');
        $setting->setValue('GMT');
        $setting->setSection('general');
        $setting->setUser($this);

        $this->settings->add($setting);

        $setting1 = new Setting();

        $setting1->setName('language');
        $setting1->setKey('language');
        $setting1->setValue('en');
        $setting1->setSection('general');
        $setting1->setUser($this);

        $this->settings->add($setting1);


        // may not be needed, see section on salt below
        //$this->salt = md5(uniqid(null, true));
    }

    /**
     * Get username.
     *
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get salt.
     *
     * @inheritDoc
     */
    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    /**
     * Get password.
     *
     * @inheritDoc
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Get all roles.
     *
     * @inheritDoc
     */
    public function getRoles()
    {
        return $this->roles->toArray();
    }

    /**
     * Not implemented.
     *
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }

    /**
     * Serialize object into array.
     *
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->email,
            $this->version,
            // see section on salt below
            // $this->salt,
        ));
    }

    /**
     * Unserialize array into object.
     *
     * @param $serialized
     *
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->username,
            $this->password,
            $this->email,
            $this->version,

            // see section on salt below
            // $this->salt
        ) = unserialize($serialized);
    }

    /**
     * Gets the value of email.
     *
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the value of email.
     *
     * @param mixed $email the email
     *
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Gets the value of isActive.
     *
     * @return mixed
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Sets the value of isActive.
     *
     * @param mixed $isActive the is active
     *
     * @return self
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Sets the value of username.
     *
     * @param mixed $username the username
     *
     * @return self
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Sets the value of password.
     *
     * @param mixed $password the password
     *
     * @return self
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Add role to the user.
     *
     * @param Role $role [description]
     */
    public function addRole(Role $role)
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
    }

    /**
     * Remove role.
     *
     * @param Role $role
     */
    public function removeRole(Role $role)
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }
    }

    /**
     * Remove all roles.
     *
     */
    public function removeRoles()
    {
        $this->roles->clear();
    }

    /**
     * Get google authentication.
     *
     */
    public function getGoogleAuthenticatorSecret() {
        return $this->googleAuthenticatorSecret;
    }

    /**
     * Set google authentication.
     *
     */
    public function setGoogleAuthenticatorSecret($googleAuthenticatorSecret) {
        $this->googleAuthenticatorSecret = $googleAuthenticatorSecret;
    }

    /**
     * Gets the value of settings.
     *
     * @return mixed
     */
    public function getSettings()
    {
        return $this->settings;
    }

    public function getSettingsByName($name)
    {
        foreach ($this->getSettings() as &$value) {
            if($value->getName() == $name) {
                return $value;
            }
        }
    }

     /**
     * Get passwordResetToken.
     *
     * @inheritDoc
     */
    public function getPasswordResetToken()
    {
        return $this->passwordResetToken;
    }

    /**
     * Sets the value of password reset token.
     *
     * @param mixed $passwordResetToken the passwordResetToken
     *
     * @return self
     */
    public function setPasswordResetToken($passwordResetToken)
    {
        $this->passwordResetToken = $passwordResetToken;

        return $this;
    }

    /**
     * Get passwordResetExpiration.
     *
     * @inheritDoc
     */
    public function getPasswordResetExpiration()
    {
        return $this->passwordResetExpiration;
    }

    /**
     * Sets the value of password reset token.
     *
     * @param mixed $passwordResetExpiration the passwordResetExpiration
     *
     * @return self
     */
    public function setPasswordResetExpiration($passwordResetExpiration)
    {
        $this->passwordResetExpiration = $passwordResetExpiration;

        return $this;
    }


}
