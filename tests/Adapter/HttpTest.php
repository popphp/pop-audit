<?php

namespace Pop\Audit\Test\Adapter;

use PHPUnit\Framework\TestCase;
use Pop\Audit\Adapter;
use Pop\Http\Client\Stream;

class HttpTest extends TestCase
{

    public function testConstructor()
    {
        $adapter = new Adapter\Http(new Stream('http://localhost/'));
        $this->assertInstanceOf('Pop\Audit\Adapter\Http', $adapter);
        $this->assertInstanceOf('Pop\Http\Client\Stream', $adapter->getStream());
    }

    public function testSend()
    {
        $old = ['username' => 'admin'];
        $new = ['username' => 'admin2'];

        $adapter = new Adapter\Http(new Stream('http://localhost/'));
        $adapter->setModel('MyApp\Model\User');
        $adapter->setModelId(1001);
        $adapter->resolveDiff($old, $new);
        $result = $adapter->send();

        $this->assertInstanceOf('Pop\Http\Client\Stream', $result);
    }

    public function testSendException()
    {
        $this->expectException('Pop\Audit\Adapter\Exception');
        $adapter = new Adapter\Http(new Stream('http://localhost/'));
        $adapter->send();
    }

}