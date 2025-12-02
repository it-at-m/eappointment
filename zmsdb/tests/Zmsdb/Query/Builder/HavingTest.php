<?php

namespace BO\Zmsdb\Tests\Query\Builder;

use PHPUnit\Framework\TestCase;
use BO\Zmsdb\Query\Builder\ConditionBuilder;
use BO\Zmsdb\Query\Builder\Dialect\ANSI;
use BO\Zmsdb\Query\Builder\Expression;

class HavingTest extends TestCase
{
    /**
     * @return \BO\Zmsdb\Query\Builder\Having
     */
    protected function havingObject()
    {
        return new class {
            use \BO\Zmsdb\Query\Builder\Having;
        };
    }

    public function testNoHaving()
    {
        $w = $this->havingObject();
        $this->assertEquals('', $w->buildHavingSQL(new ANSI));

        // Test return type:
        $this->assertEquals([], $w->having());
    }

    public function testSimpleHaving()
    {
        $w = $this->havingObject();
        $this->assertEquals($w, $w->having('name', '=', 'Alex'));
        $this->assertEquals('HAVING "name" = ?', $w->buildHavingSQL(new ANSI));
        $this->assertEquals(['Alex'], $w->getHavingParams());

        // Test return type:
        $having = $w->having();
        $this->assertCount(1, $having);
        $this->assertEquals([
            'join' => 'AND',
            'field' => 'name',
            'operator' => '=',
            'value' => 'Alex'
        ], $having[0]);
    }

    public function testSimpleOR()
    {
        $w = $this->havingObject();
        $w->having('name', '=', 'Alex')
            ->orHaving('name', '=', 'Alexander');

        $this->assertEquals('HAVING "name" = ? OR "name" = ?', $w->buildHavingSQL(new ANSI));
        $this->assertEquals(['Alex', 'Alexander'], $w->getHavingParams());

        // Test return type:
        $having = $w->having();
        $this->assertCount(2, $having);
        $this->assertEquals([
            'join' => 'AND',
            'field' => 'name',
            'operator' => '=',
            'value' => 'Alex'
        ], $having[0]);

        $this->assertEquals([
            'join' => 'OR',
            'field' => 'name',
            'operator' => '=',
            'value' => 'Alexander'
        ], $having[1]);
    }

    public function testOnlyOR()
    {
        $w = $this->havingObject();
        $w->orHaving('name', '=', 'Alex');

        $this->assertEquals('HAVING "name" = ?', $w->buildHavingSQL(new ANSI));
        $this->assertEquals(['Alex'], $w->getHavingParams());

        $where = $w->orHaving();
        $this->assertEquals([
            'join' => 'OR',
            'field' => 'name',
            'operator' => '=',
            'value' => 'Alex'
        ], $where[0]);
    }

    public function testSimpleGroup()
    {
        $w = $this->havingObject();
        $w->having('name', '=', 'Alex');
        $w->having(function (ConditionBuilder $q) {
            $q->andWith('city', '=', 'London');
            $q->orWith('city', '=', 'Toronto');
        });

        $this->assertEquals('HAVING "name" = ? AND ("city" = ? OR "city" = ?)', $w->buildHavingSQL(new ANSI));
        $this->assertEquals(['Alex', 'London', 'Toronto'], $w->getHavingParams());

        $having = $w->having();
        $this->assertEquals([
            ['join' => 'AND', 'field' => 'name', 'operator' => '=', 'value' => 'Alex'],
            [
                'join' => 'AND',
                'sub' => [
                    ['join' => 'AND', 'field' => 'city', 'operator' => '=', 'value' => 'London'],
                    ['join' => 'OR', 'field' => 'city', 'operator' => '=', 'value' => 'Toronto']
                ]
            ]
        ], $having);
    }

    /**
     * Time for a final, large and complex query to test the having() and orHaving() clauses.
     */
    public function testHavingComplex()
    {
        $w = $this->havingObject();

        $w
            ->having('name', '=', 'Alex')
            ->orHaving('name', '=', 'Lucie')
            ->having(function (ConditionBuilder $query) {
                $query
                    ->andWith('city', '=', 'London')
                    ->andWith('country', '=', 'GB');
            })
            ->orHaving(function (ConditionBuilder $query) {
                $query
                    ->andWith('city', '=', 'Toronto')
                    ->andWith('country', '=', 'CA')
                    ->orWith(function (ConditionBuilder $query) {
                        $query->andWith('active', '!=', true);
                    });
            });

        $this->assertEquals(
            'HAVING "name" = ? OR "name" = ? AND ("city" = ? AND "country" = ?) '
            .'OR ("city" = ? AND "country" = ? OR ("active" != ?))',
            $w->buildHavingSQL(new ANSI)
        );
        $this->assertEquals(
            ['Alex', 'Lucie', 'London', 'GB', 'Toronto', 'CA', true],
            $w->getHavingParams()
        );

        // Check the return types:
        $having = $w->having();
        $this->assertCount(4, $having);
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
        ], $having);
    }

    public function testResetHaving()
    {
        $w = $this->havingObject();

        $w->having('name', '=', 'Alex');
        $w->having('age', '>', 18);
        $this->assertCount(2, $w->having());

        $this->assertEquals($w, $w->resetHaving());
        $this->assertEquals([], $w->having());
        $this->assertEquals([], $w->getHavingParams());
    }

    public function testHavingExpressions()
    {
        $w = $this->havingObject();
        $w->having(new Expression('COUNT(users.id)'), '>', 27);
        $this->assertEquals('HAVING COUNT(users.id) > ?', $w->buildHavingSQL(new ANSI));
    }
}
