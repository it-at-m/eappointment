<?php

namespace BO\Zmsdb\Query\Builder;

use BO\Zmsdb\Query\Builder\Dialect\ANSI;

/**
 * Query
 *
 * Base Query class that all other query types should inherit from.
 *
 * @package     BO\Zmsdb\Query\Builder
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
abstract class Query
{
    /**
     * @var     DialectInterface
     */
    protected $dialect;

    /**
     * @var     string      The base part of the query (SELECT, INSERT etc)
     */
    protected $queryBase = null;

    /**
     * @var     array   Flags for this query
     */
    protected $flags = [];

    /**
     * Pass in a dialect, otherwise it'll assume ANSI SQL.
     *
     * @param   DialectInterface|null    $dialect
     */
    public function __construct(?DialectInterface $dialect = null)
    {
        $this->dialect = ($dialect === null) ? new ANSI() : $dialect;
    }

    /**
     * Gets/sets the query base for this query.
     * Note: This will NOT be escaped in any way! Be super careful what you pass.
     *
     * @param   null|string     $base   Null to get, string to set
     * @return  string|$this    String on get, $this on set
     */
    public function queryBaseStatement($base = null)
    {
        if ($base === null) {
            return $this->queryBase;
        }
        $this->queryBase = $base;
        return $this;
    }

    /**
     * Get/set the dialect in use for this query.
     *
     * @param   null|DialectInterface   $dialect    Null to get, DialectInterface to set
     * @return  DialectInterface|$this  DialectInterface on get, $this on set.
     */
    public function dialect(DialectInterface $dialect = null)
    {
        if ($dialect === null) {
            return $this->dialect;
        }
        $this->dialect = $dialect;
        return $this;
    }

    /**
     * Get/Set a flag for the query. Flags are any metadata that you want passed around with
     * the query, but not necessarily used within the generation of the SQL. For example you
     * might put a cache TTL in there, or some engine specific flag for your database adapter
     * to later read and use.
     *
     * Any type can be used as the flag value.
     *
     * @param   string      $flag   Name of the flag
     * @param   null|mixed  $value  Null to get the flag, mixed to set it
     * @return  mixed|$this Flag value on read (null for not set), or $this on set.
     */
    public function flag($flag, $value = null)
    {
        if ($value === null) {
            return (array_key_exists($flag, $this->flags)) ? $this->flags[$flag] : null;
        }
        $this->flags[$flag] = $value;
        return $this;
    }

    /**
     * Gets/Sets multiple flags for the query. Get returns all flags as a key => value array.
     * Set accepts a key => value array.
     *
     * @param   null|array      $flags  Null for get, array for set
     * @return  array|$this     array on get, $this on set
     * @see     Query::flag() for more info on flags.
     */
    public function flags(?array $flags = null)
    {
        if (!is_array($flags)) {
            return $this->flags;
        }
        foreach ($flags as $f => $v) {
            $this->flags[$f] = $v;
        }
        return $this;
    }

    /**
     * Deletes a flag from the query.
     *
     * @param   string  $flag   Name of the flag
     * @return  $this
     */
    public function deleteFlag($flag)
    {
        unset($this->flags[$flag]);
        return $this;
    }

    /**
     * Generates the full SQL statement for this query with all the composite parts.
     *
     * @return  string
     */
    abstract public function sql();

    /**
     * Serves as a shortcut for sql()
     *
     * @return  string
     */
    public function __toString()
    {
        return $this->sql();
    }

    /**
     * Returns all the parameters, in the correct order, to pass into PDO.
     *
     * @return  array
     */
    abstract public function params();

    /**
     * Resets the entire query.
     *
     * @return  $this
     */
    abstract public function reset();

    /**
     * Returns all the tables that this query makes mention of, in FROMs and JOINs
     *
     * @return  array
     */
    abstract public function allTablesReferenced();
}
