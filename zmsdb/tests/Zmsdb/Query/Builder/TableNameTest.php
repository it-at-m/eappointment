<?php

namespace BO\Zmsdb\Tests\Query\Builder;

use PHPUnit\Framework\TestCase;
use BO\Zmsdb\Query\Builder\TableName;

class TableNameTest extends TestCase
{
    /**
     * @return TableName
     */
    protected function traitObject()
    {
        return new class {
            use \BO\Zmsdb\Query\Builder\TableName;
        };
    }

    public function testSetGet()
    {
        $t = $this->traitObject();
        $this->assertNull($t->table());
        $this->assertEquals($t, $t->table('users'));
        $this->assertEquals('users', $t->table());
    }
}
