<?php

namespace Webcook\Cms\SecurityBundle\Tests\Entity;

use Webcook\Cms\SecurityBundle\Entity\RoleResource;
use Webcook\Cms\SecurityBundle\Authorization\Voter\WebcookCmsVoter;

class RoleResourceTest extends \Webcook\Cms\CommonBundle\Tests\BasicTestCase
{
    public function testPersist()
    {
        $this->loadFixtures(
            array(
                'Webcook\Cms\SecurityBundle\DataFixtures\ORM\LoadResourcesData',
                'Webcook\Cms\SecurityBundle\DataFixtures\ORM\LoadRoleData',
            )
        );

        $roleResource = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\RoleResource')->find(1);

        $this->assertSame(array(
            'view' => true,
            'insert' => true,
            'edit' => true,
            'delete' => true,
            'resource' => 1,
            'role' => 1,
            'id' => 1,
            'version' => 1,
        ), $roleResource->toArray());
        $this->assertTrue($roleResource->isGranted(WebcookCmsVoter::ACTION_VIEW));
        $this->assertTrue($roleResource->isGranted(WebcookCmsVoter::ACTION_EDIT));
        $this->assertTrue($roleResource->isGranted(WebcookCmsVoter::ACTION_DELETE));
        $this->assertFalse($roleResource->isGranted('Whatever else should return false.'));
    }
}
