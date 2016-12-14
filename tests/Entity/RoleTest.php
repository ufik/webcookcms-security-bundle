<?php

namespace Webcook\Cms\SecurityBundle\Tests\Entity;

use Webcook\Cms\SecurityBundle\Entity\Role;

class RoleTest extends \Webcook\Cms\CommonBundle\Tests\BasicTestCase
{
    public function testPersist()
    {
        $this->loadFixtures(array(
            'Webcook\Cms\SecurityBundle\DataFixtures\ORM\LoadRoleData',
            'Webcook\Cms\SecurityBundle\DataFixtures\ORM\LoadUserData',
            'Webcook\Cms\SecurityBundle\DataFixtures\ORM\LoadResourcesData',
        ));

        $roles = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\Role')->findAll();
        $resource = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\Resource')->find(1);

        $resources = array();
        foreach ($roles[0]->getResources() as $r) {
        	$resources[] = $r->toArray();
        }

        $this->assertCount(2, $roles);
        $this->assertEquals('Administrator', $roles[0]->getName());
        $this->assertEquals('ROLE_ADMIN', $roles[0]->getRole());
        $this->assertGreaterThan(2, $roles[0]->getResources()->count());
        $this->assertTrue($roles[0]->getResource('Security - Resource')->getView());
        $this->assertTrue($roles[0]->getResource('Security - Resource')->getInsert());
        $this->assertTrue($roles[0]->getResource('Security - Resource')->getEdit());
        $this->assertTrue($roles[0]->getResource('Security - Resource')->getDelete());
        $this->assertFalse($roles[0]->getResource('Non existing resource should return false.'));

        $testArray = array('test');

        $role = new Role();
        $role->setResources($testArray);

        $this->assertSame($testArray, $role->getResources());
    }
}
