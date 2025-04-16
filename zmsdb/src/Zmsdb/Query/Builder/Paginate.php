<?php

namespace BO\Zmsdb\Query\Builder;

/**
 * Paginate
 *
 * Allows pagination of results with a limit() and offset().
 * This works across database engines, abstracting away the differences.
 *
 * @package     BO\Zmsdb\Query\Builder
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
trait Paginate
{
    /**
     * @var     int     Limit or results per page
     */
    protected $limit = null;

    /**
     * @var     int     Offset
     */
    protected $offset = 0;

    /**
     * Set/Get the limit of the query
     *
     * @param   int|null    $limit  Int to set, null to get
     * @return  $this|int
     */
    public function limit($limit = null)
    {
        if ($limit === null) {
            return $this->limit;
        }

        $this->limit = (int)$limit;
        return $this;
    }

    /**
     * Set/Get the offset of the query
     *
     * @param   int|null    $offset     Int to set, null to get
     * @return  $this|int
     */
    public function offset($offset = null)
    {
        if ($offset === null) {
            return $this->offset;
        }

        $this->offset = (int)$offset;
        return $this;
    }

    /**
     * Builds the SQL for the pagination, based on the DB engine
     *
     * @return  string
     */
    public function buildPaginateSQL()
    {
        if ($this->limit === null) {
            return '';
        }

        return 'LIMIT ' . (($this->offset != 0) ? $this->offset . ', ' . $this->limit : $this->limit);
    }

    /**
     * Resets the LIMIT portion of this query to none.
     *
     * @return  $this
     */
    public function resetLimit()
    {
        $this->limit = null;
        return $this;
    }

    /**
     * Resets the OFFSET portion of this query to 0.
     *
     * @return  $this
     */
    public function resetOffset()
    {
        $this->offset = 0;
        return $this;
    }
}
