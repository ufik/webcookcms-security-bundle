<?php

namespace Webcook\Cms\SecurityBundle\Tests\Controller;

class UserControllerTest extends \Webcook\Cms\CoreBundle\Tests\BasicTestCase
{
    const TEST_PASS_HASH = 'xSr7r5rEPjstSyTI0LzlvqdHAtMeq4sYvpKjywW4r+k=';

    public function testGetUsers()
    {
        $this->createTestClient();
        $this->client->request('GET', '/api/users.json');
        $users = $this->client->getResponse()->getContent();

        $data = json_decode($users, true);
        $this->assertCount(3, $data);
    }

    public function testGetUser()
    {
        $this->createTestClient();

        $this->client->request('GET', '/api/users/1.json');
        $users = $this->client->getResponse()->getContent();

        $data = json_decode($users, true);
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('admin', $data['username']);
    }

    public function testPost()
    {
        $crawler = $this->jsonRequest(
            'POST',
            '/api/users',
            array(
                'username' => 'New user',
                'email' => 'new@Webcook.com',
                'password' => 'test',
                'isActive' => false,
                'roles' => array('/api/roles/1', '/api/roles/2'),
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
        $this->jsonRequest(
            'PUT',
            '/api/users/1',
            array(
                'username' => 'New user',
                'email' => 'new@Webcook.com',
                'isActive' => false,
                'roles' => array('/api/roles/1', '/api/roles/2')
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

    public function testDelete()
    {
        $this->createTestClient();

        $crawler = $this->client->request('DELETE', '/api/users/1.json');

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $users = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->findAll();

        $this->assertCount(2, $users);
    }

    public function testWrongPostUser()
    {
        $this->createTestClient();

        $crawler = $this->jsonRequest(
            'POST',
            '/api/users',
            array(
                'n' => 'Tester'
            )
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testPutNonExisting()
    {
        $crawler = $this->jsonRequest(
            'PUT',
            '/api/users/4',
            array(
                    'username' => 'Putted user',
                    'email' => 'new@Webcook.com',
                    'password' => 'test',
                    'isActive' => false,
                    'roles' => array('/api/roles/1', '/api/roles/2'),
            )
        );

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testWrongPut()
    {
        $crawler = $this->jsonRequest(
            'PUT',
            '/api/users/1',
            array(
                'name' => 'Tester missing role field',
            )
        );

        $this->markTestSkipped();
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testGetActiveUser()
    {
        $this->markTestSkipped(); // this is not necessary maybe, just use GET of the user endpoint
        $this->createTestClient();
        $this->client->request('GET', '/api/user/active');
        $content = $this->client->getResponse()->getContent();

        $content = json_decode($content);
        $this->assertEquals('user', $content['username']);
    }
}
