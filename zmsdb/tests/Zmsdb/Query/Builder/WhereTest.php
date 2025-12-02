<?php

namespace BO\Zmsdb\Tests\Query\Builder;

use PHPUnit\Framework\TestCase;
use BO\Zmsdb\Query\Builder\ConditionBuilder;
use BO\Zmsdb\Query\Builder\Dialect\ANSI;
use BO\Zmsdb\Query\Builder\Expression;

class WhereTest extends TestCase
{
    /**
     * @return \BO\Zmsdb\Query\Builder\Where
     */
    protected function whereObject()
    {
        return new class {
            use \BO\Zmsdb\Query\Builder\Where;
        };
    }

    public function testNoWhere()
    {
        $w = $this->whereObject();
        $this->assertEquals('', $w->buildWhereSQL(new ANSI));

        // Test return type:
        $this->assertEquals([], $w->where());
    }

    public function testSimpleWhere()
    {
        $w = $this->whereObject();
        $this->assertEquals($w, $w->where('name', '=', 'Alex'));
        $this->assertEquals('WHERE "name" = ?', $w->buildWhereSQL(new ANSI));
        $this->assertEquals(['Alex'], $w->getWhereParams());

        // Test return type:
        $where = $w->where();
        $this->assertCount(1, $where);
        $this->assertEquals([
            'join' => 'AND',
            'field' => 'name',
            'operator' => '=',
            'value' => 'Alex'
        ], $where[0]);
    }

    public function testSimpleOR()
    {
        $w = $this->whereObject();
        $w->where('name', '=', 'Alex')
            ->orWhere('name', '=', 'Alexander');

        $this->assertEquals('WHERE "name" = ? OR "name" = ?', $w->buildWhereSQL(new ANSI));
        $this->assertEquals(['Alex', 'Alexander'], $w->getWhereParams());

        // Test return type:
        $where = $w->where();
        $this->assertCount(2, $where);
        $this->assertEquals([
            'join' => 'AND',
            'field' => 'name',
            'operator' => '=',
            'value' => 'Alex'
        ], $where[0]);

        $this->assertEquals([
            'join' => 'OR',
            'field' => 'name',
            'operator' => '=',
            'value' => 'Alexander'
        ], $where[1]);
    }

    public function testOnlyOR()
    {
        $w = $this->whereObject();
        $w->orWhere('name', '=', 'Alex');

        $this->assertEquals('WHERE "name" = ?', $w->buildWhereSQL(new ANSI));
        $this->assertEquals(['Alex'], $w->getWhereParams());

        $where = $w->orWhere();
        $this->assertEquals([
            'join' => 'OR',
            'field' => 'name',
            'operator' => '=',
            'value' => 'Alex'
        ], $where[0]);
    }

    public function testSimpleGroup()
    {
        $w = $this->whereObject();
        $w->where('name', '=', 'Alex');
        $w->where(function (ConditionBuilder $q) {
            $q->andWith('city', '=', 'London');
            $q->orWith('city', '=', 'Toronto');
        });

        $this->assertEquals('WHERE "name" = ? AND ("city" = ? OR "city" = ?)', $w->buildWhereSQL(new ANSI));
        $this->assertEquals(['Alex', 'London', 'Toronto'], $w->getWhereParams());

        $where = $w->where();
        $this->assertEquals([
            ['join' => 'AND', 'field' => 'name', 'operator' => '=', 'value' => 'Alex'],
            [
                'join' => 'AND',
                'sub' => [
                    ['join' => 'AND', 'field' => 'city', 'operator' => '=', 'value' => 'London'],
                    ['join' => 'OR', 'field' => 'city', 'operator' => '=', 'value' => 'Toronto']
                ]
            ]
        ], $where);
    }

