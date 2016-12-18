<?php

/**
 * This file is part of Webcook security bundle.
 *
 * See LICENSE file in the root of the bundle. Webcook 
 */

namespace Webcook\Cms\SecurityBundle\Controller;

use Webcook\Cms\CommonBundle\Base\BaseRestController;
use Webcook\Cms\SecurityBundle\Entity\Setting;
use Webcook\Cms\SecurityBundle\Form\Type\SettingType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Webcook\Cms\SecurityBundle\Authorization\Voter\WebcookCmsVoter;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;

/**
 * REST api controller - setting management.
 */
class SettingController extends BaseRestController
{
    /**
     * Get all settings.
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Return collection of settings."
     * )
     * @Get(options={"i18n"=false})
     */
    public function getSettingsAction()
    {  
        $this->checkPermission(WebcookCmsVoter::ACTION_VIEW);

        $settings = $this->getDoctrine()->getManager()->getRepository('Webcook\Cms\SecurityBundle\Entity\Setting')->findAll();
        $view = $this->view($settings, 200);

        return $this->handleView($view);
    }

    /**
     * Get single settings.
     *
     * @param int $id Id of the desired setting.
     *`
     * @ApiDoc(
     *  description="Return single setting.",
     *  parameters={
     *      {"name"="settingId", "dataType"="integer", "required"=true, "description"="Setting id."}
     *  }
     * )
     * @Get(options={"i18n"=false})
     */
    public function getSettingAction($id)
    {
        $this->checkPermission(WebcookCmsVoter::ACTION_VIEW);

        $setting = $this->getSettingById($id);
        $view = $this->view($setting, 200);

        return $this->handleView($view);
    }

    /**
     * Create a new setting.
     *
     * @ApiDoc(
     *  description="Create a new setting.",
     *  input="Webcook\Cms\SecurityBundle\Form\Type\SettingType",
     *  output="Webcook\Cms\SecurityBundle\Entity\setting",
     * )
     * @Post(options={"i18n"=false})
     */
    public function postSettingsAction()
    {
        $this->checkPermission(WebcookCmsVoter::ACTION_INSERT);

        $response = $this->processSettingForm(new Setting(), 'POST');

        if ($response instanceof Setting) {
            $statusCode = 200;
            $message = 'Setting has been added.';
        } else {
            $statusCode = 400;
            $message = 'Error while adding new setting.';
        }

        $view = $this->getViewWithMessage($response, $statusCode, $message);

        return $this->handleView($view);
    }
    /**
     * Update existing setting.
     *
     * @param int $id Id of the desired setting.
     *
     * @ApiDoc(
     *  description="Update existing setting.",
     *  input="Webcook\Cms\SecurityBundle\Form\Type\SettingType",
     *  output="Webcook\Cms\SecurityBundle\Entity\Seting"
     * )
     * @Put(options={"i18n"=false})
     */
    public function putSettingAction($id)
    {
        $this->checkPermission(WebcookCmsVoter::ACTION_EDIT);

        try {
            $setting = $this->getsettingById($id);
        } catch (NotFoundHttpException $e) {
            $setting = new Setting();
        }

        $response = $this->processSettingForm($setting, 'PUT');

        if ($response instanceof Setting) {
            $statusCode = 204;
            $message = 'Setting has been updated.';
        } else {
            $statusCode = 400;
            $message = 'Error while updating setting.';
        }

        $view = $this->getViewWithMessage($response, $statusCode, $message);

        return $this->handleView($view);
    }


    /**
     * Return form if is not valid, otherwise process form and return setting object.
     *
     * @param Setting $setting
     * @param string $method
     *
     * @return [type]
     */
    private function processSettingForm($setting = null, $method = 'POST')
    {        
        $form = $this->createForm(SettingType::class, $setting);

        $form = $this->formSubmit($form, $method);

        if ($form->isValid()) {
            $setting = $form->getData();

            if ($method === 'POST') {
                $setting->setUser($this->getUser());
            }

            $this->getDoctrine()->getManager()->persist($setting);
            $this->getDoctrine()->getManager()->flush();

            return $setting;
        }

        return $form;
    }

    /**
     *
     *
     * @param int     $id              [description]
     *
     * @return Setting [description]
     */
    private function getSettingById($id)
    {
        $setting = $this->getDoctrine()->getManager()->getRepository('Webcook\Cms\SecurityBundle\Entity\Setting')->find($id);

        if (!$setting instanceof Setting) {
            throw new NotFoundHttpException('Setting not found.');
        }

        return $setting;
    }


    
}
