<?php

namespace BO\Zmsbackend;

/**
 * @SuppressWarnings(NumberOfChildren)
 * @SuppressWarnings(Public)
 *
 */
abstract class Base
{
    /**
     * @var Connection\Pdo|null $writeDb Database connection
     */
    protected $writeDb = null;

    /**
     * @var Connection\Pdo|null $readDb Database connection
     */
    protected $readDb = null;

    /**
     * Cache prepared statements
     *
     */
    protected static $preparedCache = [];

    /**
     * Make sure, we do not cache statements on different connections
     *
     */
    protected static $preparedConnectionId = null;

    /**
     * @param Connection\Pdo|null $writeConnection
     * @param Connection\Pdo|null $readConnection
     */
    public function __construct(Connection\Pdo $writeConnection = null, Connection\Pdo $readConnection = null)
    {
        $this->writeDb = $writeConnection;
        $this->readDb = $readConnection;
    }

    public static function init(Connection\Pdo $writeConnection = null, Connection\Pdo $readConnection = null): static
    {
        $instance = new static($writeConnection, $readConnection);
        return $instance;
    }

    public function getWriter(): Connection\Pdo
    {
        if (null === $this->writeDb) {
            $this->writeDb = Connection\Select::getWriteConnection();
            $this->readDb = $this->writeDb;
        }
        return $this->writeDb;
    }

    public function getReader(): Connection\Pdo
    {
        if (null !== $this->writeDb) {
            // if readDB gets a reset, still use writeDB
            return $this->writeDb;
        }
        if (null === $this->readDb) {
            $this->readDb = Connection\Select::getReadConnection();
        }
        return $this->readDb;
    }

    public function fetchPreparedStatement(Query\Base|string $query)
    {
        $sql = "$query";
        $reader = $this->getReader();
        if (spl_object_hash($reader) != static::$preparedConnectionId) {
            // do not use prepared statements on a different connection
            static::$preparedCache = [];
            static::$preparedConnectionId = spl_object_hash($reader);
        }
        if (!isset(static::$preparedCache[$sql])) {
            $prepared = $this->getReader()->prepare($sql);
            static::$preparedCache[$sql] = $prepared;
        }
        return static::$preparedCache[$sql];
    }

    public function startExecute($statement, array $parameters)
    {
        $statement = static::pdoExceptionHandler(function () use ($statement, $parameters) {
            $statement->execute($parameters);
            return $statement;
        });
        return $statement;
    }

    protected static function pdoExceptionHandler(\Closure $pdoFunction, $parameters = [])
    {
        try {
            $statement = $pdoFunction($parameters);
        } catch (\PDOException $pdoException) {
            if (stripos($pdoException->getMessage(), 'Lock wait timeout') !== false) {
                throw new \BO\Zmsbackend\Exception\Pdo\LockTimeout();
            }
            //@codeCoverageIgnoreStart
            if (stripos($pdoException->getMessage(), 'Deadlock found') !== false) {
                throw new \BO\Zmsbackend\Exception\Pdo\DeadLockFound();
            }
            //@codeCoverageIgnoreEnd
            $message = "SQL: "
                . " Err: "
                . $pdoException->getMessage()
                //. " || Statement: "
                //.$statement->queryString
                //." || Parameters=". var_export($parameters, true)
                ;
            throw new \BO\Zmsbackend\Exception\Pdo\PDOFailed($message, 0, $pdoException);
        }
        return $statement;
    }

    public function fetchStatement(Query\Base $query)
    {
        $parameters = $query->getParameters();
        $statement = $this->startExecute($this->fetchPreparedStatement($query), $parameters);
        return $statement;
    }

    /**
     * @template T of \BO\Zmsentities\Schema\Entity
     * @param T $entity
     * @return T
     */
    public function fetchOne(Query\Base $query, \BO\Zmsentities\Schema\Entity $entity)
    {
        $statement = $this->fetchStatement($query);
        $data = $statement->fetch(\PDO::FETCH_ASSOC);
        if ($data) {
            $entity->exchangeArray($query->postProcessJoins($data));
            $entity->setResolveLevel($query->getResolveLevel());
        }
        $statement->closeCursor();
        return $entity;
    }

