<?php

namespace Webcook\Cms\SecurityBundle\Tests\Controller;

class RoleControllerTest extends \Webcook\Cms\CoreBundle\Tests\BasicTestCase
{
    public function testGetRoles()
    {
        $this->createTestClient();
        $this->client->request('GET', '/api/roles.json');

        $roles = $this->client->getResponse()->getContent();

        var_dump($roles);
        
        $data = json_decode($roles, true);
        $this->assertCount(2, $data);
    }

    public function testGetRole()
    {
        $this->createTestClient();
        $this->client->request('GET', '/api/roles/1.json');
        $roles = $this->client->getResponse()->getContent();

        $data = json_decode($roles, true);

        $this->assertEquals(1, $data['id']);
        $this->assertEquals(1, $data['version']);
        $this->assertEquals('Administrator', $data['name']);
        $this->assertEquals('ROLE_ADMIN', $data['role']);
        $this->assertGreaterThan(2, $data['resources']);
    }

    public function testPost()
    {
        $this->jsonRequest(
            'POST',
            '/api/roles',
            array(
                'name' => 'Tester',
                'role' => 'ROLE_TEST',
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $roles = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\Role')->findAll();

        $this->assertCount(3, $roles);
        $this->assertEquals('Tester', $roles[2]->getName());
        $this->assertEquals('ROLE_TEST', $roles[2]->getRole());
        $this->assertGreaterThan(2, $roles[2]->getResources()->count());
    }

    public function testPut()
    {
        $crawler = $this->jsonRequest(
            'PUT',
            '/api/roles/2',
            array(
                'name' => 'Tester updated',
                'role' => 'ROLE_TESTER',
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $role = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\Role')->find(2);

        $this->assertEquals('Tester updated', $role->getName());
        $this->assertEquals('ROLE_TESTER', $role->getRole());
    }

    public function testDelete()
    {
        $this->createTestClient();

        $crawler = $this->client->request('DELETE', '/api/roles/2.json');

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $roles = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\Role')->findAll();

        $this->assertCount(1, $roles);
    }

    public function testWrongPost()
    {
        $this->createTestClient();

        $crawler = $this->jsonRequest(
            'POST',
            '/api/roles',
            array(
                'n' => 'Tester',
            )
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testPutNonExisting()
    {
        $this->jsonRequest(
            'PUT',
            '/api/roles/4',
            array(
                'name' => 'Tester putted',
                'role' => 'ROLE_TESTER_PUTTED',
            )
        );

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testWrongPut()
    {
        $this->createTestClient();
        $this->markTestSkipped();
        $crawler = $this->jsonRequest(
            'PUT',
            '/api/roles/1',
            array(
                'name' => 'Tester missing role field',
            )
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testPutRoleResources()
    {
        $this->createTestClient();

        $roleResource = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\RoleResource')->findAll();

        $this->assertEquals(array(
            'view' => true,
            'insert' => true,
            'edit' => true,
            'delete' => true,
            'resource' => 1,
            'role' => 1,
            'version' => 1,
            'id' => 1,
        ), $roleResource[0]->toArray());
        $this->assertEquals(array(
            'view' => true,
            'insert' => true,
            'edit' => true,
            'delete' => true,
            'resource' => 2,
            'role' => 1,
            'version' => 1,
            'id' => 2,
        ), $roleResource[1]->toArray());
        $this->assertEquals(array(
            'view' => true,
            'insert' => true,
            'edit' => true,
            'delete' => true,
            'resource' => 3,
            'role' => 1,
            'version' => 1,
            'id' => 3,
        ), $roleResource[2]->toArray());
        $this->em->detach($roleResource[0]);
        $this->em->detach($roleResource[1]);
        $this->em->detach($roleResource[2]);

        $crawler = $this->jsonRequest(
            'PUT',
            '/api/roles/1/resources',
            array(
                'roleResources' => array(
                    array('view' => 'true', 'edit' => 'true', 'delete' => 'false', 'insert' => false, 'id' => 1),
                    array('view' => 'true', 'edit' => 'false', 'delete' => 'true', 'insert' => true, 'id' => 2),
                    array('view' => 'false', 'edit' => 'false', 'delete' => 'false', 'insert' => false, 'id' => 3),
                    array('id' => 3),
                ),
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());

        $roleResource = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\RoleResource')->findAll();

        $this->assertEquals(array(
            'view' => true,
            'edit' => true,
            'delete' => false,
            'resource' => 1,
            'role' => 1,
            'version' => 2,
            'id' => 1,
            'insert' => false,
        ), $roleResource[0]->toArray());
        $this->assertEquals(array(
            'view' => true,
            'edit' => false,
            'delete' => true,
            'resource' => 2,
            'role' => 1,
            'version' => 2,
            'id' => 2,
            'insert' => true,
        ), $roleResource[1]->toArray());
        $this->assertEquals(array(
            'view' => false,
            'edit' => false,
            'delete' => false,
            'resource' => 3,
            'role' => 1,
            'version' => 2,
            'id' => 3,
            'insert' => false,
        ), $roleResource[2]->toArray());
    }
}
