<?php

namespace BO\Zmsbackend\Query;

use BO\Zmsbackend\Query\Builder\Select;
use BO\Zmsbackend\Query\Builder\Insert;
use BO\Zmsbackend\Query\Builder\Update;
use BO\Zmsbackend\Query\Builder\Delete;
use BO\Zmsbackend\Query\Builder\Dialect\MySQL;
use BO\Zmsbackend\Query\Builder\Expression;
use BO\Zmsbackend\Query\Builder\Query as QueryBuilder;

/**
 * Base class to construct entity specific queries
 * Usually used with the interface MappingInterface
 * Further, it allows to react to resolveReferences as parameter to calling methods
 */

/**
 * @SuppressWarnings(NumberOfChildren)
 * @SuppressWarnings(Complexity)
 *
 */
abstract class Base
{
    /**
     * Identifier for the type of query
     */
    const string SELECT = 'SELECT';
    const string INSERT = 'INSERT';
    const string UPDATE = 'UPDATE';
    const string REPLACE = 'REPLACE';
    const string DELETE = 'DELETE';

    /**
     * Name of table in DB
     */
    const TABLE = null;
    /**
     * Alias used to access TABLE
     */
    const ALIAS = null;

    /**
     * Concrete builder instance (Select|Insert|Update|Delete), always set in the constructor.
     */
    protected QueryBuilder $query;

    /**
     * @var String $query
     */
    protected $prefix = '';

    /**
     * Name of the query used for caching
     *
     */
    protected $name = false;

    /**
     * Level given ususally by parameter resolveReferences
     *
     */
    protected $resolveLevel = null;

    protected static $sqlCache = [];

    protected $currentSqlString = null;

    /**
     * List of joined aliasnames to avoid double joins
     *
     */
    protected $joinedAliasList = [];

    /**
     * List of joined queries to avoid double joins
     *
     */
    protected $joinedQueryList = [];

    protected $withEntities = [];