    /**
     * Time for a final, large and complex query to test the where() and orWhere() clauses.
     */
    public function testWhereComplex()
    {
        $w = $this->whereObject();

        $w
            ->where('name', '=', 'Alex')
            ->orWhere('name', '=', 'Lucie')
            ->where(function (ConditionBuilder $query) {
                $query
                    ->andWith('city', '=', 'London')
                    ->andWith('country', '=', 'GB');
            })
            ->orWhere(function (ConditionBuilder $query) {
                $query
                    ->andWith('city', '=', 'Toronto')
                    ->andWith('country', '=', 'CA')
                    ->orWith(function (ConditionBuilder $query) {
                        $query->andWith('active', '!=', true);
                    });
            });

        $this->assertEquals(
            'WHERE "name" = ? OR "name" = ? AND ("city" = ? AND "country" = ?) '
            .'OR ("city" = ? AND "country" = ? OR ("active" != ?))',
            $w->buildWhereSQL(new ANSI)
        );
        $this->assertEquals(
            ['Alex', 'Lucie', 'London', 'GB', 'Toronto', 'CA', true],
            $w->getWhereParams()
        );

        // Check the return types:
        $where = $w->where();
        $this->assertCount(4, $where);
        $this->assertEquals([
            ['join' => 'AND', 'field' => 'name', 'operator' => '=', 'value' => 'Alex'],
            ['join' => 'OR', 'field' => 'name', 'operator' => '=', 'value' => 'Lucie'],
            [
                'join' => 'AND',
                'sub' => [
                    ['join' => 'AND', 'field' => 'city', 'operator' => '=', 'value' => 'London'],
                    ['join' => 'AND', 'field' => 'country', 'operator' => '=', 'value' => 'GB']
                ]
            ],
            [
                'join' => 'OR',
                'sub' => [
                    ['join' => 'AND', 'field' => 'city', 'operator' => '=', 'value' => 'Toronto'],
                    ['join' => 'AND', 'field' => 'country', 'operator' => '=', 'value' => 'CA'],
                    [
                        'join' => 'OR',
                        'sub' => [
                            ['join' => 'AND', 'field' => 'active', 'operator' => '!=', 'value' => true],
                        ]
                    ]
                ]
            ]
        ], $where);
    }

    public function testResetWhere()
    {
        $w = $this->whereObject();

        $w->where('name', '=', 'Alex');
        $w->where('age', '>', 18);
        $this->assertCount(2, $w->where());

        $this->assertEquals($w, $w->resetWhere());
        $this->assertEquals([], $w->where());
        $this->assertEquals([], $w->getWhereParams());
    }

    public function testWhereExpressions()
    {
        $w = $this->whereObject();
        $w->where(new Expression('logins'), '>', 27);
        $this->assertEquals('WHERE logins > ?', $w->buildWhereSQL(new ANSI));
    }

    public function testWhereIn()
    {
        $w = $this->whereObject();

        $w->where('id', 'IN', [27, 28, 29]);
        $this->assertEquals([
            'join' => 'AND',
            'field' => 'id',
            'operator' => 'IN',
            'value' => [27, 28, 29]
        ], $w->where()[0]);
        $this->assertEquals('WHERE "id" IN (?, ?, ?)', $w->buildWhereSQL(new ANSI));
        $this->assertEquals([27, 28, 29], $w->getWhereParams());
    }

    public function testWhereNotIn()
    {
        $w = $this->whereObject();

        $w->where('id', 'NOT IN', [27, 28, 29]);
        $this->assertEquals([
            'join' => 'AND',
            'field' => 'id',
            'operator' => 'NOT IN',
            'value' => [27, 28, 29]
        ], $w->where()[0]);
        $this->assertEquals('WHERE "id" NOT IN (?, ?, ?)', $w->buildWhereSQL(new ANSI));
        $this->assertEquals([27, 28, 29], $w->getWhereParams());
    }

    public function testWhereInPlaysNicely()
    {
        $w = $this->whereObject();
        $w
            ->where('active', '=', 1)
            ->where('id', 'IN', [27, 28, 29]);

        $this->assertEquals('WHERE "active" = ? AND "id" IN (?, ?, ?)', $w->buildWhereSQL(new ANSI));
        $this->assertEquals([1, 27, 28, 29], $w->getWhereParams());
    }
}
