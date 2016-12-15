<?php

/**
 * This file is part of Webcook security bundle.
 *
 * See LICENSE file in the root of the bundle. Webcook 
 */

namespace Webcook\Cms\SecurityBundle\Controller;

use Webcook\Cms\CommonBundle\Base\BaseRestController;
use Webcook\Cms\SecurityBundle\Entity\Role;
use Webcook\Cms\SecurityBundle\Entity\Resource;
use Webcook\Cms\SecurityBundle\Entity\RoleResource;
use Webcook\Cms\SecurityBundle\Form\Type\RoleType;
use Webcook\Cms\SecurityBundle\Form\Type\ResourcesType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Webcook\Cms\SecurityBundle\Authorization\Voter\WebcookCmsVoter;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;
use Doctrine\DBAL\LockMode;

/**
 * REST api controller - user management.
 */
class RoleController extends BaseRestController
{
    /**
     * Get all roles.
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Return collection of roles.",
     * )
     * @Get(options={"i18n"=false})
     */
    public function getRolesAction()
    {
        $this->checkPermission(WebcookCmsVoter::ACTION_VIEW);

        $roles = $this->getDoctrine()->getManager()->getRepository('Webcook\Cms\SecurityBundle\Entity\Role')->findAll();
        $view = $this->view($roles, 200);

        return $this->handleView($view);
    }

    /**
     * Get single role.
     *
     * @param int $id Id of the desired role.
     *
     * @ApiDoc(
     *  description="Return single role.",
     *  parameters={
     *      {"name"="roleId", "dataType"="integer", "required"=true, "description"="Role id."}
     *  }
     * )
     * @Get(options={"i18n"=false})
     */
    public function getRoleAction($id)
    {
        $this->checkPermission(WebcookCmsVoter::ACTION_VIEW);

        $role = $this->getRoleById($id);
        $view = $this->view($role, 200);

        return $this->handleView($view);
    }

    /**
     * Save new role.
     *
     * @ApiDoc(
     *  description="Create a new role.",
     *  input="Webcook\Cms\SecurityBundle\Form\Type\RoleType",
     *  output="Webcook\Cms\SecurityBundle\Entity\Role",
     * )
     * @Post(options={"i18n"=false})
     */
    public function postRolesAction()
    {
        $this->checkPermission(WebcookCmsVoter::ACTION_INSERT);

        $response = $this->processRoleForm(new Role(), 'POST');

        if ($response instanceof Role) {
            $this->addResources($response);

            $statusCode = 200;
            $message = 'Role has been added.';
        } else {
            $statusCode = 400;
            $message = 'Error while adding new role.';
        }

        $view = $this->getViewWithMessage($response, $statusCode, $message);

        return $this->handleView($view);
    }

