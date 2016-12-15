<?php

namespace Webcook\Cms\SecurityBundle\Tests\Controller;

use Webcook\Cms\SecurityBundle\Controller\LoginController;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\ParameterBag;

class LoginControllerTest extends \Webcook\Cms\CommonBundle\Tests\BasicTestCase
{
    public function testLoginController()
    {
        $container = static::$kernel->getContainer();
        $templating = $this->getMockTemplating();

        $container->set('templating', $templating);
        
        $controller = new LoginController();
        $controller->setContainer($container);

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                     ->disableOriginalConstructor()
                     ->createMock();

        $request->attributes = new ParameterBag();

        $this->assertEquals('success', $controller->loginAction($request));
    }

    public function testLoginControllerError()
    {
        $container = static::$kernel->getContainer();
        $templating = $this->getMockTemplating();

        $container->set('templating', $templating);
        
        $controller = new LoginController();
        $controller->setContainer($container);

        $attributes = new ParameterBag();
        $attributes->set(Security::AUTHENTICATION_ERROR, true);

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                     ->disableOriginalConstructor()
                     ->createMock();

        $request->attributes = $attributes;

        $this->assertEquals('success', $controller->loginAction($request));
    }

    public function testLoginControllerSession()
    {
        $container = static::$kernel->getContainer();
        $templating = $this->getMockTemplating();

        $container->set('templating', $templating);
        
        $controller = new LoginController();
        $controller->setContainer($container);

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request', array('getSession'))
                     ->disableOriginalConstructor()
                     ->createMock();

        $request->attributes = new ParameterBag();

        $session = $this->getMockSession();
        $request->expects($this->once())
            ->method('getSession')
            ->will($this->returnValue($session));


        $this->assertEquals('success', $controller->loginAction($request));
    }

    public function testForgotPasswordAction()
    {
        $container = static::$kernel->getContainer();
        $templating = $this->getMockTemplating();

        $container->set('templating', $templating);
        
        $controller = new LoginController();
        $controller->setContainer($container);

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                     ->disableOriginalConstructor()
                     ->createMock();

        $this->assertEquals('success', $controller->forgotPasswordAction($request));
    }

    public function testResetPasswordNoEmail()
    {
        $container = static::$kernel->getContainer();
        $templating = $this->getMockTemplating('error');

        $container->set('templating', $templating);
        
        $controller = new LoginController();
        $controller->setContainer($container);

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                     ->disableOriginalConstructor()
                     ->createMock();

        $request->request = new ParameterBag();

        $this->assertEquals('error', $controller->resetPasswordEmailAction($request));
    }

    public function testResetPasswordRandomEmail()
    {
        $this->loadFixtures(array());

        $container = static::$kernel->getContainer();
        $templating = $this->getMockTemplating('error');

        $container->set('templating', $templating);
        
        $controller = new LoginController();
        $controller->setContainer($container);

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                     ->disableOriginalConstructor()
                     ->createMock();

        $request->request = new ParameterBag();
        $request->request->add(array('_email' => 'some@email.com'));
        $this->assertEquals('error', $controller->resetPasswordEmailAction($request));
    }

    public function testResetPasswordValidEmailSuccess()
    {
        $this->loadFixtures(array());

        $container = static::$kernel->getContainer();
        $templating = $this->getMockTemplating();
        $mailer = $this->getMockMailer(true);
        $router = $this->getMockRouter();

        $container->set('templating', $templating);
        $container->set('router', $router);
        
        $controller = new LoginController();
        $controller->setContainer($container);
        $controller->setMailer($mailer);

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                     ->disableOriginalConstructor()
                     ->createMock();

        $request->request = new ParameterBag();
        $request->request->add(array('_email' => 'info@Webcook.com'));
        $this->assertEquals('success', $controller->resetPasswordEmailAction($request));
    }

