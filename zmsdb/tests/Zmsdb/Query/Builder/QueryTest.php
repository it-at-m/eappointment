<?php

namespace BO\Zmsdb\Tests\Query\Builder;

use BO\Zmsdb\Query\Builder\Dialect\MySQL;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    public function testGettingSettingDialect()
    {
        $query = $this->getMockForAbstractClass('BO\\Zmsdb\\Query\\Builder\\Query', []);
        $this->assertInstanceOf('BO\\Zmsdb\\Query\\Builder\\Dialect\\ANSI', $query->dialect());

        $dialect = new MySQL();
        $this->assertEquals($query, $query->dialect($dialect));
        $this->assertEquals($dialect, $query->dialect());
    }

    /* ---------------- Flag Testing ----------------------- */

    public function testNoFlags()
    {
        /* @var     \BO\Zmsdb\Query\Builder\Query   $query  */
        $query = $this->getMockForAbstractClass('BO\\Zmsdb\\Query\\Builder\\Query', []);
        $this->assertEquals([], $query->flags());
        $this->assertEquals(null, $query->flag('unknown'));
    }

    public function testGetSetFlag()
    {
        /* @var     \BO\Zmsdb\Query\Builder\Query   $query  */
        $query = $this->getMockForAbstractClass('BO\\Zmsdb\\Query\\Builder\\Query', []);
        $dummyQuery = $this->getMockForAbstractClass('BO\\Zmsdb\\Query\\Builder\\Query', []);

        $this->assertEquals($query, $query->flag('ttl', 30));
        $this->assertEquals($query, $query->flag('model', 'User'));
        $this->assertEquals($query, $query->flag('fields', ['name', 'location']));
        $this->assertEquals($query, $query->flag('subquery', $dummyQuery));

        $this->assertEquals(30, $query->flag('ttl'));
        $this->assertEquals('User', $query->flag('model'));
        $this->assertEquals(['name', 'location'], $query->flag('fields'));
        $this->assertEquals($dummyQuery, $query->flag('subquery'));
    }

    public function testGetSetFlags()
    {
        /* @var     \BO\Zmsdb\Query\Builder\Query   $query  */
        $query = $this->getMockForAbstractClass('BO\\Zmsdb\\Query\\Builder\\Query', []);
        $dummyQuery = $this->getMockForAbstractClass('BO\\Zmsdb\\Query\\Builder\\Query', []);

        $flags = [
            'ttl'     => 30,
            'model'     => 'User',
            'fields'    => ['name', 'location'],
            'subquery'  => $dummyQuery,
        ];

        $this->assertEquals($query, $query->flags($flags));
        $this->assertEquals($flags, $query->flags());
    }
    
    public function testDeleteFlag()
    {
        /* @var     \BO\Zmsdb\Query\Builder\Query $query */
        $query = $this->getMockForAbstractClass('BO\\Zmsdb\\Query\\Builder\\Query', []);
        $query->flag('ttl', 30);
        $this->assertEquals(30, $query->flag('ttl'));

        $this->assertEquals($query, $query->deleteFlag('ttl'));
        $this->assertEquals(null, $query->flag('ttl'));
    }

    public function testGetSetQueryBase()
    {
        /* @var     \BO\Zmsdb\Query\Builder\Query    $q  */
        $q = $this->getMockForAbstractClass('BO\\Zmsdb\\Query\\Builder\\Query');
        $this->assertNull($q->queryBaseStatement());

        $this->assertEquals($q, $q->queryBaseStatement('SELECT DISTINCT'));
        $this->assertEquals('SELECT DISTINCT', $q->queryBaseStatement());
    }
}
