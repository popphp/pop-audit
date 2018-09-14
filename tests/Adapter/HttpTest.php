<?php

namespace Pop\Audit\Test\Adapter;

use PHPUnit\Framework\TestCase;
use Pop\Audit\Adapter;
use Pop\Http\Client\Stream;

class HttpTest extends TestCase
{

    public function testConstructor()
    {
        $adapter = new Adapter\Http(new Stream('http://localhost/'), new Stream('http://localhost/'));
        $this->assertInstanceOf('Pop\Audit\Adapter\Http', $adapter);
        $this->assertInstanceOf('Pop\Http\Client\Stream', $adapter->getSendStream());
        $this->assertInstanceOf('Pop\Http\Client\Stream', $adapter->getFetchStream());
    }

    public function testSend()
    {
        $old = ['username' => 'admin'];
        $new = ['username' => 'admin2'];

        $adapter = new Adapter\Http(new Stream('http://localhost/'), new Stream('http://localhost/'));
        $adapter->setModel('MyApp\Model\User');
        $adapter->setModelId(1001);
        $adapter->resolveDiff($old, $new);
        $result = $adapter->send();

        $states = $adapter->getStates(['filter' => 'timestamp >=' . date('Y-m-d')]);
        $this->assertNull($states);

        $state = $adapter->getStateById(null);
        $this->assertNull($state);

        $states = $adapter->getStateByModel('MyApp\Model\User', 1001);
        $this->assertNull($states);

        $states = $adapter->getStateByTimestamp(time(), time() - 1000);
        $this->assertNull($states);

        $states = $adapter->getStateByDate(date('Y-m-d'), date('Y-m-d', time() - 1000));
        $this->assertNull($states);

        $snapshot = $adapter->getSnapshot(null);
        $this->assertEquals(0, count($snapshot));

        $this->assertTrue($adapter->hasFetchStream());

        $this->assertInstanceOf('Pop\Http\Client\Stream', $result);
    }

    public function testSendException()
    {
        $this->expectException('Pop\Audit\Adapter\Exception');
        $adapter = new Adapter\Http(new Stream('http://localhost/'));
        $adapter->send();
    }

    public function testSendModelException()
    {
        $this->expectException('Pop\Audit\Adapter\Exception');
        $old = ['username' => 'admin'];
        $new = ['username' => 'admin2'];

        $adapter = new Adapter\Http(new Stream('http://localhost/'));
        $adapter->resolveDiff($old, $new);
        $adapter->send();
    }

}