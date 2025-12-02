<?php

namespace BO\Zmsdb\Tests\Query\Builder;

use PHPUnit\Framework\TestCase;

class PaginateTest extends TestCase
{
    /**
     * @return \BO\Zmsdb\Query\Builder\Paginate
     */
    protected function paginateObject()
    {
        return new class {
            use \BO\Zmsdb\Query\Builder\Paginate;
        };
    }

    public function testNoPaginateSet()
    {
        $p = $this->paginateObject();
        $this->assertEquals(null, $p->limit());
        $this->assertEquals(0, $p->offset());
        $this->assertEquals('', $p->buildPaginateSQL());
    }

    public function testLimitOnly()
    {
        $p = $this->paginateObject();

        $this->assertEquals($p, $p->limit(10));
        $this->assertEquals(10, $p->limit());

        $this->assertEquals('LIMIT 10', $p->buildPaginateSQL());
    }

    public function testLimitAndOffset()
    {
        $p = $this->paginateObject();

        $p->limit(10);
        $this->assertEquals($p, $p->offset(100));
        $this->assertEquals(100, $p->offset());

        $this->assertEquals('LIMIT 100, 10', $p->buildPaginateSQL());
    }

    public function testResetPaginate()
    {
        $p = $this->paginateObject();

        $p->limit(10)->offset(100);
        $this->assertEquals(10, $p->limit());
        $this->assertEquals(100, $p->offset());

        $this->assertEquals($p, $p->resetLimit());
        $this->assertEquals(null, $p->limit());

        $this->assertEquals($p, $p->resetOffset());
        $this->assertEquals(0, $p->offset());
    }
}