    public function testResetPasswordValidEmailFail()
    {
        $this->loadFixtures(array());

        $container = static::$kernel->getContainer();
        $templating = $this->getMockTemplating('error');
        $mailer = $this->getMockMailer(false);
        $router = $this->getMockRouter();

        $container->set('templating', $templating);
        $container->set('router', $router);
        
        $controller = new LoginController();
        $controller->setContainer($container);
        $controller->setMailer($mailer);

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                     ->disableOriginalConstructor()
                     ->createMock();

        $request->request = new ParameterBag();
        $request->request->add(array('_email' => 'info@Webcook.com'));
        $this->assertEquals('error', $controller->resetPasswordEmailAction($request));
    }

    public function testResetPasswordViewNoToken()
    {
        $container = static::$kernel->getContainer();
        $templating = $this->getMockTemplating('error');

        $container->set('templating', $templating);

        $controller = new LoginController();
        $controller->setContainer($container);

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                     ->disableOriginalConstructor()
                     ->createMock();

        $request->query = new ParameterBag();

        $this->assertEquals('error', $controller->resetPasswordViewAction($request));
    }

    public function testResetPasswordViewInvalidToken()
    {
        $this->loadFixtures(array());

        $container = static::$kernel->getContainer();
        $templating = $this->getMockTemplating('error');

        $container->set('templating', $templating);

        $controller = new LoginController();
        $controller->setContainer($container);

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                     ->disableOriginalConstructor()
                     ->createMock();

        $request->query = new ParameterBag();
        $request->query->add(array('encrypt' => 'someToken'));

        $this->assertEquals('error', $controller->resetPasswordViewAction($request));
    }

    public function testResetPasswordViewValid()
    {
        $this->loadFixtures(array());

        $token = 'validResetToken';
        $users = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->findAll();
        $user = $users[0];
        $user->setPasswordResetToken($token);
        $user->setPasswordResetExpiration(new \DateTime('now'));
        $this->em->flush();

        $container = static::$kernel->getContainer();
        $templating = $this->getMockTemplating('success');

        $container->set('templating', $templating);

        $controller = new LoginController();
        $controller->setContainer($container);

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                     ->disableOriginalConstructor()
                     ->createMock();

        $request->query = new ParameterBag();
        $request->query->add(array('encrypt' => $token));

        $this->assertEquals('success', $controller->resetPasswordViewAction($request));
    }    

    public function testResetPasswordViewExpired()
    {
        $this->loadFixtures(array());

        $token = 'validResetToken';
        $users = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->findAll();
        $user = $users[0];
        $user->setPasswordResetToken($token);
        $date = new \DateTime('now');
        $date->modify('-2 hour');
        $user->setPasswordResetExpiration($date);
        $this->em->flush();

        $container = static::$kernel->getContainer();
        $templating = $this->getMockTemplating('success');

        $container->set('templating', $templating);

        $controller = new LoginController();
        $controller->setContainer($container);

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                     ->disableOriginalConstructor()
                     ->createMock();

        $request->query = new ParameterBag();
        $request->query->add(array('encrypt' => $token));

        $this->assertEquals('success', $controller->resetPasswordViewAction($request));
    }

    public function testResetPasswordViewException()
    {
        //No password reset time set
        $this->loadFixtures(array());

        $token = 'validResetToken';
        $users = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->findAll();
        $user = $users[0];
        $user->setPasswordResetToken($token);
        $this->em->flush();

        $container = static::$kernel->getContainer();
        $templating = $this->getMockTemplating('error');

        $container->set('templating', $templating);

        $controller = new LoginController();
        $controller->setContainer($container);

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                     ->disableOriginalConstructor()
                     ->createMock();

        $request->query = new ParameterBag();
        $request->query->add(array('encrypt' => $token));

        $this->assertEquals('error', $controller->resetPasswordViewAction($request));
    }

    public function testResetPasswordNoToken()
    {
        $container = static::$kernel->getContainer();
        $templating = $this->getMockTemplating('error');

        $container->set('templating', $templating);

        $controller = new LoginController();
        $controller->setContainer($container);

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                     ->disableOriginalConstructor()
                     ->createMock();

        $request->request = new ParameterBag();
        $request->request->add(array());

        $this->assertEquals('error', $controller->resetPasswordAction($request));
    }

