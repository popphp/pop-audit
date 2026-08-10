<?php

namespace Pop\Audit\Test\Adapter;

use PHPUnit\Framework\TestCase;
use Pop\Audit\Adapter;
use Pop\Http\Client;
use Pop\Http\Client\Handler\Mock;
use Pop\Http\Client\Response;

class HttpTest extends TestCase
{

    protected function mockClient(Response $response): Client
    {
        $handler = new Mock();
        $handler->queue($response);
        return new Client('http://audit.localhost/', $handler);
    }

    protected function jsonResponse(array $data): Response
    {
        return new Response([
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode($data),
        ]);
    }

    public function testConstructor()
    {
        $adapter = new Adapter\Http(new Client('http://audit.localhost/'), new Client('http://audit.localhost/'));
        $this->assertInstanceOf('Pop\Audit\Adapter\Http', $adapter);
        $this->assertInstanceOf('Pop\Http\Client', $adapter->getSendClient());
        $this->assertInstanceOf('Pop\Http\Client', $adapter->getFetchClient());
    }

    public function testHasFetchClient()
    {
        $adapter = new Adapter\Http(new Client('http://audit.localhost/'));
        $this->assertFalse($adapter->hasFetchClient());

        $adapter->setFetchClient(new Client('http://audit.localhost/'));
        $this->assertTrue($adapter->hasFetchClient());
    }

    public function testSend()
    {
        $old = ['username' => 'admin'];
        $new = ['username' => 'admin2'];

        $sendClient = $this->mockClient($this->jsonResponse(['result' => 'ok']));

        $adapter = new Adapter\Http($sendClient);
        $adapter->setModel('MyApp\Model\User');
        $adapter->setModelId(1001);
        $adapter->resolveDiff($old, $new);
        $response = $adapter->send();

        $this->assertInstanceOf('Pop\Http\Client\Response', $response);
        $this->assertEquals(['result' => 'ok'], $response->getParsedResponse());
    }

    public function testSendException()
    {
        $this->expectException('Pop\Audit\Adapter\Exception');
        $adapter = new Adapter\Http(new Client('http://audit.localhost/'));
        $adapter->send();
    }

    public function testSendModelException()
    {
        $this->expectException('Pop\Audit\Adapter\Exception');
        $old = ['username' => 'admin'];
        $new = ['username' => 'admin2'];

        $adapter = new Adapter\Http(new Client('http://audit.localhost/'));
        $adapter->resolveDiff($old, $new);
        $adapter->send();
    }

    public function testGetStates()
    {
        $fetchClient = $this->mockClient($this->jsonResponse([
            ['id' => 1, 'model' => 'MyApp\Model\User', 'model_id' => 1001],
        ]));

        $adapter = new Adapter\Http(new Client('http://audit.localhost/'), $fetchClient);
        $states  = $adapter->getStates(['filter' => ['model = MyApp\Model\User']]);

        $this->assertCount(1, $states);
        $this->assertEquals('MyApp\Model\User', $states[0]['model']);
    }

    public function testGetStatesWithNoFields()
    {
        $fetchClient = $this->mockClient($this->jsonResponse([
            ['id' => 1, 'model' => 'MyApp\Model\User', 'model_id' => 1001],
        ]));

        $adapter = new Adapter\Http(new Client('http://audit.localhost/'), $fetchClient);
        $states  = $adapter->getStates();

        $this->assertCount(1, $states);
    }

    public function testGetStateByIdDecodesOldAndNew()
    {
        $fetchClient = $this->mockClient($this->jsonResponse([
            'id'  => 1001,
            'old' => json_encode(['username' => 'admin']),
            'new' => json_encode(['username' => 'admin2']),
        ]));

        $adapter = new Adapter\Http(new Client('http://audit.localhost/'), $fetchClient);
        $state   = $adapter->getStateById(1001);

        $this->assertIsArray($state['old']);
        $this->assertIsArray($state['new']);
        $this->assertEquals('admin', $state['old']['username']);
        $this->assertEquals('admin2', $state['new']['username']);
    }

