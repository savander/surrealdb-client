<?php

namespace Savander\SurrealdbClient\Tests\Unit;

use Savander\SurrealdbClient\Tests\TestCase;

class PingTest extends TestCase
{
    public function testPingSuccessful(): void
    {
        $this->assertTrue(
            $this->makeDatabaseConnection()->ping()->results()
        );
    }
}
