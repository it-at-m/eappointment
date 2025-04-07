<?php

namespace BO\Zmsdb\Query\Builder;

/**
 * Update
 *
 * Generates an SQL query for a DELETE operation.
 *
 * @package     BO\Zmsdb\Query\Builder
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
class Delete extends Query
{
    use TableName;
    use Where;
    use Paginate;

    /**
     * @var     string      The base part of the query
     */
    protected $queryBase = 'DELETE FROM';

    /**
     * Generates the full SQL statement for this query with all the composite parts.
     *
     * @return  string
     */
    public function sql()
    {
        if ($this->table === null) {
            return '';
        }

        $candidateParts = [
            $this->queryBase,
            $this->dialect->quoteTable($this->table),
            $this->buildWhereSQL($this->dialect),
            $this->buildPaginateSQL()
        ];

        return trim(implode(' ', $candidateParts));
    }

    /**
     * Returns all the parameters, in the correct order, to pass into PDO.
     *
     * @return  array
     */
    public function params()
    {
        return $this->getWhereParams();
    }

    /**
     * Resets the entire query.
     *
     * @return  $this
     */
    public function reset()
    {
        $this->table = null;
        $this->resetWhere();
        return $this;
    }

    /*
     * ------------------- All Tables ------------------------
     */

    /**
     * Returns all the tables that this query makes mention of, in FROMs and JOINs
     *
     * @return  array
     */
    public function allTablesReferenced()
    {
        if ($this->table()) {
            return [$this->table()];
        }
        return [];
    }
}
