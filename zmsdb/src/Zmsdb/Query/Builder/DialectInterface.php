<?php

namespace BO\Zmsdb\Query\Builder;

interface DialectInterface
{
    /**
     * Quotes a table name correctly as per this engines dialect.
     *
     * @param   string  $table
     * @return  string
     */
    public function quoteTable($table);

    /**
     * Correctly quotes a field name, either in "name" or "table.name" format.
     *
     * @param   string  $field
     * @return  string
     */
    public function quoteField($field);
}
