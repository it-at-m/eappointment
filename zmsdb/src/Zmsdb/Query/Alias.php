<?php

namespace BO\Zmsdb\Query;

class Alias implements \BO\Zmsdb\Query\Builder\ExpressionInterface
{
    use \BO\Zmsdb\Query\Builder\Dialect\Quote;

    protected $name;
    protected $alias;

    public function __construct($name, $alias)
    {
        $this->name = $this->quoteStructureParts($name, '`');
        $this->alias = $this->quoteStructureParts($alias, '`');
    }

    public function __toString()
    {
        return $this->name . ' AS ' . $this->alias;
    }

    public function getAliasIdentifier()
    {
        return $this->alias;
    }
}
