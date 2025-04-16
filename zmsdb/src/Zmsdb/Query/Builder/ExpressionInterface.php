<?php

namespace BO\Zmsdb\Query\Builder;

/**
 * Interface ExpressionInterface
 *
 * Interface for expressions so you can create / pass your own. All we need
 * is the __toString() function so the SQL builder can concatenate.
 *
 * @package     BO\Zmsdb\Query\Builder
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
interface ExpressionInterface
{
    /**
     * Return the expression ready for concatenation into the query
     *
     * @return  string
     */
    public function __toString();
}
