<?php

namespace BO\Zmsbackend\Tests\Query\Builder;

use PHPUnit\Framework\TestCase;
use BO\Zmsbackend\Query\Builder\Expression;

class ExpressionTest extends TestCase
{
    public function testConstruct()
    {
        $e = new Expression('COUNT(*)');
        $this->assertInstanceOf('BO\\Zmsbackend\\Query\\Builder\\Expression', $e);
    }

    public function testToString()
    {
        $e = new Expression('COUNT(*)');
        $this->assertEquals('COUNT(*)', (string)$e);
    }
}
