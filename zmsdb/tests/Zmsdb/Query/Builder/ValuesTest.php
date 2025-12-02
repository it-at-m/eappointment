<?php

namespace BO\Zmsdb\Tests\Query\Builder;

use PHPUnit\Framework\TestCase;
use BO\Zmsdb\Query\Builder\Values;

class ValuesTest extends TestCase
{
    /**
     * @return Values
     */
    protected function traitObject()
    {
        return new class {
            use \BO\Zmsdb\Query\Builder\Values;
        };
    }

    public function testDefault()
    {
        $v = $this->traitObject();
        $this->assertEquals([], $v->values());
        $this->assertEquals(null, $v->value('name'));
    }

    public function testSetGetArray()
    {
        $v = $this->traitObject();
        $this->assertEquals($v, $v->values(['name' => 'Alex', 'city' => 'London']));
        $this->assertEquals(['name' => 'Alex', 'city' => 'London'], $v->values());
    }

    public function testSetGetSingleValue()
    {
        $v = $this->traitObject();
        $this->assertEquals($v, $v->value('name', 'Alex'));
        $this->assertEquals('Alex', $v->value('name'));
    }
}
