<?php

namespace Pop\Audit\Test;

use PHPUnit\Framework\TestCase;
use Pop\Audit;
use Pop\Audit\Test\Assets\User;

class ModelTest extends TestCase
{

    public function testConstructor()
    {
        $user = new User();
        $this->assertInstanceOf('Pop\Audit\Model\AuditableModel', $user);
        $this->assertInstanceOf('Pop\Audit\Model\AuditableInterface', $user);
    }

    public function testMethods()
    {
        $user = new User();
        $user->setAuditor(new Audit\Auditor(new Audit\Adapter\File(__DIR__ . '/tmp')));
        $this->assertTrue($user->hasAuditor());
        $this->assertTrue($user->isAuditable());
        $this->assertInstanceOf('Pop\Audit\Auditor', $user->getAuditor());
    }

}