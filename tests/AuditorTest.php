<?php

namespace Kettle\Auth\Test;

use PHPUnit\Framework\TestCase;
use Pop\Audit;

class AuditorTest extends TestCase
{

    public function testConstructor()
    {
        $auditor = new Audit\Auditor();
        $this->assertInstanceOf('Pop\Audit\Auditor', $auditor);
    }

}