<?php

namespace Bundle\DoctrineUserBundle\Tests\Validation;

use Bundle\DoctrineUserBundle\Test\WebTestCase;

class UserValidationTest extends WebTestCase
{
    public function testBlankUsernameFail()
    {
        $user = $this->getService('doctrine_user.repository.user')->createUserInstance();
        $violations = $this->getService('validator')->validate($user, 'Registration');
        $this->assertTrue($this->hasViolationForPropertyPath($violations, 'username'));
    }

    public function testGoodUsernameValid()
    {
        $user = $this->getService('doctrine_user.repository.user')->createUserInstance();
        $user->setUsername(uniqid());
        $violations = $this->getService('validator')->validate($user, 'Registration');
        $this->assertFalse($this->hasViolationForPropertyPath($violations, 'username'));
    }

    public function testDuplicatedUsernameFail()
    {
        $username = uniqid();
        $repo = $this->getService('doctrine_user.repository.user');
        $om = $repo->getObjectManager();
        $validator = $this->getService('validator');
        $userClass = $repo->getObjectClass();
        $user1 = $repo->createUserInstance();
        $user1->setUsername($username);
        $user1->setEmail(uniqid().'@mail.org');
        $user1->setPlainPassword(uniqid());
        //$this->markTestSkipped();
        $violations = $this->getService('validator')->validate($user1, 'Registration');
        $this->assertFalse($this->hasViolationForPropertyPath($violations, 'username'));
        $om->persist($user1);
        $om->flush();
        $user2 = $repo->createUserInstance();
        $user2->setUsername($username);
        $user1->setEmail(uniqid().'@mail.org');
        $user1->setPlainPassword(uniqid());
        $violations = $this->getService('validator')->validate($user2, 'Registration');
        $this->assertTrue($this->hasViolationForPropertyPath($violations, 'username'));
        $om->remove($user1);
        $om->flush();
    }

    public function testDuplicatedEmailFail()
    {
        $email = uniqid().'@email.org';
        $repo = $this->getService('doctrine_user.repository.user');
        $om = $repo->getObjectManager();
        $validator = $this->getService('validator');
        $userClass = $repo->getObjectClass();
        $user1 = $repo->createUserInstance();
        $user1->setUsername(uniqid());
        $user1->setPlainPassword(uniqid());
        $user1->setEmail($email);
        $violations = $this->getService('validator')->validate($user1, 'Registration');
        $this->assertFalse($this->hasViolationForPropertyPath($violations, 'email'));
        $om->persist($user1);
        $om->flush();
        $user2 = $repo->createUserInstance();
        $user2->setUsername(uniqid());
        $user2->setPlainPassword(uniqid());
        $user2->setEmail($email);
        $violations = $this->getService('validator')->validate($user2, 'Registration');
        $this->assertTrue($this->hasViolationForPropertyPath($violations, 'email'));
        $om->remove($user1);
        $om->flush();
    }

    protected function hasViolationForPropertyPath($violations, $propertyPath)
    {
        if (!is_object($violations)) {
            return false;
        }

        foreach ($violations as $violation) {
            if ($violation->getPropertyPath() == $propertyPath) {
                return true;
            }
        }

        return false;
    }
}
