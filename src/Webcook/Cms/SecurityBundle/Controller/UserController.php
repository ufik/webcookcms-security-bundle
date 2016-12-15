<?php

/**
 * This file is part of Webcook security bundle.
 *
 * See LICENSE file in the root of the bundle. Webcook 
 */

namespace Webcook\Cms\SecurityBundle\Controller;

use Webcook\Cms\CommonBundle\Base\BaseRestController;
use Webcook\Cms\SecurityBundle\Entity\User;
use Webcook\Cms\SecurityBundle\Form\Type\UserType;
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
class UserController extends BaseRestController
{
    /**
     * Get all users.
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Return collection of users."
     * )
     * @Get(options={"i18n"=false})
     */
    public function getUsersAction()
    {
        $this->checkPermission(WebcookCmsVoter::ACTION_VIEW);

        $users = $this->getDoctrine()->getManager()->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->findAll();
        $view = $this->view($users, 200);

        return $this->handleView($view);
    }

    /**
     * Get single user.
     *
     * @param int $id Id of the desired user.
     *`
     * @ApiDoc(
     *  description="Return single user.",
     *  parameters={
     *      {"name"="userId", "dataType"="integer", "required"=true, "description"="User id."}
     *  }
     * )
     * @Get(options={"i18n"=false})
     */
    public function getUserAction($id)
    {
        $this->checkPermission(WebcookCmsVoter::ACTION_VIEW);

        $user = $this->getUserById($id);
        $view = $this->view($user, 200);

        return $this->handleView($view);
    }

    /**
     * Get user secrect key.
     *
     * @param int $id Id of the desired user.
     *`
     * @ApiDoc(
     *  description="Return user secrect.",
     *  parameters={
     *      {"name"="userId", "dataType"="integer", "required"=true, "description"="User id."}
     *  }
     * )
     * @Get(options={"i18n"=false})
     */
    public function getUserKeyAction($id)
    {
        $this->checkPermission(WebcookCmsVoter::ACTION_EDIT);

        $secret = $this->get("scheb_two_factor.security.google_authenticator")->generateSecret();

        $user = $this->getUserById($id);
        $user->setGoogleAuthenticatorSecret($secret);

        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        $view = $this->view(['google_authenticator_secret' => $secret], 200);

        return $this->handleView($view);
    }


    /**
     * Get user QR code.
     *
     * @param int $id Id of the desired user.
     *`
     * @ApiDoc(
     *  description="Return user QR code.",
     *  parameters={
     *      {"name"="userId", "dataType"="integer", "required"=true, "description"="User id."}
     *  }
     * )
     * @Get(options={"i18n"=false})
     */
    public function getUserQrcodeAction($id)
    {          
        $this->checkPermission(WebcookCmsVoter::ACTION_VIEW);

        $user = $this->getUserById($id);

        $url = '';// i set it to empty rather than null because,null giving an empty array, frontend expecting obj. Work around if you think something is wrong
        if ($user->getGoogleAuthenticatorSecret()) {
            $url = $this->get("scheb_two_factor.security.google_authenticator")->getUrl($user); 
        }       

        $view = $this->view(['qrcode' => $url], 200); 

        return $this->handleView($view);
    }

    /**
     * Create a new user.
     *
     * @ApiDoc(
     *  description="Create a new user.",
     *  input="Webcook\Cms\SecurityBundle\Form\Type\UserType",
     *  output="Webcook\Cms\SecurityBundle\Entity\User",
     * )
     * @Post(options={"i18n"=false})
     */
    public function postUsersAction()
    {
        $this->checkPermission(WebcookCmsVoter::ACTION_INSERT);

        $response = $this->processUserForm(new User(), 'POST');

        if ($response instanceof User) {
            $statusCode = 200;
            $message = 'User has been added.';
        } else {
            $statusCode = 400;
            $message = 'Error while adding new user.';
        }

        $view = $this->getViewWithMessage($response, $statusCode, $message);

        return $this->handleView($view);
    }

