<?php

namespace BO\Zmsdb\Tests\Query\Builder;

use PHPUnit\Framework\TestCase;
use BO\Zmsdb\Query\Builder\Delete;

class DeleteTest extends TestCase
{
    public function testQueryBase()
    {
        $query = new Delete;
        $this->assertEquals('DELETE FROM', $query->queryBaseStatement());
        $this->assertEquals($query, $query->queryBaseStatement('DELETE SPECIAL'));
        $this->assertEquals('DELETE SPECIAL', $query->queryBaseStatement());
    }

    public function testTable()
    {
        $q = new Delete();
        $this->assertNull($q->table());
        $this->assertEquals($q, $q->table('users'));
        $this->assertEquals('users', $q->table());
    }

    public function testBasicDeleteSQL()
    {
        $q = new Delete();
        $q
            ->table('users')
            ->where('id', '=', 27)
            ->limit(1)
        ;

        $this->assertEquals('DELETE FROM "users" WHERE "id" = ? LIMIT 1', (string)$q);
        $this->assertEquals([27], $q->params());
    }

    public function testResetDelete()
    {
        $q = new Delete();
        $q
            ->table('users')
            ->where('id', '=', 1)
            ->limit(1)
        ;

        $this->assertEquals($q, $q->reset());
        $this->assertEquals('', (string)$q);
        $this->assertEquals(null, $q->table());
        $this->assertEquals([], $q->getWhereParams());
    }

    public function testDeleteIn()
    {
        $q = new Delete();
        $q
            ->table('users')
            ->where('id', 'IN', [27, 28, 29])
            ->where('name', '!=', 'Alex');

        $this->assertEquals('DELETE FROM "users" WHERE "id" IN (?, ?, ?) AND "name" != ?', (string)$q);
        $this->assertEquals([27, 28, 29, 'Alex'], $q->params());
    }

    public function testAllTablesReferenced()
    {
        $q = new Delete();
        $this->assertEquals([], $q->allTablesReferenced());

        $q = new Delete();
        $q->table('users');
        $this->assertEquals(['users'], $q->allTablesReferenced());

        $q = new Delete();
        $q->table('users');
        $q->table('locations');
        $this->assertEquals(['locations'], $q->allTablesReferenced());
    }
}