    /**
     * Add all resources from the system.
     *
     * @param Role $role
     */
    private function addResources(Role $role)
    {
        $resources = $this->getEntityManager()->getRepository('Webcook\Cms\SecurityBundle\Entity\Resource')->findAll();

        foreach ($resources as $resource) {
            $roleResource = new RoleResource();
            $roleResource->setRole($role);
            $roleResource->setResource($resource);

            $this->getEntityManager()->persist($roleResource);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * Update role.
     *
     * @param int $id Id of the desired role.
     *
     * @ApiDoc(
     *  description="Update existing role.",
     *  input="Webcook\Cms\SecurityBundle\Form\Type\RoleType",
     *  output="Webcook\Cms\SecurityBundle\Entity\Role"
     * )
     * @Put(options={"i18n"=false})
     */
    public function putRoleAction($id)
    {
        $this->checkPermission(WebcookCmsVoter::ACTION_EDIT);

        try {
            $role = $this->getRoleById($id, $this->getLockVersion((string) new Role()));
        } catch (NotFoundHttpException $e) {
            $role = new Role();
        }

        $response = $this->processRoleForm($role, 'PUT');

        if ($response instanceof Role) {
            $statusCode = 204;
            $message = 'Role has been updated.';
        } else {
            $statusCode = 400;
            $message = 'Error while updating role.';
        }

        $view = $this->getViewWithMessage($response, $statusCode, $message);

        return $this->handleView($view);
    }

    /**
     * Update resources of the role.
     *
     * @param int $id Id of the desired role.
     *
     * @ApiDoc(
     *  description="Update existing role.",
     *  input="Webcook\Cms\SecurityBundle\Form\Type\ResourcesType"
     * )
     * @Put(options={"i18n"=false})
     */
    public function putRolesResourcesAction($id)
    {
        $this->checkPermission(WebcookCmsVoter::ACTION_EDIT);

        $parameters = $this->getPutParameters('resources');

        foreach ($parameters['roleResources'] as $roleResourceArray) {
            $roleResource = $this->getEntityManager()->getRepository('Webcook\Cms\SecurityBundle\Entity\RoleResource')->find($roleResourceArray['id']);

            if ($roleResource) {
                $roleResource->setView($this->getValueFromArray($roleResourceArray, 'view'));
                $roleResource->setInsert($this->getValueFromArray($roleResourceArray, 'insert'));
                $roleResource->setEdit($this->getValueFromArray($roleResourceArray, 'edit'));
                $roleResource->setDelete($this->getValueFromArray($roleResourceArray, 'delete'));
            }
        }

        $this->getEntityManager()->flush();

        $view = $this->view(null, 204);

        return $this->handleView($view);
    }

    /**
     * Get specific value from an array.
     *
     * @param array  $array array of values
     * @param string $key   key of desired value
     *
     * @return bool|string Value or false.
     */
    private function getValueFromArray($array, $key)
    {
        if (array_key_exists($key, $array)) {
            return filter_var($array[$key], FILTER_VALIDATE_BOOLEAN);
        }

        return false;
    }

    /**
     * Delete role.
     *
     * @param int $id Id of the desired role.
     *
     * @ApiDoc(
     *  description="Delete role.",
     *  parameters={
     *     {"name"="roleId", "dataType"="integer", "required"=true, "description"="Role id."}
     *  }
     * )
     * @Delete(options={"i18n"=false})
     */
    public function deleteRoleAction($id)
    {
        $this->checkPermission(WebcookCmsVoter::ACTION_DELETE);

        $role = $this->getRoleById($id);

        $this->getEntityManager()->remove($role);
        $this->getEntityManager()->flush();

        $view = $this->getViewWithMessage(array(), 200, 'Role has been deleted.');

        return $this->handleView($view);
    }

    /**
     * Return form if is not valid, otherwise process form and return role object.
     *
     * @param Role   $role
     * @param string $method Method of request
     *
     * @return Form [description]
     */
    private function processRoleForm(Role $role, $method = 'POST')
    {
        $form = $this->createForm(RoleType::class, $role);
        //$form->add('submit', 'submit', array('label' => 'security.roles.form.submit_button', 'attr' => array('class' => 'btn btn-primary btn-sm')));

        $form = $this->formSubmit($form, $method);

        if ($form->isValid()) {
            $role = $form->getData();

            if ($role instanceof Role) {
                $this->getDoctrine()->getManager()->persist($role);
            }

            $this->getDoctrine()->getManager()->flush();

            return $role;
        }

        return $form;
    }

    /**
     * Get role by id.
     *
     * @param int $id [description]
     *
     * @return Role
     *
     * @throws NotFoundHttpException If role doesn't exist
     */
    private function getRoleById($id, $expectedVersion = null)
    {
        if ($expectedVersion) {
            $role = $this->getDoctrine()->getManager()->getRepository('Webcook\Cms\SecurityBundle\Entity\Role')->find($id, LockMode::OPTIMISTIC, $expectedVersion);
        } else {
            $role = $this->getDoctrine()->getManager()->getRepository('Webcook\Cms\SecurityBundle\Entity\Role')->find($id);
        }

        if (!$role instanceof Role) {
            throw new NotFoundHttpException('Role not found.');
        }

        $this->saveLockVersion($role);

        return $role;
    }
}
