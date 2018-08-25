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
        $adapter->resolveDiff($old, $new);
        $fileName = $adapter->send();

        $this->assertFileExists(__DIR__ . '/../tmp/' . $fileName);
        $data = $adapter->decode($fileName);
        $this->assertEquals('admin', $data['old']['username']);
        $this->assertEquals('admin2', $data['new']['username']);
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

}