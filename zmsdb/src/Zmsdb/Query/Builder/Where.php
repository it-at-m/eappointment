<?php

namespace BO\Zmsdb\Query\Builder;

/**
 * Where
 *
 * Adds in where(), orWhere() and groupings
 * into the SQL builder.
 *
 *      $query->where(function(ConditionBuilder $conditions) {
 *          $conditions
 *              ->andWith('user', '=', 'Alex')
 *              ->andWith('country', '=', 'GB');
 *      })
 *      ->orWhere(function(ConditionBuilder $conditions) {
 *          $conditions->andWith('user', '=', 'Lucie');
 *          $conditions->andWith('country', '=', 'CA');
 *      });
 *
 * Would generate:
 *
 *      WHERE (name = 'Alex' AND country = 'GB')
 *      OR (name = 'Lucie' AND country = 'CA')
 *
 * @package     BO\Zmsdb\Query\Builder
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
trait Where
{
    /**
     * @var     ConditionBuilder
     */
    protected $whereBuilder;

    /**
     * Get/Set an "AND WHERE" clause on the query. You can either pass a simple
     * comparison ('name', '=', 'Alex') or a function to append multiple queries
     * in a group.
     *
     * @param   string|\Closure|null    $field      Fieldname|callback for group|to return
     * @param   string|null             $operator   Operator (=, !=, <>, <= etc)
     * @param   mixed|null              $value      Value to test against
     * @return  $this|array                         $this on set, array on get
     */
    public function where($field = null, $operator = null, $value = null)
    {
        if (!isset($this->whereBuilder)) {
            $this->whereBuilder = new ConditionBuilder();
        }
        if ($field == null) {
            return $this->whereBuilder->conditions();
        }
        $this->whereBuilder->andWith($field, $operator, $value);
        return $this;
    }

    /**
     * Adds a new 'OR ' predicate to the query.
     *
     * @param   string|\Closure|null    $field      Fieldname|callback for group|to return
     * @param   string|null             $operator   Operator (=, !=, <>, <= etc)
     * @param   mixed|null              $value      Value to test against
     * @return  $this|array                         $this on set, array on get
     */
    public function orWhere($field = null, $operator = null, $value = null)
    {
        if (!isset($this->whereBuilder)) {
            $this->whereBuilder = new ConditionBuilder();
        }
        if ($field == null) {
            return $this->whereBuilder->conditions();
        }
        $this->whereBuilder->orWith($field, $operator, $value);
        return $this;
    }

    public function whereIn(string $field, array $values)
    {
        if (empty($values)) {
            throw new \InvalidArgumentException('whereIn() requires a non-empty $values array.');
        }
        return $this->where($field, 'IN', array_values($values));
    }

    /**
     * Returns the SQL string for the WHERE portion of the query
     *
     * @param   DialectInterface    $dialect
     * @return  string
     */
    public function buildWhereSQL(DialectInterface $dialect)
    {
        if (!isset($this->whereBuilder) || !$this->whereBuilder->hasConditions()) {
            return '';
        }

        return 'WHERE ' . $this->whereBuilder->buildConditionSQL($dialect);
    }

    /**
     * Returns an array of all the parameter that have been passed to where()
     * ready to be thrown at PDO.
     *
     * @return  array
     */
    public function getWhereParams()
    {
        return (isset($this->whereBuilder)) ? $this->whereBuilder->getConditionParameters() : [];
    }

    /**
     * Resets the WHERE portion of this query to empty.
     *
     * @return  $this
     */
    public function resetWhere()
    {
        unset($this->whereBuilder);
        $this->whereBuilder = new ConditionBuilder();
        return $this;
    }
}
