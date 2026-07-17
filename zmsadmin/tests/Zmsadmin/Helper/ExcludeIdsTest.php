<?php

namespace BO\Zmsadmin\Tests\Helper;

use BO\Zmsadmin\Helper\ExcludeIds;
use PHPUnit\Framework\TestCase;

class ExcludeIdsTest extends TestCase
{
    public function testFromQueryParsesCsv(): void
    {
        $this->assertSame(['999999', '82252'], ExcludeIds::fromQuery('999999,82252'));
        $this->assertSame(['999999'], ExcludeIds::fromQuery(' 999999 , '));
        $this->assertSame([], ExcludeIds::fromQuery(''));
        $this->assertSame([], ExcludeIds::fromQuery(null));
    }

    public function testToQueryJoinsIds(): void
    {
        $this->assertSame('999999,82252', ExcludeIds::toQuery(['999999', '82252']));
        $this->assertSame('', ExcludeIds::toQuery([]));
    }
}
