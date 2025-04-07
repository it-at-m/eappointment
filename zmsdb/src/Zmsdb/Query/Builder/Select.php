<?php

namespace BO\Zmsdb\Query\Builder;

/**
 * Select
 *
 * Runs a SELECT SQL query against the database.
 *
 * @package     BO\Zmsdb\Query\Builder
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
class Select extends Query
{
    use Where;
    use Having;
    use Paginate;

    /**
     * @var     string      The base part of the query
     */
    protected $queryBase = 'SELECT';

    /**
     * @var     array
     */
    protected $selectColumns = [];

    /**
     * @var     array
     */
    protected $fromTables = [];

    /**
     * @var     array
     */
    protected $joins = [];

    /**
     * @var     array
     */
    protected $groupBy = [];

    /**
     * @var     array
     */
    protected $orderBy = [];

    /*
     * ------------------ SELECT ---------------------
     */

    /**
     * Set/Get the Select columns.
     *
     * To get, pass no arguments.
     *
     * To set:
     *  - pass either a pair of string for column, alias
     *  - pass an array of columns
     *  - pass an array of [alias => column] pairs
     *
     * Columns can be ExpressionInterface instances.
     *
     * @param   string|array|null   $columns
     * @param   string|null         $alias      Null to get or no alias. Alias is ignored if $columns is an array.
     * @return  $this|array
     */
    public function select($columns = null, $alias = null)
    {
        if ($columns === null) {
            return $this->selectColumns;
        }

        if (!is_array($columns)) {
            $this->selectColumns[] = [
                'column' => $columns,
                'alias' => $alias,
            ];
        } else {
            foreach ($columns as $k => $v) {
                $this->selectColumns[] = [
                    'column'    => $v,
                    'alias'     => (is_numeric($k) || is_null($k)) ? null : $k,
                ];
            }
        }

        return $this;
    }

    /**
     * Builds the SQL for the SELECT component of the query
     *
     * @return  string
     */
    public function buildSelectSQL()
    {
        if (empty($this->selectColumns)) {
            return '';
        }

        $parts = [];
        foreach ($this->selectColumns as $c) {
            $ret = $this->dialect->quoteField($c['column']);
            $ret .= ($c['alias'] != null) ? ' AS ' . $this->dialect->quoteField($c['alias']) : '';
            $parts[] = $ret;
        }
        return $this->queryBase . ' ' . implode(', ', $parts);
    }

    /**
     * Resets the SELECT portion of this query to empty.
     *
     * @return  $this
     */
    public function resetSelect()
    {
        $this->selectColumns = [];
        return $this;
    }

    /*
     * ----------------- FROM --------------------
     */

    /**
     * Get/Set Table to pull from.
     *
     * @param   string|null     $table      String to set, NULL to return
     * @param   string|null     $alias
     * @return  $this|string|array
     */
    public function from($table = null, $alias = null)
    {
        if ($table === null) {
            return $this->fromTables;
        }
        $this->fromTables[] = [
            'table' => $table,
            'alias' => $alias
        ];
        return $this;
    }

    /**
     * Builds the SQL for the FROM component of the query
     *
     * @return  string
     */
    public function buildFromSQL()
    {
        if (empty($this->fromTables)) {
            return '';
        }

        $parts = [];
        foreach ($this->fromTables as $f) {
            $part = $this->dialect->quoteTable($f['table']);
            $part .= ($f['alias'] !== null) ? ' ' . $this->dialect->quoteTable($f['alias']) : '';
            $parts[] = $part;
        }
        return 'FROM ' . implode(', ', $parts);
    }

    /**
     * Resets the FROM portion of this query to empty.
     *
     * @return  $this
     */
    public function resetFrom()
    {
        $this->fromTables = [];
        return $this;
    }

    /*
     * ------------------ JOIN -----------------------
     */

    /**
     * Sets/Gets an INNER JOIN.
     *
     *  $query->join('comments', 'users.id', '=', 'comment.user_id');
     *
     * @param   string|null     $right          Name of the table to join
     * @param   string|null     $leftField      Left part of the ON
     * @param   string|null     $operator       Operator for the ON
     * @param   string|null     $rightField     Right part of the ON
     * @return  $this|array                     $this on set, array on get
     * @throws  \InvalidArgumentException       On unknown $type
     */
    public function join($right = null, $leftField = null, $operator = null, $rightField = null)
    {
        return $this->applyJoin('INNER', $right, $leftField, $operator, $rightField);
    }

    /**
     * Sets/Gets a LEFT JOIN.
     *
     *  $query->leftJoin('comments', 'users.id', '=', 'comment.user_id');
     *
     * @param   string|null     $right          Name of the table we're joining
     * @param   string|null     $leftField      Left part of the ON
     * @param   string|null     $operator       Operator for the ON
     * @param   string|null     $rightField     Right part of the ON
     * @return  $this|array                     $this on set, array on get
     * @throws  \InvalidArgumentException       On unknown $type
     */
    public function leftJoin($right = null, $leftField = null, $operator = null, $rightField = null)
    {
        return $this->applyJoin('LEFT', $right, $leftField, $operator, $rightField);
    }

    /**
     * Sets/Gets a LEFT JOIN.
     *
     *  $query->leftJoin('comments', 'users.id', '=', 'comment.user_id');
     *
     * @param   string|null     $right          Name of the table we're joining
     * @param   string|null     $leftField      Left part of the ON
     * @param   string|null     $operator       Operator for the ON
     * @param   string|null     $rightField     Right part of the ON
     * @return  $this|array                     $this on set, array on get
     * @throws  \InvalidArgumentException       On unknown $type
     */
    public function rightJoin($right = null, $leftField = null, $operator = null, $rightField = null)
    {
        return $this->applyJoin('RIGHT', $right, $leftField, $operator, $rightField);
    }

    /**
     * Adds in any kind of join. Used internally, you should use join(), leftJoin() and rightJoin()
     *
     * @param   string          $type           The type of join to add
     * @param   string|null     $right          Name of the right table
     * @param   string|null     $leftField      Left part of the ON
     * @param   string|null     $operator       Operator for the ON
     * @param   string|null     $rightField     Right part of the ON
     * @return  $this|array                     $this on set, array of joins for given type on get
     */
    protected function applyJoin($type, $right = null, $leftField = null, $operator = null, $rightField = null)
    {
        // Get:
        if ($right === null) {
            return (array_key_exists($type, $this->joins)) ? $this->joins[$type] : [];
        }

        // Set:
        $this->joins[$type][] = [
            'right'         => $right,
            'leftField'     => $leftField,
            'operator'      => $operator,
            'rightField'    => $rightField
        ];

        return $this;
    }

    /**
     * Builds the SQL for a JOIN statement
     *
     * @return  string
     */
    public function buildJoinSQL()
    {
        if (empty($this->joins)) {
            return '';
        }

        $joins = [];
        foreach ($this->joins as $type => $typeJoins) {
            foreach ($typeJoins as $j) {
                $join = ($type != 'INNER') ? $type . ' ' : '';
                $join .= 'JOIN ';
                $join .= $this->dialect->quoteTable($j['right']) . ' ON ';
                $join .= $this->dialect->quoteField($j['leftField']);
                $join .= ' ' . $j['operator'] . ' ';
                $join .= $this->dialect->quoteField($j['rightField']);
                $joins[] = $join;
            }
        }

        return trim(implode("\n", $joins));
    }

    /**
     * Resets the JOIN portion of this query to empty.
     *
     * @return  $this
     */
    public function resetJoins()
    {
        $this->joins = [];
        return $this;
    }

    /*
     * ------------------ GROUP BY -------------------
     */

    /**
     * Set/Get the group by clause
     *
     * @param   string|array|null     $clause     String or array to set, null to get
     * @return  $this|string|array
     */
    public function groupBy($clause = null)
    {
        if ($clause === null) {
            return $this->groupBy;
        }

        $clause = (is_array($clause)) ? $clause : [$clause];
        $this->groupBy = array_merge($this->groupBy, $clause);

        return $this;
    }

    /**
     * Builds the SQL for a GROUP BY statement
     *
     * @return  string
     */
    public function buildGroupBySQL()
    {
        if (empty($this->groupBy)) {
            return '';
        }

        $parts = [];
        foreach ($this->groupBy as $p) {
            $parts[] = $this->dialect->quoteField($p);
        }

        return 'GROUP BY ' . implode(', ', $parts);
    }

    /**
     * Resets the GROUP BY portion of this query to empty.
     *
     * @return  $this
     */
    public function resetGroupBy()
    {
        $this->groupBy = [];
        return $this;
    }

    /*
     * ---------------- ORDER BY ----------------------
     */

    /**
     * Set/Get the ORDER BY component of the query.
     *
     * @param   string|array|null       $field      Field name or an array of field => direction. Null to get
     * @param   string|null             $direction  ASC by default
     * @return  $this|array
     */
    public function orderBy($field = null, $direction = 'ASC')
    {
        if ($field === null) {
            return $this->orderBy;
        }

        if (is_array($field)) {
            foreach ($field as $f => $d) {
                $this->orderBy[] = [
                    'field' => $f,
                    'direction' => $d,
                ];
            }
        } else {
            $this->orderBy[] = [
                'field' => $field,
                'direction' => ($field instanceof ExpressionInterface) ? null : $direction
            ];
        }

        return $this;
    }

    /**
     * Builds the SQL for the ORDER BY part of the query
     *
     * @return  string
     */
    public function buildOrderBySQL()
    {
        if (empty($this->orderBy)) {
            return '';
        }

        $parts = [];
        foreach ($this->orderBy as $order) {
            $part = $this->dialect->quoteField($order['field']);
            $part .= ($order['direction'] != null) ? ' ' . $order['direction'] : '';
            $parts[] = $part;
        }

        return 'ORDER BY ' . implode(', ', $parts);
    }

    /**
     * Resets the ORDER BY portion of this query to empty.
     *
     * @return  $this
     */
    public function resetOrderBy()
    {
        $this->orderBy = [];
        return $this;
    }

    /*
     * --------------- Generating SQL --------------------
     */

    /**
     * Generates the full SQL statement for this query with all the composite parts.
     * Note: there is no guarantee that this will be valid SQL! Obviously the parts
     * you've given will come out good, but if you forget to add a FROM or something,
     * this class won't automatically guess one for you!
     *
     * @return  string
     */
    public function sql()
    {
        $candidateParts = [
            $this->buildSelectSQL(),
            $this->buildFromSQL(),
            $this->buildJoinSQL(),
            $this->buildWhereSQL($this->dialect),
            $this->buildGroupBySQL(),
            $this->buildHavingSQL($this->dialect),
            $this->buildOrderBySQL(),
            $this->buildPaginateSQL()
        ];

        $realParts = [];
        foreach ($candidateParts as $p) {
            if ($p != '') {
                $realParts[] = $p;
            }
        }

        return implode(" ", $realParts);
    }

    /**
     * Returns all the parameters, in the correct order, to pass into PDO.
     *
     * @return  array
     */
    public function params()
    {
        return array_merge($this->getWhereParams(), $this->getHavingParams());
    }

    /**
     * Resets the entire query by calling each sections resetXXX() function.
     *
     * @return  $this
     */
    public function reset()
    {
        return $this
            ->resetSelect()
            ->resetFrom()
            ->resetJoins()
            ->resetWhere()
            ->resetGroupBy()
            ->resetHaving()
            ->resetOrderBy()
            ->resetLimit()
            ->resetOffset();
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
        $tables = [];

        // Deal with FROMs:
        if ($this->fromTables) {
            foreach ($this->fromTables as $tbl) {
                $tables[] = $tbl['table'];
            }
        }

        // And now JOINs:
        if ($this->joins) {
            foreach ($this->joins as $_ => $joins) {
                foreach ($joins as $join) {
                    $tables[] = $join['right'];
                }
            }
        }

        return array_unique($tables);
    }
}