    /**
     * Update existing user.
     *
     * @param int $id Id of the desired user.
     *
     * @ApiDoc(
     *  description="Update existing user.",
     *  input="Webcook\Cms\SecurityBundle\Form\Type\UserType",
     *  output="Webcook\Cms\SecurityBundle\Entity\User"
     * )
     * @Put(options={"i18n"=false})
     */
    public function putUserAction($id)
    {
        $this->checkPermission(WebcookCmsVoter::ACTION_EDIT);

        try {
            $user = $this->getUserById($id, $this->getLockVersion((string) new User()));
        } catch (NotFoundHttpException $e) {
            $user = new User();
        }

        $response = $this->processUserForm($user, 'PUT');

        if ($response instanceof User) {
            $statusCode = 204;
            $message = 'User has been updated.';
        } else {
            $statusCode = 400;
            $message = 'Error while updating user.';
        }

        $view = $this->getViewWithMessage($response, $statusCode, $message);

        return $this->handleView($view);
    }

    /**
     * Delete user.
     *
     * @param int $id Id of the desired user.
     *
     * @ApiDoc(
     *  description="Delete user.",
     *  parameters={
     *     {"name"="userId", "dataType"="integer", "required"=true, "description"="User id."}
     *  }
     * )
     * @Delete(options={"i18n"=false})
     */
    public function deleteUserAction($id)
    {
        $this->checkPermission(WebcookCmsVoter::ACTION_DELETE);

        $user = $this->getUserById($id);

        $this->getDoctrine()->getManager()->remove($user);
        $this->getDoctrine()->getManager()->flush();

        $view = $this->getViewWithMessage(array(), 200, 'User has been deleted.');

        return $this->handleView($view);
    }

     /**
     * Delete user secrect key.
     *
     * @param int $id Id of the desired user.
     *`
     * @ApiDoc(
     *  description="Delete user secrect.",
     *  parameters={
     *      {"name"="userId", "dataType"="integer", "required"=true, "description"="User id."}
     *  }
     * )
     * @Delete(options={"i18n"=false})
     */
     public function deleteUserKeyAction($id)
     {
        $this->checkPermission(WebcookCmsVoter::ACTION_EDIT);

        $user = $this->getUserById($id);
        $user->setGoogleAuthenticatorSecret(null);

        $this->getDoctrine()->getManager()->flush();

        $view = $this->getViewWithMessage(array(), 200, 'User secret key has been deleted.');

        return $this->handleView($view);
    }

    /**
     * Get active user from the session.
     *
     * @ApiDoc(
     *  description="Return logged user.",
     * )
     * @Get(options={"i18n"=false})
     */
    public function getUserActiveAction()
    {
        $view = $this->view($this->get('security.token_storage')->getToken()->getUser(), 200);

        return $this->handleView($view);
    }

    /**
     * Return form if is not valid, otherwise process form and return role object.
     *
     * @param [type] $user
     * @param string $method
     *
     * @return [type]
     */
    private function processUserForm($user = null, $method = 'POST')
    {
        $oldPassword = '';
        if ($method === 'PUT') {
            $user->removeRoles();
            $oldPassword = $user->getPassword();
        }

        $form = $this->createForm(UserType::class, $user);

        $form = $this->formSubmit($form, $method);

        if ($form->isValid()) {
            $user = $form->getData();

            $plainPassword = $user->getPassword();

            if (!empty($plainPassword)) {
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $password = $encoder->encodePassword($plainPassword, $user->getSalt());
                $user->setPassword($password);
            } else {
                $user->setPassword($oldPassword);
            }

            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();

            return $user;
        }

        return $form;
    }

    /**
     *
     *
     * @param int     $id              [description]
     * @param int     $expectedVersion [description]
     * @param boolean $saveLockVersion [description]
     *
     * @return User [description]
     */
    private function getUserById($id, $expectedVersion = null)
    {
        if ($expectedVersion) {
            $user = $this->getDoctrine()->getManager()->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->find($id, LockMode::OPTIMISTIC, $expectedVersion);
        } else {
            $user = $this->getDoctrine()->getManager()->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->find($id);
        }

        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not found.');
        }

        $this->saveLockVersion($user);

        return $user;
    }


    
}