    public function testGetStateByIdLeavesNonJsonOldAndNewUntouched()
    {
        $fetchClient = $this->mockClient($this->jsonResponse([
            'id'  => 1001,
            'old' => 'not-json',
            'new' => 'still-not-json',
        ]));

        $adapter = new Adapter\Http(new Client('http://audit.localhost/'), $fetchClient);
        $state   = $adapter->getStateById(1001);

        $this->assertEquals('not-json', $state['old']);
        $this->assertEquals('still-not-json', $state['new']);
    }

    public function testGetStateByIdAsQueryAddsIdAsRequestData()
    {
        $handler = new Mock();
        $handler->queue($this->jsonResponse(['id' => 1001]));
        $fetchClient = new Client('http://audit.localhost/', $handler);

        $adapter = new Adapter\Http(new Client('http://audit.localhost/'), $fetchClient);
        $adapter->getStateById(1001, true);

        $request = $handler->getLastRequest();
        $this->assertEquals(1001, $request->getData()->getData('id'));
    }

    public function testGetStateByIdRestoresOriginalUri()
    {
        $fetchClient = $this->mockClient($this->jsonResponse(['id' => 1001]));
        $origUri     = $fetchClient->getRequest()->getUri()->getFullUri();

        $adapter = new Adapter\Http(new Client('http://audit.localhost/'), $fetchClient);
        $adapter->getStateById(1001);

        $this->assertEquals($origUri, $fetchClient->getRequest()->getUri()->getFullUri());
    }

    public function testGetStateByModel()
    {
        $fetchClient = $this->mockClient($this->jsonResponse([
            ['id' => 1, 'model' => 'MyApp\Model\User', 'model_id' => 1001],
        ]));

        $adapter = new Adapter\Http(new Client('http://audit.localhost/'), $fetchClient);
        $states  = $adapter->getStateByModel('MyApp\Model\User', 1001);

        $this->assertCount(1, $states);
        $this->assertEquals(1001, $states[0]['model_id']);
    }

    public function testGetStateByTimestamp()
    {
        $fetchClient = $this->mockClient($this->jsonResponse([
            ['id' => 1, 'timestamp' => date('Y-m-d H:i:s')],
        ]));

        $adapter = new Adapter\Http(new Client('http://audit.localhost/'), $fetchClient);
        $states  = $adapter->getStateByTimestamp(time(), time() - 1000);

        $this->assertCount(1, $states);
    }

    public function testGetStateByDate()
    {
        $fetchClient = $this->mockClient($this->jsonResponse([
            ['id' => 1, 'timestamp' => date('Y-m-d H:i:s')],
        ]));

        $adapter = new Adapter\Http(new Client('http://audit.localhost/'), $fetchClient);
        $states  = $adapter->getStateByDate(date('Y-m-d'), date('Y-m-d', strtotime('-1 day')));

        $this->assertCount(1, $states);
    }

    public function testGetSnapshotPreAndPost()
    {
        $row = [
            'id'  => 1001,
            'old' => json_encode(['username' => 'admin']),
            'new' => json_encode(['username' => 'admin2']),
        ];

        $handler = new Mock();
        $handler->queue($this->jsonResponse($row));
        $handler->queue($this->jsonResponse($row));
        $fetchClient = new Client('http://audit.localhost/', $handler);

        $adapter = new Adapter\Http(new Client('http://audit.localhost/'), $fetchClient);

        $preSnapshot  = $adapter->getSnapshot(1001);
        $postSnapshot = $adapter->getSnapshot(1001, true);

        $this->assertEquals('admin', $preSnapshot['username']);
        $this->assertEquals('admin2', $postSnapshot['username']);
        $this->assertNotEquals($preSnapshot['username'], $postSnapshot['username']);
    }

}
