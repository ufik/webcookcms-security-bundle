<?php

namespace Webcook\Cms\SecurityBundle\Tests\Controller;

class UserControllerTest extends \Webcook\Cms\CommonBundle\Tests\BasicTestCase
{
    const TEST_PASS_HASH = 'xSr7r5rEPjstSyTI0LzlvqdHAtMeq4sYvpKjywW4r+k=';

    public function testGetUsers()
    {
        $this->createTestClient();
        $this->client->request('GET', '/api/users');
        $users = $this->client->getResponse()->getContent();

        $data = json_decode($users, true);
        $this->assertCount(3, $data);
    }

    public function testGetUser()
    {
        $this->createTestClient();

        $this->client->request('GET', '/api/users/1');
        $users = $this->client->getResponse()->getContent();

        $data = json_decode($users, true);
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('admin', $data['username']);
    }

    public function testPost()
    {
        $this->createTestClient();

        $crawler = $this->client->request(
            'POST',
            '/api/users',
            array(
                'user' => array(
                    'username' => 'New user',
                    'email' => 'new@Webcook.com',
                    'password' => array(
                        'first' => 'test',
                        'second' => 'test'
                     ),
                    'isActive' => false,
                    'roles' => array(1, 2),
                ),
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $user = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->find(4);

        $this->assertEquals('New user', $user->getUsername());
        $this->assertEquals(self::TEST_PASS_HASH, $user->getPassword());
        $this->assertEquals('new@Webcook.com', $user->getEmail());
        $this->assertFalse($user->getIsActive());
        $this->assertCount(2, $user->getRoles());
    }

    public function testPutUpdate()
    {
        $this->createTestClient();

        $this->client->request('GET', '/api/users/1'); // save version into session
        $crawler = $this->client->request(
            'PUT',
            '/api/users/1',
            array(
                'user' => array(
                    'username' => 'New user',
                    'email' => 'new@Webcook.com',
                    'password' => array(
                        'first' => '',
                        'second' => ''
                     ),
                    'isActive' => false,
                    'roles' => array(1, 2),
                ),
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $user = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->find(1);

        $this->assertEquals('New user', $user->getUsername());
        $this->assertEquals(self::TEST_PASS_HASH, $user->getPassword());
        $this->assertEquals('new@Webcook.com', $user->getEmail());
        $this->assertFalse($user->getIsActive());
        $this->assertCount(2, $user->getRoles());
    }

    public function testPutNew()
    {
        $this->createTestClient();

        $crawler = $this->client->request(
            'PUT',
            '/api/users/2',
            array(
                'user' => array(
                    'username' => 'New user',
                    'email' => 'new@Webcook.com',
                    'password' => array(
                        'first' => 'test',
                        'second' => 'test'
                     ),
                    'isActive' => false,
                    'roles' => array(1, 2),
                ),
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $user = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->find(2);

        $this->assertEquals('New user', $user->getUsername());
        $this->assertEquals(self::TEST_PASS_HASH, $user->getPassword());
        $this->assertEquals('new@Webcook.com', $user->getEmail());
        $this->assertFalse($user->getIsActive());
        $this->assertCount(2, $user->getRoles());
    }

    public function testDelete()
    {
        $this->createTestClient();

        $crawler = $this->client->request('DELETE', '/api/users/1');

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $users = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->findAll();

        $this->assertCount(2, $users);
    }

    public function testWrongPost()
    {
        $this->createTestClient();

        $crawler = $this->client->request(
            'POST',
            '/api/users',
            array(
                'user' => array(
                    'n' => 'Tester',
                ),
            )
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testPutNonExisting()
    {
        $this->createTestClient();

        $crawler = $this->client->request(
            'PUT',
            '/api/users/3',
            array(
                'user' => array(
                    'username' => 'Putted user',
                    'email' => 'new@Webcook.com',
                    'password' => array(
                        'first' => 'test',
                        'second' => 'test'
                     ),
                    'isActive' => false,
                    'roles' => array(1, 2),
                ),
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $users = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->findAll();
        $user = $users[2];

        $this->assertCount(3, $users);
        $this->assertEquals('Putted user', $user->getUsername());
        $this->assertEquals(self::TEST_PASS_HASH, $user->getPassword());
        $this->assertEquals('new@Webcook.com', $user->getEmail());
        $this->assertFalse($user->getIsActive());
        $this->assertCount(2, $user->getRoles());
    }

    public function testWrongPut()
    {
        $this->createTestClient();

        $crawler = $this->client->request(
            'PUT',
            '/api/users/1',
            array(
                'user' => array(
                    'name' => 'Tester missing role field',
                ),
            )
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testGetActiveUser()
    {
        $this->createTestClient();
        $this->client->request('GET', '/api/user/active');
        $content = $this->client->getResponse()->getContent();

        $this->assertContains('"id":1,"version":1,"username":"admin"', $content);
    }

    public function testGetSecretKey()
    {
        $this->createTestClient();

        $this->client->request('GET', '/api/users/1/key');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertContains('google_authenticator_secret', $this->client->getResponse()->getContent());
    }

    public function testGetQrCode()
    {
        $this->createTestClient();

        $this->client->request('GET', '/api/users/1/qrcode');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertContains('qrcode', $this->client->getResponse()->getContent());
    }

    public function testDeleteSecretKey()
    {
        $this->createTestClient();

        $this->client->request('DELETE', '/api/users/1/key');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertContains('deleted', $this->client->getResponse()->getContent());
    }
}
