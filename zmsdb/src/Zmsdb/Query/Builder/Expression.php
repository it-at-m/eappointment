<?php

namespace BO\Zmsdb\Query\Builder;

/**
 * Expression
 *
 * This class serves as a way of passing SQL expressions into a query
 * thereby marking them exempt from quotation by the Dialect (either field or table).
 *
 * It goes without saying, expressions are an SQL injection vulnerability. ONLY
 * use them on data you 100% trust. And seriously reconsider trusting it.
 *
 * @package     BO\Zmsdb\Query\Builder
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
class Expression implements ExpressionInterface
{
    /**
     * @var     string
     */
    protected $expression;

    /**
     * Pass in the expression you want exempted.
     *
     * @param   string  $exp
     */
    public function __construct($exp)
    {
        $this->expression = $exp;
    }

    /**
     * Returns this expression as a string to make concatenation easier.
     *
     * @return  string
     */
    public function __toString()
    {
        return $this->expression;
    }
}
