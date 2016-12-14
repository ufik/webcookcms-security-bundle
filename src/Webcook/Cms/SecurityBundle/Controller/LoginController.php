<?php

/**
 * This file is part of Webcook security bundle.
 *
 * See LICENSE file in the root of the bundle. Webcook 
 */

namespace Webcook\Cms\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Webcook\Cms\SecurityBundle\Entity\User;
use Webcook\Cms\SecurityBundle\Form\Type\UserType;

/**
 * Login controller.
 */
class LoginController extends Controller
{
    private $mailer;

    /**
     * @TODO refactor!
     * Login form action.
     *
     * @param Request $request
     *
     * @return
     */
    public function loginAction(Request $request)
    {
        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContextInterface::AUTHENTICATION_ERROR
            );
        } elseif (null !== $session && $session->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
            $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(SecurityContextInterface::LAST_USERNAME);

        return $this->render(
            'WebcookCmsSecurityBundle:Auth:login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error'         => $error,
                'success' => null
            )
        );
    }

    public function forgotPasswordAction(Request $request)
    {
        return $this->render(
            'WebcookCmsSecurityBundle:Auth:forgotPassword.html.twig',
            array(
                'success'         => null,
                'error'         => null,
            )
        );      
    }

    /**
     * Send an email with link to reset password.
     *
     */
    public function resetPasswordEmailAction(Request $request)
    {   
            
         // $_POST parameters        
        $email = $request->request->get('_email');
        if (empty($email)) {
            return $this->render(
                'WebcookCmsSecurityBundle:Auth:forgotPassword.html.twig',
                array(
                    // last username entered by the user
                    'success' => null,
                    'error' => 'This email does not exist. Please enter a valid email.',
                )
            );
        }

        try{
            $user = $this->getDoctrine()->getManager()->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->findOneBy(array('email'=> $email));
            if($user === NULL){
                return $this->render(
                    'WebcookCmsSecurityBundle:Auth:forgotPassword.html.twig',
                    array(
                        // last username entered by the user
                        'success' => null,
                        'error' => 'This email does not exist. Please enter a valid email.',
                    )
                );
            }

            $encrypt = md5(uniqid(mt_rand(), true));            
            $resetLink = $this->generateUrl('_security_resetPasswordView',array('encrypt' => $encrypt), true);
            $date = new \DateTime();
            $user->setPasswordResetToken($encrypt);
            $user->setPasswordResetExpiration($date);
            $from = 'no-reply@webcook.cz';

            // Create the Transport
            $message = \Swift_Message::newInstance()
                ->setSubject('Password Reset')
                ->setFrom($from)
                ->setTo($email)
                ->setBody($this->render(
                    'WebcookCmsSecurityBundle:Auth:emailTemplate.html.twig',
                        array(
                            'user' => $user->getUsername(),
                            'resetLink' => $resetLink
                        )
                    ))
                ->setContentType("text/html");      

            $result = $this->getMailer()->send($message);

            if($result) {
                return $this->render(
                    'WebcookCmsSecurityBundle:Auth:forgotPassword.html.twig',
                    array(
                        // last username entered by the user
                        'success' => "Your password reset link was sent to your e-mail address.",
                        'error' => null,
                        //'' => 'does not exists. Please try again',
                    )
                );
            } else {
                return $this->render(
                    'WebcookCmsSecurityBundle:Auth:forgotPassword.html.twig',
                    array(
                        // last username entered by the user
                        'success' => null,
                        'error' => 'Something went wrong on our side. Please try again',
                    )
                );
            }  

        } catch(\Exception $e){
             return $this->render(
                'WebcookCmsSecurityBundle:Auth:forgotPassword.html.twig',
                array(
                    // last username entered by the user
                    'success' => null,
                    'error' => 'This email does not exist. Please enter a valid email.',
                )
            );
        }        
    }

    public function resetPasswordViewAction(Request $request)
    { 
        // $_POST parameters        
        $token = $request->query->get('encrypt');

        if (empty($token)) {
            return $this->render(
                'WebcookCmsSecurityBundle:Auth:invalid.html.twig',
                array(
                    // last username entered by the user
                    'success' => null,
                    'error' => 'This link is invalid.',
                )
            );
        }

        try {
            $user = $this->getDoctrine()->getManager()->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->findOneBy(array('passwordResetToken'=> $token));
            if($user === NULL){
                return $this->render(
                    'WebcookCmsSecurityBundle:Auth:invalid.html.twig',
                    array(
                        // last username entered by the user
                        'success' => null,
                        'error' => 'This link is invalid.',
                    )
                );
            }

            $date = new \DateTime();
            $expiryTime = $user->getPasswordResetExpiration();

            $result = date_diff($date,$expiryTime);

            $diff = $result->i * 60 + $result->s;

            if($diff < 600 && $result->y == 0 && $result->m == 0 && $result->d == 0 && $result->h == 0) {
                return $this->render(
                    'WebcookCmsSecurityBundle:Auth:resetPassword.html.twig',
                    array(
                        // last username entered by the user
                        'token' => $token,
                        'success' => 'Please enter your new password.',
                        'error' => null,
                    )
                );
            } else {
                return $this->render(
                    'WebcookCmsSecurityBundle:Auth:invalid.html.twig',
                    array(
                        // last username entered by the user
                        'success' => null,
                        'error' => 'This link has expired.',
                    )
                );
            }
        } catch(\Exception $e){
            return $this->render(
                'WebcookCmsSecurityBundle:Auth:invalid.html.twig',
                array(
                    // last username entered by the user
                    'success' => null,
                    'error' => 'This link is invalid.',
                )
            );
        }        

    }

    public function resetPasswordAction(Request $request){

        $password = $request->request->get('_password');
        $repeatPassword = $request->request->get('_repeatPassword');
        $token = $request->request->get('_token');

        if (empty($token) ) {
            return $this->render(
                'WebcookCmsSecurityBundle:Auth:invalid.html.twig',
                array(
                    // last username entered by the user
                    'success' => null,
                    'error' => 'This link is invalid.',
                )
            );
        }

        if(empty($password) || empty($repeatPassword)){
            return $this->render(
                'WebcookCmsSecurityBundle:Auth:resetPassword.html.twig',
                array(
                        // last username entered by the user
                    'token' => $token,
                    'success' => null,
                    'error' => 'Passwords can\'t be empty',
                    )
            );
        }

        if($password == $repeatPassword) {
            try {
                $user = $this->getDoctrine()->getManager()->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->findOneBy(array('passwordResetToken'=> $token));     
                
                if($user === NULL){
                    return $this->render(
                        'WebcookCmsSecurityBundle:Auth:invalid.html.twig',
                        array(
                            // last username entered by the user
                            'success' => null,
                            'error' => 'This link is invalid.',
                        )
                    );
                }

                $factory = $this->container->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $password = $encoder->encodePassword($password, $user->getSalt());
                $user->setPassword($password);

                $user->setPasswordResetToken(null);

                return $this->render(
                        'WebcookCmsSecurityBundle:Auth:login.html.twig',
                        array(
                            // last username entered by the user
                            'success' => 'Please login with your new password.',
                            'error' => null,
                        )
                    );
            } catch(\Exception $e){
                 return $this->render(
                    'WebcookCmsSecurityBundle:Auth:invalid.html.twig',
                    array(
                        // last username entered by the user
                        'success' => null,
                        'error' => 'This link is invalid.',
                    )
                );
            }
           
        } else {
            return $this->render(
                'WebcookCmsSecurityBundle:Auth:resetPassword.html.twig',
                array(
                    // last username entered by the user
                    'token' => $token,
                    'success' => null,
                    'error' => 'Your passwords don\'t match.',
                )
            );
        }
    }

    public function setMailer($mailer)
    {
        $this->mailer = $mailer;
    }

    private function getMailer()
    {
        if(!$this->mailer) {
            return $this->get('mailer');
        }

        return $this->mailer;
    }
}
