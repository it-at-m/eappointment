<?php

namespace BO\Zmsdb\Tests\Query\Builder;

use PHPUnit\Framework\TestCase;
use BO\Zmsdb\Query\Builder\Expression;
use BO\Zmsdb\Query\Builder\Select;

class SelectTest extends TestCase
{
    /*
     * ------------------- Core Tests ------------------------
     */

    public function testQueryBase()
    {
        $query = new Select;
        $this->assertEquals('SELECT', $query->queryBaseStatement());
        $this->assertEquals($query, $query->queryBaseStatement('SELECT DISTINCT'));
        $this->assertEquals('SELECT DISTINCT', $query->queryBaseStatement());
    }

    /*
     * ------------------- SELECT testing -------------------------
     */
    public function testSelect()
    {
        // No alias
        $query = new Select;
        $this->assertEquals($query, $query->select('id'));
        $this->assertEquals([['column' => 'id', 'alias' => null]], $query->select());

        // Aliasing
        $query = new Select;
        $query->select(['my_id' => 'id']);
        $this->assertEquals([['column' => 'id', 'alias' => 'my_id']], $query->select());

        // Array, without aliasing
        $query = new Select;
        $query->select(['id', 'username', 'password']);
        $this->assertEquals([
            ['column' => 'id', 'alias' => null],
            ['column' => 'username', 'alias' => null],
            ['column' => 'password', 'alias' => null]
        ], $query->select());

        // Array with aliasing
        $query = new Select;
        $query->select(['my_id' => 'id', 'my_username' => 'username']);
        $this->assertEquals([
            ['column' => 'id', 'alias' => 'my_id'],
            ['column' => 'username', 'alias' => 'my_username']
        ], $query->select());
    }

    public function testSelectSQL()
    {
        $query = new Select;
        $this->assertEquals('', $query->buildSelectSQL());

        $query = new Select;
        $query->select('name');
        $this->assertEquals('SELECT "name"', $query->buildSelectSQL());

        $query = new Select;
        $query->select(['name', 'age']);
        $this->assertEquals('SELECT "name", "age"', $query->buildSelectSQL());

        $query = new Select;
        $query->select(['myname' => 'name']);
        $this->assertEquals('SELECT "name" AS "myname"', $query->buildSelectSQL());

        $query = new Select;
        $query->select(['myname' => 'name', 'years_alive' => 'age']);
        $this->assertEquals('SELECT "name" AS "myname", "age" AS "years_alive"', $query->buildSelectSQL());
    }

    public function testResetSelect()
    {
        $query = new Select;
        $query
            ->select('name')
            ->select('age');

        $this->assertEquals([
            ['column' => 'name', 'alias' => null],
            ['column' => 'age', 'alias' => null]
        ], $query->select());

        $this->assertEquals($query, $query->resetSelect());
        $this->assertEquals([], $query->select());
    }

    public function testSelectExpressions()
    {
        $query = new Select;
        $query->select(new Expression('COUNT(*)'));
        $this->assertEquals('SELECT COUNT(*)', $query->buildSelectSQL());
    }

    public function testSelectExpressionsAs()
    {
        $query = new Select;
        $query->select(new Expression('COUNT(*)'), 'total');
        $this->assertEquals('SELECT COUNT(*) AS "total"', $query->buildSelectSQL());
    }

    /*
     * ------------------ FROM testing -----------------------------
     */

    public function testFromWithAlias()
    {
        $query = new Select;

        // With alias
        $query->from('users', 'u');
        $this->assertEquals([[
            'table' => 'users',
            'alias' => 'u'
        ]], $query->from());
    }

    public function testFromWithoutAlias()
    {
        $query = new Select;
        $query->from('noalias');

        $this->assertEquals([[
            'table' => 'noalias',
            'alias' => null
        ]], $query->from());
    }

    public function testFromSQL()
    {
        $query = new Select;
        $this->assertEquals('', $query->buildFromSQL());

        $query = new Select;
        $query->from('users');
        $this->assertEquals('FROM "users"', $query->buildFromSQL());

        $query = new Select;
        $query->from('users', 'u');
        $this->assertEquals('FROM "users" "u"', $query->buildFromSQL());

        $query = new Select;
        $query
            ->from('users', 'u')
            ->from('roles', 'r');

        $this->assertEquals('FROM "users" "u", "roles" "r"', $query->buildFromSQL());
    }

    public function testResetFrom()
    {
        $query = new Select;
        $query
            ->from('users')
            ->from('comments');

        $this->assertEquals([
            ['table' => 'users', 'alias' => null],
            ['table' => 'comments', 'alias' => null],
        ], $query->from());

        $this->assertEquals($query, $query->resetFrom());
        $this->assertEquals([], $query->from());
    }

