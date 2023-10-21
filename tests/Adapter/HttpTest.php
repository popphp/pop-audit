<?php

namespace Pop\Audit\Test\Adapter;

use PHPUnit\Framework\TestCase;
use Pop\Audit\Adapter;
use Pop\Http\Client;

class HttpTest extends TestCase
{

    public function testConstructor()
    {
        $adapter = new Adapter\Http(new Client('http://localhost/'), new Client('http://localhost/'));
        $this->assertInstanceOf('Pop\Audit\Adapter\Http', $adapter);
        $this->assertInstanceOf('Pop\Http\Client', $adapter->getSendClient());
        $this->assertInstanceOf('Pop\Http\Client', $adapter->getFetchClient());
    }

    public function testSend()
    {
        $old = ['username' => 'admin'];
        $new = ['username' => 'admin2'];

        $adapter = new Adapter\Http(new Client('http://localhost/'), new Client('http://localhost/'));
        $adapter->setModel('MyApp\Model\User');
        $adapter->setModelId(1001);
        $adapter->resolveDiff($old, $new);
        $result = $adapter->send();

        $states = $adapter->getStates(['filter' => 'timestamp >=' . date('Y-m-d')]);
        $this->assertNotNull($states);

        $states = $adapter->getStateById(1001);
        $this->assertNotNull($states);

        $states = $adapter->getStateByModel('MyApp\Model\User', 1001);
        $this->assertNotNull($states);

        $states = $adapter->getStateByTimestamp(time(), time() - 1000);
        $this->assertNotNull($states);

        $states = $adapter->getStateByDate(date('Y-m-d'), date('Y-m-d', time() - 1000));
        $this->assertNotNull($states);

        $states = $adapter->getSnapshot(1001);
        $this->assertNotNull($states);

        $this->assertTrue($adapter->hasFetchClient());

        $this->assertInstanceOf('Pop\Http\Client\Response', $adapter->getFetchClient()->getResponse());
    }

    public function testSendException()
    {
        $this->expectException('Pop\Audit\Adapter\Exception');
        $adapter = new Adapter\Http(new Client('http://localhost/'));
        $adapter->send();
    }

    public function testSendModelException()
    {
        $this->expectException('Pop\Audit\Adapter\Exception');
        $old = ['username' => 'admin'];
        $new = ['username' => 'admin2'];

        $adapter = new Adapter\Http(new Client('http://localhost/'));
        $adapter->resolveDiff($old, $new);
        $adapter->send();
    }

}