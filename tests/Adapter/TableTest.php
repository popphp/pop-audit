<?php

namespace Pop\Audit\Test\Adapter;

use PHPUnit\Framework\TestCase;
use Pop\Audit\Adapter;
use Pop\Audit\Test\Assets\AuditLog;

class TableTest extends TestCase
{

    public function testConstructor()
    {
        copy(__DIR__ . '/../tmp/auditor.sqlite.orig', __DIR__ . '/../tmp/auditor.sqlite');
        chmod(__DIR__ . '/../tmp/auditor.sqlite', 0777);

        AuditLog::setDb(\Pop\Db\Db::sqliteConnect([
            'database' => __DIR__ . '/../tmp/auditor.sqlite'
        ]));

        $adapter = new Adapter\Table('Pop\Audit\Test\Assets\AuditLog');
        $this->assertInstanceOf('Pop\Audit\Adapter\Table', $adapter);
        $this->assertEquals('Pop\Audit\Test\Assets\AuditLog', $adapter->getTable());
    }

    public function testSend()
    {
        $old = ['username' => 'admin'];
        $new = ['username' => 'admin2'];

        $adapter = new Adapter\Table('Pop\Audit\Test\Assets\AuditLog');
        $adapter->setModel('MyApp\Model\User');
        $adapter->setModelId(1001);
        $adapter->resolveDiff($old, $new);
        $row = $adapter->send();

        $new = json_decode($row->new, true);
        $this->assertEquals('admin2', $new['username']);


        if (file_exists(__DIR__ . '/../tmp/auditor.sqlite')) {
            unlink( __DIR__ . '/../tmp/auditor.sqlite');
        }
    }

    public function testSendException()
    {
        $this->expectException('Pop\Audit\Adapter\Exception');
        $adapter = new Adapter\Table('Pop\Audit\Test\Assets\AuditLog');
        $adapter->send();
    }
}