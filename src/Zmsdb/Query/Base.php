<?php

namespace BO\Zmsdb\Query;

use \Solution10\SQL\Select;
use \Solution10\SQL\Insert;
use \Solution10\SQL\Update;
use \Solution10\SQL\Delete;
use \Solution10\SQL\Dialect\MySQL;
use \Solution10\SQL\Expression;

/**
 * Base class to construct entity specific queries
 * Usually used with the interface MappingInterface
 * Further, it allows to react to resolveReferences as parameter to calling methods
 */

/**
 * @SuppressWarnings(NumberOfChildren)
 *
 */
abstract class Base
{
    /**
     * Identifier for the type of query
     */
    const SELECT = 'SELECT';
    const INSERT = 'INSERT';
    const UPDATE = 'UPDATE';
    const REPLACE = 'REPLACE';
    const DELETE = 'DELETE';

    /**
     * @var \Solution10\SQL\Query $query
     */
    protected $query = null;

    /**
     * @var String $query
     */
    protected $prefix = '';

    /**
     * Create query builder if necessary
     *
     * @param Mixed $queryType one of the constants for a query type or of instance \Solution10\SQL\Query
     */
    public function __construct($queryType, $prefix = '')
    {
        $this->prefix = $prefix;
        $dialect = new MySQL();
        if (self::SELECT === $queryType) {
            $this->query = new Select($dialect);
            $this->addSelect();
        } elseif (self::INSERT === $queryType) {
            $this->query = new Insert($dialect);
            $this->addTable();
        } elseif (self::UPDATE === $queryType) {
            $this->query = new Update($dialect);
            $this->addTableAlias();
        } elseif (self::REPLACE === $queryType) {
            $this->query = new INSERT($dialect);
            $this->query->queryBaseStatement('REPLACE INTO');
            $this->addTable();
        } elseif (self::DELETE === $queryType) {
            $this->query = new Delete($dialect);
            $this->query->queryBaseStatement('DELETE '. $this::getAlias() .' FROM');
            $this->addTableAlias();
        } elseif ($queryType instanceof \Solution10\SQL\Query) {
            $this->query = $queryType;
        }
        if ($this->query instanceof Select) {
            $this->addRequiredJoins();
        }
    }

    /**
     * Add the from part to the queryBaseStatement
     * This implementation tries to guess the syntax using the constant TABLE in the class
     * Override the method for a special implementation or required joins
     *
     * @return self
     */
    protected function addSelect()
    {
        $table = $this::getTablename();
        $alias = $this::getAlias();
        $this->query->from($table, $alias);
        return $this;
    }

    /**
     * Add the alias part to the queryBaseStatement
     * This implementation tries to guess the syntax using the constant TABLE in the class
     * Override the method for a special implementation or required joins
     *
     * @return self
     */
    public static function getAlias()
    {
        $class = get_called_class();
        $alias = lcfirst(preg_replace('#^.*\\\#', '', $class));
        return $alias;
    }

    /**
     * Get the table name for the query
     *
     * @return string
     */
    public static function getTablename()
    {
        $class = get_called_class();
        $table = constant($class . '::TABLE');
        return $table;
    }

    /**
     * Add the from part to the queryBaseStatement
     * This implementation tries to guess the syntax using the constant TABLE in the class
     * Override the method for a special implementation or required joins
     *
     * @return self
     */
    protected function addTable()
    {
        $table = $this::getTablename();
        $alias = $this::getAlias();
        $this->query->table($table, $alias);
        return $this;
    }

    /**
     * Add the from part to the queryBaseStatement
     * This implementation tries to guess the syntax using the constant TABLE in the class
     * Override the method for a special implementation or required joins
     *
     * @return self
     */
    protected function addTableAlias()
    {
        $table = $this::getTablename();
        $alias = $this::getAlias();
        $this->query->table(self::expression($table .' '. $alias));
        return $this;
    }

    /**
     * Add joins to table if required
     * Override this method if join are required for a select
     */
    protected function addRequiredJoins()
    {
    }

    /**
     * resolves references by joining tables defined in the method addJoin()
     *
     * @param  Int $depth Number of levels of sub references to resolve
     * @return self
     */
    public function addResolvedReferences($depth)
    {
        if ($depth > 0) {
            $queryList = $this->addJoin();
            foreach ($queryList as $query) {
                $query->addResolvedReferences($depth - 1);
                $query->addEntityMapping();
            }
        } else {
            $this->addReferenceMapping();
        }
        return $this;
    }

    /**
     * If resolveReferences is required, override this method
     *
     * @return Array of self
     */
    protected function addJoin()
    {
        return [];
    }

    /**
     * get SQL-String
     *
     * @return String
     */
    public function getSql()
    {
        return (string)$this->query;
    }

    /**
     * List of parameters to use for a prepared statement
     *
     * @return Array
     */
    public function getParameters()
    {
        return $this->query->params();
    }

    public function getReferenceMapping()
    {
        return [
        ];
    }

    /**
     * Shortcut to create an SQL-Expression without quoting
     *
     * @return \Solution10\SQL\Expression
     */
    protected static function expression($string)
    {
        return new Expression($string);
    }

    /**
     * Add a select part to the query containing a mapping from the db schema to the entity schema
     *
     * @return self
     */
    public function addEntityMapping($type = null)
    {
        $entityMapping = $this->getPrefixedList($this->getEntityMapping($type));
        $this->query->select($entityMapping);
        return $this;
    }

    protected function getPrefixed($prefix)
    {
        return $this->prefix . $prefix;
    }

    protected function getPrefixedList($unprefixedList)
    {
        $prefixed = [];
        foreach ($unprefixedList as $key => $value) {
            $prefixed[$this->getPrefixed($key)] = $value;
        }
        return $prefixed;
    }

    /**
     * Add a select part to the query containing references if no resolveReferences is given
     *
     * @return self
     */
    protected function addReferenceMapping()
    {
        $referenceMapping = $this->getPrefixedList($this->getReferenceMapping());
        $this->query->select($referenceMapping);
        return $this;
    }

    public function addLimit($count)
    {
        $this->query->limit($count);
        return $this;
    }

    /**
     * Add values to a insert or update query
     *
     * @return self
     */
    public function addValues($values)
    {
        $this->query->values($values);
        return $this;
    }

    /**
     * postProcess data if necessary
     *
     */
    public function postProcess($data)
    {
        return $data;
    }
}
