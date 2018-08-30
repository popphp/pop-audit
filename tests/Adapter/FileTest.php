<?php

namespace Pop\Audit\Test\Adapter;

use PHPUnit\Framework\TestCase;
use Pop\Audit\Adapter;

class FileTest extends TestCase
{

    public function testConstructor()
    {
        $adapter = new Adapter\File(__DIR__ . '/../tmp');
        $this->assertInstanceOf('Pop\Audit\Adapter\File', $adapter);
        $this->assertEquals(__DIR__ . '/../tmp', $adapter->getFolder());
        $this->assertEquals('pop-audit-', $adapter->getPrefix());
    }

    public function testConstructorException()
    {
        $this->expectException('Pop\Audit\Adapter\Exception');
        $adapter = new Adapter\File(__DIR__ . '/../bad-folder');
    }

    public function testDecode()
    {
        $adapter = new Adapter\File(__DIR__ . '/../tmp');
        $this->assertInstanceOf('Pop\Audit\Adapter\File', $adapter);
        $this->assertEquals(__DIR__ . '/../tmp', $adapter->getFolder());
    }

    public function testDecodeException()
    {
        $this->expectException('Pop\Audit\Adapter\Exception');
        $adapter = new Adapter\File(__DIR__ . '/../tmp');
        $data = $adapter->decode(__DIR__ . '/../tmp/bad-file.log');
    }

    public function testSend()
    {
        $old = ['username' => 'admin'];
        $new = ['username' => 'admin2'];

        $adapter  = new Adapter\File(__DIR__ . '/../tmp');
        $adapter->setModel('MyApp\Model\User')
            ->setModelId(1001);
        $adapter->resolveDiff($old, $new);
        $fileName = $adapter->send();

        $id = $adapter->getId($fileName);

        $this->assertFileExists(__DIR__ . '/../tmp/' . $fileName);
        $data = $adapter->decode($fileName);
        $this->assertNotEmpty($id);
        $this->assertEquals('admin', $data['old']['username']);
        $this->assertEquals('admin2', $data['new']['username']);

        $states = $adapter->getStates();
        $this->assertGreaterThanOrEqual(0, count($states));

        $states = $adapter->getStates('ASC', 1, 1);
        $this->assertGreaterThanOrEqual(0, count($states));

        $stateById = $adapter->getStateById($id);
        $stateById = $adapter->getStateById($id);
        $stateById = reset($stateById);
        $this->assertEquals('admin', $stateById['old']['username']);
        $this->assertEquals('admin2', $stateById['new']['username']);

        $stateByModel = $adapter->getStateByModel('MyApp\Model\User', 1001);
        $this->assertGreaterThanOrEqual(0, count($stateByModel));

        $stateByTs = $adapter->getStateByTimestamp(time() + 10, time() - 10);
        $stateByTs = reset($stateByTs);
        $this->assertGreaterThanOrEqual(0, count($stateByTs));

        $stateByDate = $adapter->getStateByDate(date('Y-m-d'), date('Y-m-d'));
        $stateByDate = reset($stateByDate);

        $preSnapshot  = $adapter->getSnapshot($id);
        $postSnapshot = $adapter->getSnapshot($id, true);

        $this->assertNotEquals($preSnapshot['username'], $postSnapshot['username']);

        if (file_exists(__DIR__ . '/../tmp/' . $fileName)) {
            unlink(__DIR__ . '/../tmp/' . $fileName);
        }
    }

    public function testSendException()
    {
        $this->expectException('Pop\Audit\Adapter\Exception');
        $adapter  = new Adapter\File(__DIR__ . '/../tmp');
        $adapter->send();
    }

    public function testSendModelException()
    {
        $this->expectException('Pop\Audit\Adapter\Exception');
        $old = ['username' => 'admin'];
        $new = ['username' => 'admin2'];

        $adapter  = new Adapter\File(__DIR__ . '/../tmp');
        $adapter->resolveDiff($old, $new);
        $adapter->send();
    }

    public function testGetStateModelException()
    {
        $this->expectException('Pop\Audit\Adapter\Exception');
        $adapter  = new Adapter\File(__DIR__ . '/../tmp');
        $adapter->getStateByModel('MyApp\Model\User');
    }

}