    public function testResetPasswordNoPassword()
    {
        $container = static::$kernel->getContainer();
        $templating = $this->getMockTemplating('error');

        $container->set('templating', $templating);

        $controller = new LoginController();
        $controller->setContainer($container);

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                     ->disableOriginalConstructor()
                     ->createMock();

        $request->request = new ParameterBag();
        $request->request->add(array('_token' => 'sometoken'));

        $this->assertEquals('error', $controller->resetPasswordAction($request));
    }

    public function testResetPasswordNoUser()
    {
        $this->loadFixtures(array());

        $container = static::$kernel->getContainer();
        $templating = $this->getMockTemplating('error');

        $container->set('templating', $templating);

        $controller = new LoginController();
        $controller->setContainer($container);

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                     ->disableOriginalConstructor()
                     ->createMock();

        $request->request = new ParameterBag();
        $request->request->add(array('_token' => 'sometoken', '_password' => 'pass', '_repeatPassword' => 'pass'));

        $this->assertEquals('error', $controller->resetPasswordAction($request));
    }

    public function testResetPasswordMismatch()
    {
        $container = static::$kernel->getContainer();
        $templating = $this->getMockTemplating('error');

        $container->set('templating', $templating);

        $controller = new LoginController();
        $controller->setContainer($container);

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                     ->disableOriginalConstructor()
                     ->createMock();

        $request->request = new ParameterBag();
        $request->request->add(array('_token' => 'sometoken', '_password' => 'pass', '_repeatPassword' => 'passe'));

        $this->assertEquals('error', $controller->resetPasswordAction($request));
    }

    public function testResetPasswordSuccess()
    {
        $this->loadFixtures(array());
        $token = 'passwordResetToken';
        $users = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->findAll();
        $user = $users[0];
        $user->setPasswordResetToken($token);
        $this->em->flush();

        $container = static::$kernel->getContainer();
        $templating = $this->getMockTemplating('success');

        $container->set('templating', $templating);

        $controller = new LoginController();
        $controller->setContainer($container);

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                     ->disableOriginalConstructor()
                     ->createMock();

        $request->request = new ParameterBag();
        $request->request->add(array('_token' => $token, '_password' => 'pass', '_repeatPassword' => 'pass'));

        $this->assertEquals('success', $controller->resetPasswordAction($request));

        $users = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->findAll();
        $user = $users[0];

        $this->assertEquals(null, $user->getPasswordResetToken());

        $factory = $container->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);
        $password = $encoder->encodePassword('pass', $user->getSalt());

        $this->assertEquals($password, $user->getPassword());
    }

    private function getMockRouter()
    {
        $router = $this->getMockBuilder('Symfony\Component\Routing\Router', array('generate'))
                     ->disableOriginalConstructor()
                     ->createMock();

        $router->expects($this->once())
            ->method('generate')
            ->with($this->anything())
            ->will($this->returnValue('http://unsublink.com'));

        return $router;
    }

    private function getMockTemplating($return = 'success')
    {
        $templating = $this->createMock('Symfony\Bundle\FrameworkBundle\Templating\Engine', array('renderResponse'));

        $templating->expects($this->any())
            ->method('renderResponse')
            ->with($this->anything())
            ->will($this->returnValue($return));

        return $templating;
    }

    private function getMockMailer($return)
    {
        $mailer = $this->getMockBuilder('Swift_Mailer')
                ->disableOriginalConstructor()
                ->setMethods(array('send'))
                ->createMock();

        $mailer->expects($this->any())
            ->method('send')
            ->with($this->anything())
            ->will($this->returnValue($return));

        return $mailer;
    }

    private function getMockSession()
    {
        $session = $this->createMock('Symfony\Component\HttpFoundation\Session', array('has', 'get', 'remove'));

        $session->expects($this->once())
            ->method('has')
            ->with($this->anything())
            ->will($this->returnValue(true));

        $session->expects($this->any())
            ->method('get')
            ->with($this->anything())
            ->will($this->returnValue('error'));

        $session->expects($this->once())
            ->method('remove')
            ->with($this->anything())
            ->will($this->returnValue(true));

        return $session;
    }
}
