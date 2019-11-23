<?php

namespace Pop\Audit\Test\Adapter;

use PHPUnit\Framework\TestCase;
use Pop\Audit\Adapter;
use Pop\Audit\Test\Assets\AuditLog;

class TableTest extends TestCase
{

    public function testConstructor()
    {
        chmod(__DIR__ . '/../tmp', 0777);
        touch(__DIR__ . '/../tmp/auditor.sqlite');
        chmod(__DIR__ . '/../tmp/auditor.sqlite', 0777);


        $db = \Pop\Db\Db::sqliteConnect([
            'database' => __DIR__ . '/../tmp/auditor.sqlite'
        ]);
        AuditLog::setDb($db);

        $adapter = new Adapter\Table('Pop\Audit\Test\Assets\AuditLog');
        $this->assertInstanceOf('Pop\Audit\Adapter\Table', $adapter);
        $this->assertEquals('Pop\Audit\Test\Assets\AuditLog', $adapter->getTable());
        $this->assertTrue($db->hasTable(AuditLog::table()));
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

        $states = $adapter->getStates();
        $this->assertGreaterThanOrEqual(0, count($states));

        $states = $adapter->getStates(['timestamp-' => null]);
        $this->assertGreaterThanOrEqual(0, count($states));

        $stateById = $adapter->getStateById($row->id);
        $this->assertEquals('admin', $stateById['old']['username']);
        $this->assertEquals('admin2', $stateById['new']['username']);

        $stateByModel = $adapter->getStateByModel('MyApp\Model\User', 1001);
        $this->assertGreaterThanOrEqual(0, count($stateByModel));

        $stateByTs = $adapter->getStateByTimestamp(time() + 10, time() - 10);
        $stateByTs = reset($stateByTs);
        $this->assertGreaterThanOrEqual(0, count($stateByTs));

        $stateByDate = $adapter->getStateByDate(date('Y-m-d'), date('Y-m-d'));
        $stateByDate = reset($stateByDate);
        $this->assertGreaterThanOrEqual(0, count($stateByDate));

        $preSnapshot  = $adapter->getSnapshot($row->id);
        $postSnapshot = $adapter->getSnapshot($row->id, true);

        $this->assertNotEquals($preSnapshot['username'], $postSnapshot['username']);

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

    public function testSendModelException()
    {
        $this->expectException('Pop\Audit\Adapter\Exception');
        $old = ['username' => 'admin'];
        $new = ['username' => 'admin2'];

        $adapter = new Adapter\Table('Pop\Audit\Test\Assets\AuditLog');
        $adapter->resolveDiff($old, $new);
        $adapter->send();

        unlink(__DIR__ . '/../tmp/auditor.sqlite');
    }
}