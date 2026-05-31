<?php

namespace BO\Zmsdb\Query\Builder;

/**
 * Table
 *
 * Used by INSERT and UPDATE to define which table to operate on.
 *
 * @package     BO\Zmsdb\Query\Builder
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
trait TableName
{
    /**
     * @var     array     Table to update
     */
    protected $table = null;

    /**
     * Set/Get the table we're updating
     *
     * @param string|null     $table      string to set, null to get
     *
     * @return Update|array
     */
    public function table($table = null): array|Update
    {
        if ($table === null) {
            return $this->table;
        }
        $this->table = $table;
        return $this;
    }
}
