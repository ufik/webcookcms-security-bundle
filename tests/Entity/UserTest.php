<?php

namespace Webcook\Cms\SecurityBundle\Tests\Entity;

use Webcook\Cms\SecurityBundle\Entity\User;
use Webcook\Cms\SecurityBundle\Entity\Role;
use Webcook\Cms\SecurityBundle\Entity\Setting;
use \Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserTest extends \Webcook\Cms\CommonBundle\Tests\BasicTestCase
{
    public function testPersist()
    {
        $this->loadFixtures(array(
            'Webcook\Cms\SecurityBundle\DataFixtures\ORM\LoadRoleData',
            'Webcook\Cms\SecurityBundle\DataFixtures\ORM\LoadUserData',           
        ));

        $users = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->findAll();
        $role = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\Role')->find(1);
        
        $this->assertCount(3, $users);
        $this->assertCount(1, $users[0]->getRoles());
        $this->assertCount(2, $users[0]->getSettings());

        $this->assertEquals('admin', $users[0]->getUsername());
        $this->assertEquals(1, $users[0]->getVersion());
        $this->assertEquals('xSr7r5rEPjstSyTI0LzlvqdHAtMeq4sYvpKjywW4r+k=', $users[0]->getPassword());
        $this->assertEquals('info@Webcook.com', $users[0]->getEmail());
        $this->assertTrue($users[0]->getIsActive());

        $users[0]->removeRole($role);

        $this->em->flush();

        $users = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->findAll();

        $this->assertCount(0, $users[0]->getRoles());

        $users[0]->addRole($role);
        $this->em->flush();

        $users = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->findAll();

        $this->assertCount(1, $users[0]->getRoles());

        $users[0]->removeRoles();

        $users = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->findAll();

        $this->assertCount(0, $users[0]->getRoles());

        $users[0]->eraseCredentials();
        $serialized = $users[0]->serialize();
        $newUser = new \Webcook\Cms\SecurityBundle\Entity\User();
        $newUser->unserialize($serialized);

        $user = $users[0]->toArray();
        $newUser = $newUser->toArray();

        $this->assertEquals($user['username'], $newUser['username']);
        $this->assertEquals($user['password'], $newUser['password']);
        $this->assertEquals($user['email'], $newUser['email']);
        $this->assertEquals($user['roles'], $newUser['roles']);
        $this->assertEquals($user['isActive'], $newUser['isActive']);
        $this->assertEquals($user['id'], $newUser['id']);
        $this->assertEquals($user['version'], $newUser['version']);
    }

    public function testRefresh()
    {
        $this->loadFixtures(array(
            'Webcook\Cms\SecurityBundle\DataFixtures\ORM\LoadRoleData',
            'Webcook\Cms\SecurityBundle\DataFixtures\ORM\LoadUserData',
        ));

        $user = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->find(1);
        $user->setUsername('changed');

        $this->assertEquals('changed', $user->getUsername());

        $user = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->refreshUser($user);

        $this->assertEquals('changed', $user->getUsername());
    }

    public function testLoad()
    {
        $this->loadFixtures(array(
            'Webcook\Cms\SecurityBundle\DataFixtures\ORM\LoadRoleData',
            'Webcook\Cms\SecurityBundle\DataFixtures\ORM\LoadUserData',
        ));

        try {
            $user = $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->loadUserByUsername('admin');
        } catch (UsernameNotFoundException $e) {
            $this->assertFalse(true, 'This user should exist.');
        }

        $assertion = false;
        try {
            $this->em->getRepository('Webcook\Cms\SecurityBundle\Entity\User')->loadUserByUsername('non existing user');
        } catch (UsernameNotFoundException $e) {
            $assertion = true;
        }

        $this->assertTrue($assertion);
    }
}