    public function fetchRow(Query\Base|string $query, array|null $parameters = null)
    {
        return static::pdoExceptionHandler(function () use ($query, $parameters) {
            $prepared = $this->fetchPreparedStatement($query);
            $prepared->execute($parameters);
            $row = $prepared->fetch(\PDO::FETCH_ASSOC);
            $prepared->closeCursor();
            return $row;
        });
    }

    public function fetchValue(Query\Base|string $query, array|null $parameters = null)
    {
        return static::pdoExceptionHandler(function () use ($query, $parameters) {
            $prepared = $this->fetchPreparedStatement($query);
            $prepared->execute($parameters);
            $value = $prepared->fetchColumn();
            $prepared->closeCursor();
            return $value;
        });
    }

    public function fetchAll(Query\Base|string $query, array|null $parameters = null)
    {
        return static::pdoExceptionHandler(function () use ($query, $parameters) {
            $prepared = $this->fetchPreparedStatement($query);
            $prepared->execute($parameters);
            $list = $prepared->fetchAll(\PDO::FETCH_ASSOC);
            $prepared->closeCursor();
            return $list;
        });
    }

    public function fetchHandle(Query\Base|string $query, array|null $parameters = null)
    {
        return static::pdoExceptionHandler(function () use ($query, $parameters) {
            $prepared = $this->fetchPreparedStatement($query);
            $prepared->execute($parameters);
            return $prepared;
        });
    }

    public function fetchList(Query\Base $query, \BO\Zmsentities\Schema\Entity $entity, array|\ArrayAccess $resultList = [])
    {
        $statement = $this->fetchStatement($query);
        while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $dataEntity = clone $entity;
            $dataEntity->exchangeArray($query->postProcessJoins($data));
            $dataEntity->setResolveLevel($query->getResolveLevel());
            $resultList[] = $dataEntity;
        }
        $statement->closeCursor();
        return $resultList;
    }

    /**
     * Write an Item to database - Insert, Update
     *
     * @param Query\Base $query
     * @return bool
     */
    public function writeItem(Query\Base $query)
    {
        return static::pdoExceptionHandler(function () use ($query) {
            $this->getWriter(); //Switch to writer for write/delete
            $statement = $this->fetchPreparedStatement($query);
            $status = $statement->execute($query->getParameters());
            $statement->closeCursor();
            return $status;
        });
    }

    /**
     * @param Query\Base $query
     * @return bool
     */
    public function deleteItem(Query\Base $query)
    {
        return $this->writeItem($query);
    }

    public function perform(string $sql, array|null $parameters = null)
    {
        return static::pdoExceptionHandler(function () use ($sql, $parameters) {
            $this->getWriter(); //Switch to writer for perform
            $prepared = $this->fetchPreparedStatement($sql);
            $status = $prepared->execute($parameters);
            $prepared->closeCursor();
            return $status;
        });
    }

    public function fetchAffected(string $sql, array|null $parameters = null)
    {
        return static::pdoExceptionHandler(function () use ($sql, $parameters) {
            $this->getWriter(); //Switch to writer for perform
            $prepared = $this->fetchPreparedStatement($sql);
            $prepared->execute($parameters);
            $count = $prepared->rowCount();
            $prepared->closeCursor();
            return $count;
        });
    }

    /**
     * @SuppressWarnings(Param)
     *
     * @codeCoverageIgnore
     *
     * @return \BO\Zmsentities\Schema\Entity
     */
    public function readResolvedReferences(\BO\Zmsentities\Schema\Entity $entity, int $resolveReferences)
    {
        return $entity;
    }

    /**
     * This function produces a hash value that contains info for comparison
     *
     * @param string $value
     * @param callable|null $callable function to be used for hashing
     * @return string
     */
    public function hashStringValue(string $value, callable $callable = null): string
    {
        if ($callable === null) {
            $callable = 'sha1';
        }

        if (is_callable($callable)) {
            $hash = $callable($value);

            if (is_string($callable)) {
                return $callable . ':' . $hash;
            }
            if ($callable instanceof \Closure) {
                return 'closure:' . $hash;
            }
            // else
            return 'custom:' . $hash;
        }

        throw new \InvalidArgumentException('hashStringValue() should be called with a callable as second parameter.');
    }
}