    public function testFromExpressions()
    {
        $query = new Select;
        $query
            ->from(new Expression('users u'));

        $this->assertEquals('FROM users u', $query->buildFromSQL());
    }

    /*
     * ------------------- JOIN testing ----------------------------
     */

    public function testJoinDefault()
    {
        $query = new Select;

        $this->assertEquals($query, $query->join('posts', 'users.id', '=', 'posts.user_id'));
        $this->assertEquals([
            [
                'right' => 'posts',
                'leftField' => 'users.id',
                'operator' => '=',
                'rightField' => 'posts.user_id'
            ]
        ], $query->join());
    }

    public function testJoinExplicitType()
    {
        $query = new Select;

        $this->assertEquals($query, $query->leftJoin('posts', 'users.id', '=', 'posts.user_id'));
        $this->assertEquals([
            [
                'right' => 'posts',
                'leftField' => 'users.id',
                'operator' => '=',
                'rightField' => 'posts.user_id'
            ]
        ], $query->leftJoin());
    }

    public function testMultiJoin()
    {
        $query = new Select;

        $query
            ->join('posts', 'users.id', '=', 'posts.user_id')
            ->leftJoin('comments', 'users.id', '=', 'comments.user_id')
        ;
        $this->assertEquals([
            [
                'right' => 'posts',
                'leftField' => 'users.id',
                'operator' => '=',
                'rightField' => 'posts.user_id'
            ]
        ], $query->join());

        $this->assertEquals([
            [
                'right' => 'comments',
                'leftField' => 'users.id',
                'operator' => '=',
                'rightField' => 'comments.user_id'
            ]
        ], $query->leftJoin());
    }

    public function testJoinSQL()
    {
        $query = new Select;
        $this->assertEquals('', $query->buildJoinSQL());

        $query = new Select;
        $query->join('posts', 'users.id', '=', 'posts.user_id');
        $this->assertEquals('JOIN "posts" ON "users"."id" = "posts"."user_id"', $query->buildJoinSQL());

        $query = new Select;
        $query->leftJoin('posts', 'users.id', '=', 'posts.user_id');
        $this->assertEquals('LEFT JOIN "posts" ON "users"."id" = "posts"."user_id"', $query->buildJoinSQL());

        $query = new Select;
        $query->rightJoin('posts', 'users.id', '=', 'posts.user_id');
        $this->assertEquals('RIGHT JOIN "posts" ON "users"."id" = "posts"."user_id"', $query->buildJoinSQL());

        $query = new Select;
        $query->join('posts', 'users.id', '=', 'posts.user_id');
        $query->leftJoin('comments', 'users.id', '=', 'comments.user_id');
        $this->assertEquals(
            'JOIN "posts" ON "users"."id" = "posts"."user_id"'."\n"
            .'LEFT JOIN "comments" ON "users"."id" = "comments"."user_id"',
            $query->buildJoinSQL()
        );
    }

    public function testResetJoins()
    {
        $query = new Select;
        $query->join('posts', 'users.id', '=', 'posts.user_id');
        $query->leftJoin('comments', 'users.id', '=', 'comments.user_id');

        $this->assertCount(1, $query->join());
        $this->assertCount(1, $query->leftJoin());

        $this->assertEquals($query, $query->resetJoins());
        $this->assertEquals([], $query->join());
    }

    /*
     * ------------------ GROUP BY testing ---------------------
     */

    public function testGroupBy()
    {
        $query = new Select;
        $this->assertEquals([], $query->groupBy());

        $query = new Select;
        $this->assertEquals($query, $query->groupBy('name'));
        $this->assertEquals(['name'], $query->groupBy());

        $query = new Select;
        $query
            ->groupBy('name')
            ->groupBy('age');

        $this->assertEquals(['name', 'age'], $query->groupBy());

        $query = new Select;
        $query->groupBy(['name', 'age']);
        $this->assertEquals(['name', 'age'], $query->groupBy());
    }

    public function testGroupBySQL()
    {
        $query = new Select;
        $this->assertEquals('', $query->buildGroupBySQL());

        $query = new Select;
        $query->groupBy('name');
        $this->assertEquals('GROUP BY "name"', $query->buildGroupBySQL());

        $query = new Select;
        $query
            ->groupBy('name')
            ->groupBy('age');

        $this->assertEquals('GROUP BY "name", "age"', $query->buildGroupBySQL());

        $query = new Select;
        $query->groupBy(['name', 'age']);
        $this->assertEquals('GROUP BY "name", "age"', $query->buildGroupBySQL());
    }

    public function testResetGroupBy()
    {
        $query = new Select;
        $query
            ->groupBy('name')
            ->groupBy('age');

        $this->assertEquals(['name', 'age'], $query->groupBy());

        $this->assertEquals($query, $query->resetGroupBy());
        $this->assertEquals([], $query->groupBy());
    }

