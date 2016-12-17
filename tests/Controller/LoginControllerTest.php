<?php

namespace Webcook\Cms\SecurityBundle\Tests\Controller;

use Webcook\Cms\SecurityBundle\Controller\LoginController;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Bundle\FrameworkBundle\Tests\Templating\Helper\Fixtures\StubTemplateNameParser;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\Loader\FilesystemLoader;

class LoginControllerTest extends \Webcook\Cms\CommonBundle\Tests\BasicTestCase
{
    public function testPasswordEmail()
    {
        $this->createTestClient();

        $this->client->getContainer()->set('mailer', $this->getMockMailer(true));

        $this->sendResetEmail();

        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testPasswordEmailFailed()
    {
        $this->createTestClient();

        $this->client->getContainer()->set('mailer', $this->getMockMailer(false));

        $this->sendResetEmail();

        $this->assertFalse($this->client->getResponse()->isSuccessful());
    }

    public function testPasswordWrongEmail()
    {
        $this->createTestClient();
        $this->client->request(
            'POST',
            '/api/password/email/reset',
            array(
                'email' => "info@Webcook.c"
            )
        );

        $this->assertFalse($this->client->getResponse()->isSuccessful());
    }

    public function testPasswordResetFail()
    {
        $this->createTestClient();
        $this->client->request(
            'GET',
            '/api/password/reset',
            array(
                'token' => "info@Webcook.c"
            )
        );

        $this->assertFalse($this->client->getResponse()->isSuccessful());
    }

    public function testPasswordReset()
    {
        $this->createTestClient();
        $this->sendResetEmail();

        $user = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->find(1);
        $this->client->request(
            'GET',
            '/api/password/reset',
            array(
                'token' => $user->getPasswordResetToken()
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testPasswordResetExpiredToken()
    {
        $this->createTestClient();
        $this->sendResetEmail();

        $user = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->find(1);
        $date = new \DateTime('now');
        $date->modify('-10 minute');
        $user->setPasswordResetExpiration($date);
        $this->em->flush();
        
        $this->client->request(
            'GET',
            '/api/password/reset',
            array(
                'token' => $user->getPasswordResetToken()
            )
        );

        $this->assertFalse($this->client->getResponse()->isSuccessful());
    }
    
    public function testPasswordResetPostFailMissingField()
    {
        $this->createTestClient();
        $this->sendResetEmail();
        
        $user = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->find(1);
        $this->client->request(
            'POST',
            '/api/password/reset',
            array(
                'token' => $user->getPasswordResetToken()
            )
        );

        $this->assertFalse($this->client->getResponse()->isSuccessful());
    }

    public function testPasswordResetPostFailPasswordsMismatch()
    {
        $this->createTestClient();
        $this->sendResetEmail();
        
        $user = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->find(1);
        $this->client->request(
            'POST',
            '/api/password/reset',
            array(
                'token' => $user->getPasswordResetToken(),
                'password' => 'newpass',
                'repeatPassword' => 'new'
            )
        );

        $this->assertFalse($this->client->getResponse()->isSuccessful());
    }
    
    public function testPasswordResetPostSuccess()
    {
        $this->createTestClient();
        $this->sendResetEmail();
        
        $this->markTestSkipped(
            'Fix problem with database update.'
        );

        $user = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->find(1);
        $this->client->request(
            'POST',
            '/api/password/reset',
            array(
                'token' => $user->getPasswordResetToken(),
                'password' => 'newpass',
                'repeatPassword' => 'newpass'
            )
        );
        
        $newPasswordUser = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->findAll();
        $newPasswordUser = $newPasswordUser[0];

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertNotEquals($user->getPassword(), $newPasswordUser->getPassword());
        $this->assertEmpty($newPasswordUser->getPasswordResetToken());
        $this->assertEmpty($newPasswordUser->getPasswordResetExpiration());
    }
    
    private function sendResetEmail()
    {
        $this->client->request(
            'POST',
            '/api/password/email/reset',
            array(
                'email' => "info@Webcook.com"
            )
        );
    }

    private function getMockMailer($return)
    {
        $mailer = $this->getMockBuilder('Swift_Mailer')
                ->disableOriginalConstructor()
                ->setMethods(array('send'))
                ->getMock();

        $mailer->expects($this->once())
            ->method('send')
            ->with($this->anything())
            ->will($this->returnValue($return));

        return $mailer;
    }
}
