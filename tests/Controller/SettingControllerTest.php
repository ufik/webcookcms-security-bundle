<?php


namespace Webcook\Cms\SecurityBundle\Tests\Controller;

class SettingControllerTest extends \Webcook\Cms\CoreBundle\Tests\BasicTestCase
{
    public function testGetSettings()
    {   
        $this->loadData();
        $this->createTestClient();

        $this->client->request('GET', '/api/settings');

        $settings = $this->client->getResponse()->getContent();

        $data = json_decode($settings, true);
        $this->assertCount(6, $data);
    }
    
    public function testGetSetting()
    {
        $this->loadData();
        $this->createTestClient();

        $this->client->request('GET', '/api/settings/1');

        $setting = $this->client->getResponse()->getContent();

        $data = json_decode($setting, true);
        $this->assertEquals('Timezone', $data['name']);
    }
    
    public function testPostSettings()
    {
        $this->loadData();
        $this->createTestClient();

        $crawler = $this->client->request(
            'POST',
            '/api/settings',
            array(
                'setting' => array(
                    'name' => 'test',
                    'key' => 'test',
                    'section' => 'angular-WebcookCms-app',
                    'value' => 'test',
                ),
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $setting = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\Setting')->find(7);

        $this->assertEquals('test', $setting->getName());
        $this->assertEquals('test', $setting->getKey());
        $this->assertEquals('angular-WebcookCms-app', $setting->getSection());
        $this->assertEquals('test', $setting->getValue());
    }
   
    public function testPutSetting()
    {
        $this->loadData();
        $this->createTestClient();

        $crawler = $this->client->request(
            'PUT',
            '/api/settings/2',
            array(
                'setting' => array(
                    'name' => 'language',
                    'key' => 'language',
                    'section' => 'angular-WebcookCms-app',
                    'value' => 'test',                    
                ),
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $setting = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\Setting')->find(2);

        $this->assertEquals('language', $setting->getName());
        $this->assertEquals('language', $setting->getKey());
        $this->assertEquals('angular-WebcookCms-app', $setting->getSection());
        $this->assertEquals('test', $setting->getValue());
    }
     
    public function testWrongPutAgent()
    {
        $this->loadData();
        $this->createTestClient();

        $crawler = $this->client->request(
            'PUT',
            '/api/settings/1',
            array(
                'setting' => array(
                    'name' => 'Timezone - updated',
                    'key' => 'timezone',
                    'section' => 'angular-WebcookCms-app',
                    'Ttest' => 'Euro',
                ),
            )
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

   
   /* public function testNotFoundPutAgent()
    {
        $this->loadData();
        $this->createTestClient();

        $crawler = $this->client->request(
            'PUT',
            '/api/settings/999',
            array(
                'setting' => array(
                    'name' => 'New setting - updated',
                    'key' => 'test key',
                    'value' => 'test Value',
                    'section' => 'test section',                   
                ),
            )
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }*/
    
    public function testWrongPostSetting()
    {
        $this->loadData();
        $this->createTestClient();

        $crawler = $this->client->request(
            'POST',
            '/api/settings',
            array(
                'setting' => array(
                    'name' => 'New Setting',
                ),
            )
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }
   
    public function getNonExistingSetting()
    {
        $this->client->request('GET', '/api/settings/11');

        $this->assertTrue(404, $this->client->getResponse()->getStatusCode());
    }
    
    private function loadData()
    {
        $this->loadFixtures(array(
            'Webcook\Cms\SecurityBundle\DataFixtures\ORM\LoadUserData'
        ));
    }
    
}
