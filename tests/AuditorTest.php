<?php

namespace Pop\Audit\Test;

use PHPUnit\Framework\TestCase;
use Pop\Audit;

class AuditorTest extends TestCase
{

    public function testConstructor()
    {
        $auditor = new Audit\Auditor(new Audit\Adapter\File(__DIR__ . '/tmp'));
        $this->assertInstanceOf('Pop\Audit\Auditor', $auditor);
        $this->assertInstanceOf('Pop\Audit\Adapter\File', $auditor->adapter());
    }

    public function testSetAndGetModel()
    {
        $auditor = new Audit\Auditor(new Audit\Adapter\File(__DIR__ . '/tmp'));
        $auditor->setModel('MyApp\Model\User', 1001);
        $this->assertEquals('MyApp\Model\User', $auditor->adapter()->getModel());
        $this->assertEquals(1001, $auditor->adapter()->getModelId());
    }

    public function testSetAndGetUser()
    {
        $auditor = new Audit\Auditor(new Audit\Adapter\File(__DIR__ . '/tmp'));
        $auditor->setUser('admin', 1001);
        $this->assertEquals('admin', $auditor->adapter()->getUsername());
        $this->assertEquals(1001, $auditor->adapter()->getUserId());
    }

    public function testSetAndGetDomain()
    {
        $auditor = new Audit\Auditor(new Audit\Adapter\File(__DIR__ . '/tmp'));
        $auditor->setDomain('app.localhost');
        $this->assertEquals('app.localhost', $auditor->adapter()->getDomain());
    }

    public function testMetadata()
    {
        $auditor = new Audit\Auditor(new Audit\Adapter\File(__DIR__ . '/tmp'));
        $auditor->setMetadata(['foo' => 'bar']);
        $auditor->addMetadata('baz', 123);
        $this->assertTrue($auditor->adapter()->hasMetadata());
        $this->assertTrue($auditor->adapter()->hasMetadata('foo'));
        $this->assertEquals(2, count($auditor->adapter()->getMetadata()));
        $this->assertEquals('bar', $auditor->adapter()->getMetadata('foo'));
    }

    public function testSetDiff()
    {
        $old = ['username' => 'admin'];
        $new = ['username' => 'admin2'];

        $auditor = new Audit\Auditor(new Audit\Adapter\File(__DIR__ . '/tmp'));
        $auditor->setDiff($old, $new);

        $this->assertTrue($auditor->hasDiff());
    }

    public function testResolveDiff()
    {
        $old = ['username' => 'admin'];
        $new = ['username' => 'admin2'];

        $auditor = new Audit\Auditor(new Audit\Adapter\File(__DIR__ . '/tmp'));
        $auditor->resolveDiff($old, $new);

        $this->assertTrue($auditor->hasDiff());
    }

    public function testSend()
    {
        $old = ['username' => 'admin'];
        $new = ['username' => 'admin2'];

        $auditor  = new Audit\Auditor(new Audit\Adapter\File(__DIR__ . '/tmp'));
        $fileName = $auditor->send($old, $new);

        $this->assertFileExists(__DIR__ . '/tmp/' . $fileName);
        unlink(__DIR__ . '/tmp/' . $fileName);
    }

}