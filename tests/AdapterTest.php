<?php

namespace Pop\Audit\Test;

use PHPUnit\Framework\TestCase;
use Pop\Audit;

class AdapterTest extends TestCase
{

    public function testSetAndGetUser()
    {
        $adapter = new Audit\Adapter\File(__DIR__ . '/tmp');
        $adapter->setUsername('admin');
        $this->assertEquals('admin', $adapter->getUsername());
    }

    public function testSetAndGetUserId()
    {
        $adapter = new Audit\Adapter\File(__DIR__ . '/tmp');
        $adapter->setUserId(1001);
        $this->assertEquals(1001, $adapter->getUserId());
    }

    public function testSetAndGetDomain()
    {
        $adapter = new Audit\Adapter\File(__DIR__ . '/tmp');
        $adapter->setDomain('app.localhost');
        $this->assertEquals('app.localhost', $adapter->getDomain());
    }

    public function testSetAndGetRoute()
    {
        $adapter = new Audit\Adapter\File(__DIR__ . '/tmp');
        $adapter->setRoute('/users/1');
        $this->assertEquals('/users/1', $adapter->getRoute());
    }

    public function testSetAndGetMethod()
    {
        $adapter = new Audit\Adapter\File(__DIR__ . '/tmp');
        $adapter->setMethod('PUT');
        $this->assertEquals('PUT', $adapter->getMethod());
    }

    public function testSetAndGetModel()
    {
        $adapter = new Audit\Adapter\File(__DIR__ . '/tmp');
        $adapter->setModel('MyApp\Model\User');
        $this->assertEquals('MyApp\Model\User', $adapter->getModel());
    }

    public function testSetAndGetModelId()
    {
        $adapter = new Audit\Adapter\File(__DIR__ . '/tmp');
        $adapter->setModelId(1001);
        $this->assertEquals(1001, $adapter->getModelId());
    }

    public function testGetAction()
    {
        $old = ['username' => 'admin'];
        $new = ['username' => 'admin2'];

        $adapter = new Audit\Adapter\File(__DIR__ . '/tmp');
        $adapter->resolveDiff($old, $new);
        $this->assertEquals('updated', $adapter->getAction());
    }

    public function testGetOriginalAndModified()
    {
        $old = ['username' => 'admin'];
        $new = ['username' => 'admin2'];

        $adapter = new Audit\Adapter\File(__DIR__ . '/tmp');
        $adapter->resolveDiff($old, $new);
        $original = $adapter->getOriginal();
        $modified = $adapter->getModified();

        $this->assertTrue($adapter->hasDiff());
        $this->assertEquals('admin', $original['username']);
        $this->assertEquals('admin2', $modified['username']);
    }

    public function testResolveDiffCreated()
    {
        $old = [];
        $new = ['username' => 'admin2'];

        $adapter = new Audit\Adapter\File(__DIR__ . '/tmp');
        $adapter->resolveDiff($old, $new);
        $this->assertEquals('created', $adapter->getAction());
    }

    public function testResolveDiffDeleted()
    {
        $old = ['username' => 'admin'];
        $new = [];

        $adapter = new Audit\Adapter\File(__DIR__ . '/tmp');
        $adapter->resolveDiff($old, $new);
        $this->assertEquals('deleted', $adapter->getAction());
    }

    public function testSetDiffCreated()
    {
        $old = [];
        $new = ['username' => 'admin2'];

        $adapter = new Audit\Adapter\File(__DIR__ . '/tmp');
        $adapter->setDiff($old, $new);
        $this->assertEquals('created', $adapter->getAction());
    }

    public function testSetDiffUpdated()
    {
        $old = ['username' => 'admin'];
        $new = ['username' => 'admin2'];

        $adapter = new Audit\Adapter\File(__DIR__ . '/tmp');
        $adapter->setDiff($old, $new);
        $this->assertEquals('updated', $adapter->getAction());
    }

    public function testSetDiffDeleted()
    {
        $old = ['username' => 'admin'];
        $new = [];

        $adapter = new Audit\Adapter\File(__DIR__ . '/tmp');
        $adapter->setDiff($old, $new);
        $this->assertEquals('deleted', $adapter->getAction());
    }
}