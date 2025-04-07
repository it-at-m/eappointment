<?php

namespace BO\Zmsdb\Query\Builder;

/**
 * Insert
 *
 * Generates an SQL query for an INSERT operation.
 *
 * @package     BO\Zmsdb\Query\Builder
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
class Insert extends Query
{
    use TableName;
    use Values;

    /**
     * @var     string      The base part of the query
     */
    protected $queryBase = 'INSERT INTO';

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
            $this->valuesSQL(),
        ];

        return implode(' ', $candidateParts);
    }

    /**
     * Returns the values part of a query for an INSERT statement (so using VALUES())
     *
     * @return  string
     */
    protected function valuesSQL()
    {
        $sql = '';
        if (!empty($this->values)) {
            $keyParts = [];
            $valueParts = [];
            foreach ($this->values as $field => $_) {
                $keyParts[]     = $this->dialect->quoteField($field);
                $valueParts[]   = '?';
            }

            $sql .= '(' . implode(', ', $keyParts) . ') ';
            $sql .= 'VALUES (' . implode(', ', $valueParts) . ')';
        }
        return $sql;
    }

    /**
     * Returns all the parameters, in the correct order, to pass into PDO.
     *
     * @return  array
     */
    public function params()
    {
        return array_values($this->values);
    }

    /**
     * Resets the entire query.
     *
     * @return  $this
     */
    public function reset()
    {
        $this->table = null;
        $this->resetValues();
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
