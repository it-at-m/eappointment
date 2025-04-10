<?php

namespace BO\Zmsdb\Query\Builder;

/**
 * ConditionBuilder
 *
 * Builds up a set of conditions for a query, either used in a WHERE or
 * HAVING block of SQL.
 *
 * @package     BO\Zmsdb\Query\Builder
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
class ConditionBuilder
{
    /**
     * @var     array   "Parts" as in field, op, value
     */
    protected $parts = [];

    /**
     * @var     array   Params (used as a shortcut for feeding into PDO)
     */
    protected $params = [];

    /**
     * Adds an AND condition into the builder
     *
     * @param   string|\Closure     $field              Fieldname|callback for group
     * @param   string              $operator           Operator (=, !=, <>, <= etc)
     * @param   mixed               $value              Value to test against
     * @return  $this
     */
    public function andWith($field, $operator = null, $value = null)
    {
        $this->addCondition('AND', $field, $operator, $value);
        return $this;
    }

    /**
     * Adds an OR condition into the builder
     *
     * @param   string|\Closure     $field              Fieldname|callback for group
     * @param   string              $operator           Operator (=, !=, <>, <= etc)
     * @param   mixed               $value              Value to test against
     * @return  $this
     */
    public function orWith($field, $operator = null, $value = null)
    {
        $this->addCondition('OR', $field, $operator, $value);
        return $this;
    }

    /**
     * Adds the condition into the family we're building.
     *
     * @param   string              $join               AND or OR
     * @param   string|\Closure     $field              Fieldname|callback for group
     * @param   string              $operator           Operator (=, !=, <>, <= etc)
     * @param   mixed               $value              Value to test against
     * @return  $this               $this on set, array on get
     */
    protected function addCondition($join, $field, $operator, $value)
    {
        $newParams = [];
        if ($field instanceof \Closure) {
            // Return and merge the result of these queries
            $subQuery = new ConditionBuilder();
            $field($subQuery);
            $this->parts[] = [
                'join' => $join,
                'sub' => $subQuery->conditions()
            ];
            $newParams = $subQuery->getConditionParameters();
//            $this->params = array_merge($this->params, $subQuery->getConditionParameters());
        } else {
            $this->parts[] = [
                'join' => $join,
                'field' => $field,
                'operator' => $operator,
                'value' => $value
            ];
            $newParams = $value;
        }

        if (!is_array($newParams)) {
            $newParams = [$newParams];
        }

        $this->params = array_merge($this->params, $newParams);

        return $this;
    }

    /**
     * Returns the conditions that have been set on this builder.
     *
     * @return  array
     */
    public function conditions()
    {
        return $this->parts;
    }

    /**
     * Builds up the SQL for this condition.
     *
     * @param   DialectInterface    $dialect
     * @return  string
     */
    public function buildConditionSQL(DialectInterface $dialect)
    {
        return $this->buildPartsSQL($this->parts, $dialect);
    }

    /**
     * Builds up an array of parts into a SQL string. To be used recursively.
     *
     * @param   DialectInterface    $dialect
     * @param   array               $parts
     * @return  string
     */
    protected function buildPartsSQL(array $parts, DialectInterface $dialect)
    {
        $where = '';
        foreach ($parts as $c) {
            $where .= ' ' . $c['join'] . ' ';
            if (array_key_exists('sub', $c)) {
                $where .= '(';
                $where .= $this->buildPartsSQL($c['sub'], $dialect);
                $where .= ')';
            } else {
                $where .= $dialect->quoteField($c['field']) . ' ' . $c['operator'] . ' ';
                if (is_array($c['value'])) {
                    $inParts = [];
                    for ($i = 0; $i < count($c['value']); $i++) {
                        $inParts[] = '?';
                    }
                    $where .= '(' . implode(', ', $inParts) . ')';
                } else {
                    $where .= '?';
                }
            }
        }
        $where = trim(preg_replace('/^(AND|OR) /', '', trim($where)));
        return $where;
    }

    /**
     * Returns the parameters from this set of conditions ready to throw at a PDO statement
     *
     * @return  array
     */
    public function getConditionParameters()
    {
        return $this->params;
    }

    /**
     * Returns whether any conditions have been added to this builder
     *
     * @return  bool
     */
    public function hasConditions()
    {
        return !empty($this->parts);
    }
}
