<?php

namespace Webcook\Cms\SecurityBundle\Tests\Controller;

class ResourceControllerTest extends \Webcook\Cms\CoreBundle\Tests\BasicTestCase
{
    public function testGetResources()
    {
        $this->createTestClient();

        $this->client->request('GET', '/api/resources.json');
        $resources = $this->client->getResponse()->getContent();

        $data = json_decode($resources, true);
        $this->assertGreaterThan(2, $data);
    }

    public function testGetResource()
    {
        $this->createTestClient();
        $this->client->request('GET', '/api/resources/1.json');
        $users = $this->client->getResponse()->getContent();

        $data = json_decode($users, true);
        
        $this->assertEquals(1, $data['id']);
        $this->assertEquals(1, $data['version']);
        $this->assertContains(' - ', $data['name']);
    }

    public function testDelete()
    {
        $this->createTestClient();

        $old = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\Resource')->findAll();

        $this->client->request('DELETE', '/api/resources/1.json');

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $roles = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\Resource')->findAll();

        $this->assertEquals(count($roles), count($old) - 1);
    }

    public function testSynchronizeResources()
    {
        $this->markTestSkipped();
        $this->createTestClient();

        $resources = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\Resource')->findAll();

        $this->assertCount(4, $resources);

        //move test controller
        $source = __DIR__.'/../Temp/TestController.php';
        $dest = __DIR__.'/../../src/Webcook/Cms/SecurityBundle/Controller/TestController.php';
        copy($source, $dest);

        $this->client->request('POST', '/api/resources/synchronizes');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $resources = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\Resource')->findAll();
        
        $this->assertGreaterThan(5, $resources);
        unlink($dest);
    }

    public function testNotFound()
    {
        $this->createTestClient();
        $crawler = $this->client->request('GET', '/api/resources/30.json');

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }
}
