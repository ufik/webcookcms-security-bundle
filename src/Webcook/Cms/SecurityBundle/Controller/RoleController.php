<?php

/**
 * This file is part of Webcook security bundle.
 *
 * See LICENSE file in the root of the bundle. Webcook
 */

namespace Webcook\Cms\SecurityBundle\Controller;

use Webcook\Cms\CoreBundle\Base\BaseRestController;
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
 * REST api controller - role management.
 */
class RoleController extends BaseRestController
{
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
        $parameters = $this->getPutParameters('roleResources');

        foreach ($parameters as $roleResourceArray) {
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
}
