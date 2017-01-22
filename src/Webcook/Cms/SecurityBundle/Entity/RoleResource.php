<?php

/**
 * This file is part of Webcook security bundle.
 *
 * See LICENSE file in the root of the bundle. Webcook
 */

namespace Webcook\Cms\SecurityBundle\Entity;

use Webcook\Cms\CoreBundle\Base\BasicEntity;
use Doctrine\ORM\Mapping as ORM;
use Webcook\Cms\SecurityBundle\Authorization\Voter\WebcookCmsVoter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Role resource entity.
 *
 * @ORM\Table(name="Security_role_resource")
 * @ORM\Entity()
 */
class RoleResource extends BasicEntity
{
    /**
     * View permission.
     *
     * @ORM\Column(name="view", type="boolean")
     */
    private $view = true;

    /**
     * Insert permission.
     *
     * @ORM\Column(name="`insert`", type="boolean")
     */
    private $insert = false;

    /**
     * Edit permission.
     *
     * @ORM\Column(name="edit", type="boolean")
     */
    private $edit = false;

    /**
     * Delete permission.
     *
     * @ORM\Column(name="`delete`", type="boolean")
     */
    private $delete = false;

    /**
     * Resource object.
     *
     * @ORM\ManyToOne(targetEntity="Resource", fetch="EAGER")
     * @ORM\JoinColumn(name="resource_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     **/
    private $resource;

    /**
     * Role object.
     *
     * @ORM\ManyToOne(targetEntity="Role", inversedBy="resources")
     **/
    private $role;

    /**
     * Check whether role resource has a permission.
     *
     * @param [type] $attribute [description]
     *
     * @return boolean|null [description]
     */
    public function isGranted($attribute)
    {
        switch ($attribute) {
            case WebcookCmsVoter::ACTION_VIEW:
                return $this->view;
                break;

            case WebcookCmsVoter::ACTION_INSERT:
                return $this->insert;
                break;

            case WebcookCmsVoter::ACTION_EDIT:
                return $this->edit;
                break;

            case WebcookCmsVoter::ACTION_DELETE:
                return $this->delete;
                break;

            default:
                return false;
                break;
        }
    }

    /**
     * Gets the value of view.
     *
     * @return boolean
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Sets the value of view.
     *
     * @param mixed $view the view
     *
     * @return self
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Gets the value of edit.
     *
     * @return boolean
     */
    public function getEdit()
    {
        return $this->edit;
    }

    /**
     * Sets the value of edit.
     *
     * @param boolean $edit the edit
     *
     * @return self
     */
    public function setEdit($edit)
    {
        $this->edit = $edit;

        return $this;
    }

    /**
     * Gets the value of delete.
     *
     * @return boolean
     */
    public function getDelete()
    {
        return $this->delete;
    }

    /**
     * Sets the value of delete.
     *
     * @param boolean $delete the delete
     *
     * @return self
     */
    public function setDelete($delete)
    {
        $this->delete = $delete;

        return $this;
    }

    /**
     * Gets the value of resource.
     *
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Sets the value of resource.
     *
     * @param mixed $resource the resource
     *
     * @return self
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Gets the value of role.
     *
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Sets the value of role.
     *
     * @param mixed $role the role
     *
     * @return self
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Gets the Insert permission.
     *
     * @return boolean
     */
    public function getInsert()
    {
        return $this->insert;
    }

    /**
     * Sets the Insert permission.
     *
     * @param boolean $insert the insert
     *
     * @return self
     */
    public function setInsert($insert)
    {
        $this->insert = $insert;

        return $this;
    }
}
