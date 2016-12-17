<?php

/**
 * This file is part of Webcook security bundle.
 *
 * See LICENSE file in the root of the bundle. Webcook 
 */

namespace Webcook\Cms\SecurityBundle\Controller;

use Webcook\Cms\CommonBundle\Base\BaseRestController;
use Webcook\Cms\SecurityBundle\Controller\PublicControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Webcook\Cms\SecurityBundle\Entity\User;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Get;

/**
 * Login controller.
 */
class LoginController extends BaseRestController implements PublicControllerInterface
{

     /**
     * Send an email with a link to reset user's password.
     *
     * @ApiDoc(
     *  description="Send an email with a link to reset user's password."
     * )
     * @Post("password/email/reset", options={"i18n"=false})
     */
    public function resetPasswordEmailAction(Request $request): Response
    {        
        $email = $request->request->get('email');
        $user  = $this->getEntityManager()->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->findOneBy(array('email'=> $email));

        if($user === null) {
            $view = $this->getViewWithMessage(null, 404, 'This email does not exist. Please enter a valid email.');
            return $this->handleView($view);
        }

        $resetLink = $this->setResetToken($user);
        $message = \Swift_Message::newInstance()
            ->setSubject('Password Reset')
            ->setFrom('no-reply@webcook.cz')
            ->setTo($email)
            ->setBody($this->render(
                'WebcookCmsSecurityBundle:Auth:emailTemplate.html.twig',
                    array(
                        'user'      => $user->getUsername(),
                        'resetLink' => $resetLink
                    )
                ))
            ->setContentType("text/html");

        $result = $this->get('mailer')->send($message);
        if($result) {
            $view = $this->getViewWithMessage(null, 200, 'Your password reset link was sent to your e-mail address.');
        } else {
            $view = $this->getViewWithMessage(null, 400, 'Cannot send an email.');
        }

        return $this->handleView($view);
    }

     /**
     * Reset password view.
     *
     * @ApiDoc(
     *  description="Reset password view."
     * )
     * @Get("password/reset", options={"i18n"=false})
     */
    public function resetPasswordGetAction(Request $request): Response
    {
        $token = $request->query->get('token');
        $user  = $this->getEntityManager()->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->findOneBy(array('passwordResetToken'=> $token));
        if($user === null || empty($token)){
            $view = $this->getViewWithMessage(null, 404, 'This token is invalid.');
            return $this->handleView($view);
        }

        $dateDiff = date_diff(
            new \DateTime(),
            $user->getPasswordResetExpiration()
        );

        $view          = $this->getViewWithMessage(null, 400, 'This token has expired.');
        $diffInSeconds = $dateDiff->i * 60 + $dateDiff->s;
        if($diffInSeconds < 600 && $dateDiff->y == 0 && $dateDiff->m == 0 && $dateDiff->d == 0 && $dateDiff->h == 0) {
            $view = $this->getViewWithMessage(null, 200, 'Please enter your new password.');
        }

        return $this->handleView($view);
    }

    /**
     * Reset password action.
     *
     * @ApiDoc(
     *  description="Reset password action."
     * )
     * @Post("password/reset", options={"i18n"=false})
     */
    public function resetPasswordPostAction(Request $request): Response
    {
        $password       = $request->request->get('password');
        $repeatPassword = $request->request->get('repeatPassword');
        $token          = $request->request->get('token');
        if(empty($password) || empty($repeatPassword) || empty($token)){
            $view = $this->getViewWithMessage(null, 400, 'Passwords and token can\'t be empty.');
            return $this->handleView($view);
        }

        if($password == $repeatPassword) {
            $user = $this->getEntityManager()->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->findOneBy(array('passwordResetToken'=> $token));
            if($user === null){
                $view = $this->getViewWithMessage(null, 404, 'This token is invalid.');
                return $this->handleView($view);
            }

            $this->setUserPassword($user, $password);

            $view = $this->getViewWithMessage(null, 200, 'Password has been changed.');
            return $this->handleView($view);
        }

        $view = $this->getViewWithMessage(null, 400, 'Passwords dont\'t match.');
        return $this->handleView($view);
    }

    private function setUserPassword(User $user, String $password)
    {
        $factory  = $this->container->get('security.encoder_factory');
        $encoder  = $factory->getEncoder($user);
        $password = $encoder->encodePassword($password, $user->getSalt());

        $user->setPassword($password);
        $user->setPasswordResetToken(null);
        $user->setPasswordResetExpiration(null);

        $this->getEntityManager()->flush();
    }

    private function setResetToken(User $user): string
    {
        $token     = md5(uniqid(mt_rand(), true));            
        $resetLink = $this->generateUrl('reset_password_get', array('token' => $token), true);
        $date      = new \DateTime();

        $user->setPasswordResetToken($token);
        $user->setPasswordResetExpiration($date);

        $this->getEntityManager()->flush();

        return $resetLink;
    }
}
