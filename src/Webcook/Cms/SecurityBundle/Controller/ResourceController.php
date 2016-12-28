<?php

/**
 * This file is part of Webcook security bundle.
 *
 * See LICENSE file in the root of the bundle. Webcook 
 */

namespace Webcook\Cms\SecurityBundle\Controller;

use Webcook\Cms\CoreBundle\Base\BaseRestController;
use Webcook\Cms\SecurityBundle\Entity\Resource;
use Webcook\Cms\SecurityBundle\Entity\RoleResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Webcook\Cms\SecurityBundle\Authorization\Voter\WebcookCmsVoter;
use Webcook\Cms\SecurityBundle\Common\SecurityHelper;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;

/**
 * REST api controller - resource management.
 */
class ResourceController extends BaseRestController
{
    /**
     * Get all resources.
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Return collection of resources.",
     * )
     * @Get(options={"i18n"=false})
     */
    public function getResourcesAction()
    {
        $this->checkPermission(WebcookCmsVoter::ACTION_VIEW);

        $resources = $this->getEntityManager()->getRepository('Webcook\Cms\SecurityBundle\Entity\Resource')->findAll();
        $view = $this->view($resources, 200);

        return $this->handleView($view);
    }

    /**
     * Get single resource by id.
     *
     * @param int $id Id of the desired resource.
     *
     * @ApiDoc(
     *  description="Return single resource.",
     *  parameters={
     *      {"name"="resourceId", "dataType"="integer", "required"=true, "description"="Resource id."}
     *  }
     * )
     * @Get(options={"i18n"=false})
     */
    public function getResourceAction($id)
    {
        $this->checkPermission(WebcookCmsVoter::ACTION_VIEW);

        $resource = $this->getResourceById($id);
        $view = $this->view($resource, 200);

        return $this->handleView($view);
    }

    /**
     * Delete resource.
     *
     * @param int $id Id of the desired resource.
     *
     * @ApiDoc(
     *  description="Delete resource.",
     *  parameters={
     *     {"name"="resourceId", "dataType"="integer", "required"=true, "description"="Resource id."}
     *  }
     * )
     * @Delete(options={"i18n"=false})
     */
    public function deleteResourceAction($id)
    {
        $this->checkPermission(WebcookCmsVoter::ACTION_DELETE);

        $resource = $this->getResourceById($id);

        $this->getEntityManager()->remove($resource);
        $this->getEntityManager()->flush();

        $view = $this->getViewWithMessage(array(), 200, 'Resource has been deleted.');

        return $this->handleView($view);
    }

    /**
     * Add all found resources into database.
     *
     * @ApiDoc(
     *  description="Synchronize resources.",
     * )
     * @Post(options={"i18n"=false})
     */
    public function postResourcesSynchronizeAction()
    {
        $this->checkPermission(WebcookCmsVoter::ACTION_INSERT);

        $resources = SecurityHelper::getResourcesNames($this->container->getParameter('resources_path'));

        $added = 0;
        $resourcesAdded = array();
        foreach ($resources as $resourceName) {
            $exists = $this->getEntityManager()->getRepository('Webcook\Cms\SecurityBundle\Entity\Resource')->findByName($resourceName);

            if (!$exists) {
                $resource = new Resource();
                $resource->setName($resourceName);

                $this->getEntityManager()->persist($resource);
                $this->getEntityManager()->flush();

                $added++;
                $resourcesAdded[] = $resourceName;
            }
        }

        $this->setDefaultPermissions();

        $view = $this->getViewWithMessage(array(
            'added' => $added,
            'resourcesAdded' => $resourcesAdded,
        ), 200, 'Synchronized.');

        return $this->handleView($view);
    }

    /**
     * Set default permissions.
     *
     */
    private function setDefaultPermissions()
    {
        $resources = $this->getEntityManager()->getRepository('Webcook\Cms\SecurityBundle\Entity\Resource')->findAll();
        $roles = $this->getEntityManager()->getRepository('Webcook\Cms\SecurityBundle\Entity\Role')->findAll();

        foreach ($roles as $role) {
            foreach ($resources as $resource) {
                $exists = $this->getEntityManager()->getRepository('Webcook\Cms\SecurityBundle\Entity\RoleResource')->findOneBy(array(
                    'role' => $role,
                    'resource' => $resource,
                ));

                if (!$exists) {
                    $roleResource = new RoleResource();
                    $roleResource->setRole($role);
                    $roleResource->setResource($resource);

                    $this->getEntityManager()->persist($roleResource);
                }
            }
        }

        $this->getEntityManager()->flush();
    }

    /**
     * Get single resource by id.
     *
     * @param int $id Id of the resource.
     *
     * @return Resource
     *
     * @throws NotFoundHttpException If Resource is not found.
     */
    private function getResourceById($id)
    {
        $resource = $this->getEntityManager()->getRepository('Webcook\Cms\SecurityBundle\Entity\Resource')->find($id);

        if (!$resource instanceof Resource) {
            throw new NotFoundHttpException('Resource not found.');
        }

        return $resource;
    }
}