    /**
     * Create query builder if necessary
     *
     * @param Mixed $queryType one of the constants for a query type or of instance \BO\Zmsbackend\Query\Builder\Query
     * @param String $prefix If used in a subquery, prefix results with this string
     * @param string|false $name A named query has a cached SQL as soon as called first
     * @param int|null $resolveLevel
     * @param array $withEntities
     */
    public function __construct(
        $queryType,
        $prefix = '',
        string|false $name = false,
        mixed $resolveLevel = null,
        array $withEntities = []
    ) {
        $this->prefix = $prefix;
        $this->name = $name;
        $this->withEntities = $withEntities;
        $this->setResolveLevel($resolveLevel);
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
            $this->query->queryBaseStatement('DELETE ' . $this::getAlias() . ' FROM');
            $this->addTableAlias();
        } elseif ($queryType instanceof self) {
            $this->query = $queryType->query;
            // Share join-alias state with the parent query (intentional by-ref).
            /** @psalm-suppress UnsupportedPropertyReferenceUsage */
            $this->joinedAliasList =& $queryType->joinedAliasList;
            $this->resolveLevel = $queryType->resolveLevel - 1;
        } elseif ($queryType instanceof QueryBuilder) {
            $this->query = $queryType;
        } else {
            throw new \InvalidArgumentException(
                'Unsupported query type for ' . static::class . ': '
                . (is_object($queryType) ? $queryType::class : gettype($queryType))
            );
        }
        if ($this->query instanceof Select) {
            $this->addRequiredJoins();
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function __toString()
    {
        if ($this->name) {
            $name = $this->name . '_' . $this->prefix . (string) $this->resolveLevel;
            if (!isset(static::$sqlCache[$name])) {
                static::$sqlCache[$name] = $this->getSql();
            }
            return static::$sqlCache[$name];
        }
        if ($this->currentSqlString) {
            $sql = $this->currentSqlString;
        } else {
            $sql = $this->getSql();
        }
        return $sql;
    }

    public function getName()
    {
        return $this->name ? $this->name : get_class($this);
    }

    /**
     * Add the from part to the queryBaseStatement
     * This implementation tries to guess the syntax using the constant TABLE in the class
     * Override the method for a special implementation or required joins
     */
    protected function addSelect(): static
    {
        $table = $this::getTablename();
        $alias = $this::getAlias();
        $this->query->from($table, $alias);
        return $this;
    }

    public function setDistinctSelect(): void
    {
        $this->query->queryBaseStatement('SELECT DISTINCT');
    }

    public function setResolveLevel(int|null $resolveLevel): static
    {
        if ($resolveLevel !== null) {
            $this->resolveLevel = $resolveLevel;
        }
        return $this;
    }

    public function getResolveLevel()
    {
        if (null === $this->resolveLevel) {
            throw new \Exception("Required setting for resolveReferenceLevel missing in " . get_class($this));
        }
        return $this->resolveLevel;
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
        $alias = constant($class . '::ALIAS');
        if (null === $alias) {
            $alias = lcfirst(preg_replace('#^.*\\\#', '', $class));
        }
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
     */
    protected function addTable(): static
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
     */
    protected function addTableAlias(): static
    {
        $table = $this::getTablename();
        $alias = $this::getAlias();
        $this->query->table(self::expression($table . ' ' . $alias));
        return $this;
    }

    /**
     * Add joins to table if required
     * Override this method if join are required for a select
     *
     * @return void
     */
    protected function addRequiredJoins()
    {
    }

    /**
     * resolves references by joining tables defined in the method addJoin()
     *
     * @param  Int $depth Number of levels of sub references to resolve
     */
    public function addResolvedReferences($depth): static
    {
        $this->setResolveLevel($depth);
        if ($depth > 0) {
            $queryList = $this->addJoin();
            foreach ($queryList as $query) {
                $query->setResolveLevel($depth);
                $query->setWithEntities($this->withEntities);
                $query->addResolvedReferences($depth - 1);
                $query->addEntityMapping();
            }
            $this->joinedQueryList = $queryList;
        } else {
            $this->addReferenceMapping();
        }
        return $this;
    }

    /**
     * @psalm-api
     */
    public function setWithEntities($withEntities = []): void
    {
        $this->withEntities = $withEntities;
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

    protected function leftJoin(Alias $alias, string|Expression|null $left = null, string|null $operator = null, string|null $right = null): QueryBuilder
    {
        $aliasId = $alias->getAliasIdentifier();
        if (!in_array($aliasId, $this->joinedAliasList)) {
            $this->joinedAliasList[] = $aliasId;
            $this->query->leftJoin($alias, $left, $operator, $right);
        } else {
            //throw new \Exception("Tried to add Alias ".$aliasId);
        }
        return $this->query;
    }

    protected function innerJoin(Alias $alias, string|null $left = null, string|null $operator = null, string|null $right = null): QueryBuilder
    {
        $aliasId = $alias->getAliasIdentifier();
        if (!in_array($aliasId, $this->joinedAliasList)) {
            $this->joinedAliasList[] = $aliasId;
            $this->query->join($alias, $left, $operator, $right);
        }
        return $this->query;
    }

    /**
     * get SQL-String
     * Implement a simple caching routine to prevent multiple rebuilds
     *
     * @return string
     */
    public function getSql()
    {
        $this->currentSqlString = (string)$this->query;
        return $this->currentSqlString;
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

    /**
     * @return array
     *
     */
    public function getReferenceMapping()
    {
        return [
        ];
    }

    /**
     * Shortcut to create an SQL-Expression without quoting
     *
     * @return \BO\Zmsbackend\Query\Builder\Expression
     */
    protected static function expression(string $string)
    {
        return new Expression($string);
    }

    /**
     * Add a select part to the query containing a mapping from the db schema to the entity schema
     *
     * @param mixed $type
     * @return static
     */
    public function addEntityMapping(mixed $type = null): static
    {
        // Concrete query classes provide getEntityMapping(); not all declare MappingInterface.
        /** @psalm-suppress UndefinedMethod, TooManyArguments */
        $entityMapping = $this->getPrefixedList($this->getEntityMapping($type));
        $this->query->select($entityMapping);
        return $this;
    }

    protected function getPrefixed($prefix): string
    {
        return $this->prefix . $prefix;
    }

    protected function getPrefixedList(array $unprefixedList): array
    {
        $prefixed = [];
        foreach ($unprefixedList as $key => $value) {
            $prefixed[$this->getPrefixed($key)] = $value;
        }
        return $prefixed;
    }

    /**
     * Add a select part to the query containing references if no resolveReferences is given
     */
    protected function addReferenceMapping(): static
    {
        $referenceMapping = $this->getPrefixedList($this->getReferenceMapping());
        $this->query->select($referenceMapping);
        return $this;
    }

    public function addLimit($count, $offset = null): static
    {
        $this->query->limit($count);
        if ($offset) {
            $this->query->offset($offset);
        }
        return $this;
    }

    /**
     * Add values to a insert or update query
     */
    public function addValues(array $values): static
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

    /**
     * postProcess data including joined queries if necessary
     *
     * @param (null|scalar)[] $data
     *
     */
    public function postProcessJoins(array $data)
    {
        $data = $this->postProcess($data);
        foreach ($this->joinedQueryList as $query) {
            $data = $query->postProcess($data);
        }
        return $data;
    }

    public function shouldLoadEntity(string $name): bool
    {
        if (empty($this->withEntities)) {
            return true;
        }

        return in_array($name, $this->withEntities);
    }
}
