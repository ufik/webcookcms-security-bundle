<?php

namespace Webcook\Cms\SecurityBundle\Tests\Entity;

use Webcook\Cms\SecurityBundle\Entity\User;
use Webcook\Cms\SecurityBundle\Entity\Setting;
use \Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class SettingTest extends \Webcook\Cms\CoreBundle\Tests\BasicTestCase
{
    public function testPersist()
    {
        $this->loadFixtures(array(
            'Webcook\Cms\SecurityBundle\DataFixtures\ORM\LoadUserData',
        ));

        $settings = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\Setting')->findAll();

        $this->assertCount(6, $settings);

        $this->assertEquals("Timezone", $settings[0]->getName());
        $this->assertEquals("timezone", $settings[0]->getKey());
        $this->assertEquals("general", $settings[0]->getSection());
        $this->assertEquals("GMT", $settings[0]->getValue());

    }

    public function testRefresh()
    {
        $this->loadFixtures(array(
            'Webcook\Cms\SecurityBundle\DataFixtures\ORM\LoadUserData'
        ));

        $setting = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\Setting')->find(1);
        $setting->setName('Launguage');

        $this->assertEquals('Launguage', $setting->getName());

    }
}
