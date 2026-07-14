<?php

namespace BO\Zmsbackend\Tests\Query\Builder;

use PHPUnit\Framework\TestCase;
use BO\Zmsbackend\Query\Builder\TableName;

class TableNameTest extends TestCase
{
    /**
     * @return TableName
     */
    protected function traitObject()
    {
        return $this->getMockForTrait('BO\\Zmsbackend\\Query\\Builder\\TableName');
    }

    public function testSetGet()
    {
        $t = $this->traitObject();
        $this->assertNull($t->table());
        $this->assertEquals($t, $t->table('users'));
        $this->assertEquals('users', $t->table());
    }
}