    /*
     * ------------------ ORDER BY testing -------------------------
     */

    public function testOrderBySingle()
    {
        $query = new Select;
        $this->assertEquals($query, $query->orderBy('name', 'DESC'));
        $this->assertEquals([
            ['field' => 'name', 'direction' => 'DESC']
        ], $query->orderBy());

        // And test the default:
        $query = new Select;
        $query->orderBy('name');
        $this->assertEquals([
            ['field' => 'name', 'direction' => 'ASC']
        ], $query->orderBy());
    }

    public function testOrderByArray()
    {
        $query = new Select;

        $order = [
            'name' => 'ASC',
            'age' => 'DESC',
        ];

        $this->assertEquals($query, $query->orderBy($order));
        $this->assertEquals([
            ['field' => 'name', 'direction' => 'ASC'],
            ['field' => 'age', 'direction' => 'DESC']
        ], $query->orderBy());
    }

    public function testOrderBySQL()
    {
        $query = new Select;
        $this->assertEquals('', $query->buildOrderBySQL());

        $query = new Select;
        $query->orderBy('name', 'ASC');
        $this->assertEquals('ORDER BY "name" ASC', $query->buildOrderBySQL());

        $query = new Select;
        $query->orderBy('name', 'ASC');
        $query->orderBy('age', 'DESC');
        $this->assertEquals('ORDER BY "name" ASC, "age" DESC', $query->buildOrderBySQL());

        $query = new Select;
        $query->orderBy([
            'name'  => 'ASC',
            'age'   => 'DESC'
        ]);
        $this->assertEquals('ORDER BY "name" ASC, "age" DESC', $query->buildOrderBySQL());
    }

    public function testResetOrderBy()
    {
        $query = new Select;
        $query
            ->orderBy('name', 'ASC')
            ->orderBy('age', 'DESC');

        $this->assertEquals([
            ['field' => 'name', 'direction' => 'ASC'],
            ['field' => 'age', 'direction' => 'DESC']
        ], $query->orderBy());

        $this->assertEquals($query, $query->resetOrderBy());
        $this->assertEquals([], $query->orderBy());
    }

    public function testOrderByExpressions()
    {
        $query = new Select;
        $query->orderBy(new Expression('RAND()'));

        $this->assertEquals('ORDER BY RAND()', $query->buildOrderBySQL());
    }

    /*
     * ------------------ SQL string testing ------------------
     */

    public function testBasicStatement()
    {
        $q = new Select();
        $q->select('*')
            ->from('users');

        $this->assertEquals(
            'SELECT * FROM "users"',
            (string)$q
        );
        $this->assertEquals([], $q->params());
    }

    public function testStatementWithWhere()
    {
        $q = new Select();
        $q->select('*')
            ->from('users')
            ->where('name', '=', 'Alex');

        $this->assertEquals(
            'SELECT * FROM "users" WHERE "name" = ?',
            (string)$q
        );
        $this->assertEquals(['Alex'], $q->params());
    }

    public function testStatementWithOrder()
    {
        $q = new Select();
        $q->select('*')
            ->from('users')
            ->where('name', '=', 'Alex')
            ->orderBy('created', 'DESC')
        ;

        $this->assertEquals(
            'SELECT * FROM "users" WHERE "name" = ? ORDER BY "created" DESC',
            (string)$q
        );
        $this->assertEquals(['Alex'], $q->params());
    }

    public function testStatementWithLimit()
    {
        $q = new Select();
        $q->select('*')
            ->from('users')
            ->where('name', '=', 'Alex')
            ->orderBy('created', 'DESC')
            ->limit(10)
        ;

        $this->assertEquals(
            'SELECT * FROM "users" WHERE "name" = ? ORDER BY "created" DESC LIMIT 10',
            (string)$q
        );
        $this->assertEquals(['Alex'], $q->params());
    }

    public function testStatementWithLimitAndOffset()
    {
        $q = new Select();
        $q->select('*')
            ->from('users')
            ->where('name', '=', 'Alex')
            ->orderBy('created', 'DESC')
            ->limit(10)
            ->offset(5)
        ;

        $this->assertEquals(
            'SELECT * FROM "users" WHERE "name" = ? ORDER BY "created" DESC LIMIT 5, 10',
            (string)$q
        );
        $this->assertEquals(['Alex'], $q->params());
    }

