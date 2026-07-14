<?php

namespace BO\Zmsbackend\Query;

class Alias implements \BO\Zmsbackend\Query\Builder\ExpressionInterface
{
    use \BO\Zmsbackend\Query\Builder\Dialect\Quote;

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
