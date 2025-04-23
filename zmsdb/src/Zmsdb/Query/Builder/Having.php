<?php

namespace BO\Zmsdb\Query\Builder;

/**
 * Having
 *
 * Adds in having(), orHaving() and groupings
 * into the SQL builder.
 *
 *      $query->having(function(ConditionBuilder $conditions) {
 *          $conditions
 *              ->andWith('user', '=', 'Alex')
 *              ->andWith('country', '=', 'GB');
 *      })
 *      ->orHaving(function(ConditionBuilder $conditions) {
 *          $conditions->andWith('user', '=', 'Lucie');
 *          $conditions->andWith('country', '=', 'CA');
 *      });
 *
 * Would generate:
 *
 *      HAVING (name = 'Alex' AND country = 'GB')
 *      OR (name = 'Lucie' AND country = 'CA')
 *
 * Unlike While, this trait is only used once in the Select query type
 * however it's of sufficient complexity to warrant it being split out.
 *
 * @package     BO\Zmsdb\Query\Builder
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
trait Having
{
    /**
     * @var     ConditionBuilder
     */
    protected $havingBuilder;

    /**
     * Get/Set an "AND HAVING" clause on the query. You can either pass a simple
     * comparison ('name', '=', 'Alex') or a function to append multiple queries
     * in a group.
     *
     * @param   string|\Closure|null    $field      Fieldname|callback for group|to return
     * @param   string|null             $operator   Operator (=, !=, <>, <= etc)
     * @param   mixed|null              $value      Value to test against
     * @return  $this|array                         $this on set, array on get
     */
    public function having($field = null, $operator = null, $value = null)
    {
        if (!isset($this->havingBuilder)) {
            $this->havingBuilder = new ConditionBuilder();
        }
        if ($field == null) {
            return $this->havingBuilder->conditions();
        }
        $this->havingBuilder->andWith($field, $operator, $value);
        return $this;
    }

    /**
     * Adds a new 'OR' predicate to the query.
     *
     * @param   string|\Closure|null    $field      Fieldname|callback for group|to return
     * @param   string|null             $operator   Operator (=, !=, <>, <= etc)
     * @param   mixed|null              $value      Value to test against
     * @return  $this|array                         $this on set, array on get
     */
    public function orHaving($field = null, $operator = null, $value = null)
    {
        if (!isset($this->havingBuilder)) {
            $this->havingBuilder = new ConditionBuilder();
        }
        if ($field == null) {
            return $this->havingBuilder->conditions();
        }
        $this->havingBuilder->orWith($field, $operator, $value);
        return $this;
    }

    /**
     * Returns the SQL string for the WHERE portion of the query
     *
     * @param   DialectInterface    $dialect
     * @return  string
     */
    public function buildHavingSQL(DialectInterface $dialect)
    {
        if (!isset($this->havingBuilder) || !$this->havingBuilder->hasConditions()) {
            return '';
        }

        return 'HAVING ' . $this->havingBuilder->buildConditionSQL($dialect);
    }

    /**
     * Returns an array of all the parameter that have been passed to having()
     * ready to be thrown at PDO.
     *
     * @return  array
     */
    public function getHavingParams()
    {
        return (isset($this->havingBuilder)) ? $this->havingBuilder->getConditionParameters() : [];
    }

    /**
     * Resets the HAVING portion of this query to empty.
     *
     * @return  $this
     */
    public function resetHaving()
    {
        unset($this->havingBuilder);
        $this->havingBuilder = new ConditionBuilder();
        return $this;
    }
}