    public function testStatementWithJoins()
    {
        $q = new Select();
        $q->select('*')
            ->from('users')
            ->where('name', '=', 'Alex')
            ->orderBy('created', 'DESC')
            ->limit(10)
            ->offset(5)
            ->leftJoin('comments', 'users.id', '=', 'comments.user_id')
        ;

        $this->assertEquals(
            'SELECT * FROM "users" LEFT JOIN "comments" ON "users"."id" = "comments"."user_id" '
            .'WHERE "name" = ? ORDER BY "created" DESC LIMIT 5, 10',
            (string)$q
        );
        $this->assertEquals(['Alex'], $q->params());
    }

    public function testStatementWithGroupBy()
    {
        $q = new Select();
        $q->select('*')
            ->from('users')
            ->where('name', '=', 'Alex')
            ->orderBy('created', 'DESC')
            ->limit(10)
            ->offset(5)
            ->leftJoin('comments', 'users.id', '=', 'comments.user_id')
            ->groupBy('comments.user_id')
        ;

        $this->assertEquals(
            'SELECT * FROM "users" LEFT JOIN "comments" ON "users"."id" = "comments"."user_id" '
            .'WHERE "name" = ? GROUP BY "comments"."user_id" ORDER BY "created" DESC LIMIT 5, 10',
            (string)$q
        );
        $this->assertEquals(['Alex'], $q->params());
    }

    public function testStatementWithHaving()
    {
        $q = new Select();
        $q->select('*')
            ->from('users')
            ->where('name', '=', 'Alex')
            ->orderBy('created', 'DESC')
            ->limit(10)
            ->offset(5)
            ->leftJoin('comments', 'users.id', '=', 'comments.user_id')
            ->groupBy('comments.user_id')
            ->having(new Expression('COUNT("comments"."user_id")'), '>', 10)
        ;

        $this->assertEquals(
            'SELECT * FROM "users" LEFT JOIN "comments" ON "users"."id" = "comments"."user_id" '
            .'WHERE "name" = ? GROUP BY "comments"."user_id" HAVING COUNT("comments"."user_id") > ? '
            .'ORDER BY "created" DESC LIMIT 5, 10',
            (string)$q
        );
        $this->assertEquals(['Alex', 10], $q->params());
    }

    public function testFullReset()
    {
        $q = new Select();
        $q->select('*')
            ->from('users')
            ->where('name', '=', 'Alex')
            ->orderBy('created', 'DESC')
            ->limit(10)
            ->offset(5)
            ->leftJoin('comments', 'users.id', '=', 'comments.user_id')
            ->groupBy('comments.user_id')
            ->having('COUNT(comments.user_id)', '>', 10)
        ;

        $this->assertEquals($q, $q->reset());
        $this->assertEquals('', $q->sql());
        $this->assertEquals([], $q->params());
    }

    // ----------------- All Tables Tests -------------------------

    public function testTablesReferencedFrom()
    {
        $q = new Select();
        $this->assertEquals([], $q->allTablesReferenced());

        $q = new Select();
        $q->from('users');
        $this->assertEquals(['users'], $q->allTablesReferenced());

        $q = new Select();
        $q->from('users', 'u');
        $this->assertEquals(['users'], $q->allTablesReferenced());

        $q = new Select();
        $q
            ->from('users')
            ->from('locations');
        $this->assertEquals(['users', 'locations'], $q->allTablesReferenced());

        $q = new Select();
        $q
            ->from('users', 'u')
            ->from('locations', 'l');
        $this->assertEquals(['users', 'locations'], $q->allTablesReferenced());
    }

    public function testTablesReferencedJoin()
    {
        $q = new Select();
        $q->join('locations', 'locations.id', '=', 'users.location_id');
        $this->assertEquals(['locations'], $q->allTablesReferenced());

        $q = new Select();
        $q->leftJoin('locations', 'locations.id', '=', 'users.location_id');
        $this->assertEquals(['locations'], $q->allTablesReferenced());

        $q = new Select();
        $q->rightJoin('locations', 'locations.id', '=', 'users.location_id');
        $this->assertEquals(['locations'], $q->allTablesReferenced());

        $q = new Select();
        $q
            ->join('locations', 'locations.id', '=', 'users.location_id')
            ->join('addresses', 'addresses.id', '=', 'users.address_id');
        $this->assertEquals(['locations', 'addresses'], $q->allTablesReferenced());
    }

    public function testTablesReferencedBoth()
    {
        $q = new Select();
        $q
            ->from('users', 'u')
            ->join('locations', 'locations.id', '=', 'users.location_id')
            ->join('addresses', 'addresses.id', '=', 'users.address_id');

        $this->assertEquals(['users', 'locations', 'addresses'], $q->allTablesReferenced());
    }

    public function testTablesReferencedDuplicates()
    {
        $q = new Select();
        $q->from('users');
        $q->join('users', 'user.mother_id', '=', 'users.id');

        $this->assertEquals(['users'], $q->allTablesReferenced());
    }
}
