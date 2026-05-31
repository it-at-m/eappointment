<?php

namespace BO\Zmsdb;

/**
 * @SuppressWarnings(NumberOfChildren)
 * @SuppressWarnings(Public)
 *
 */
abstract class Base
{
    /**
     * @var \PDO $writeDb Database connection
     */
    protected $writeDb = null;

    /**
     * @var \PDO $readDb Database connection
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
     * @var null|string
     */
    protected static string|null $preparedConnectionId = null;

    /**
     * @param \PDO $writeConnection
     * @param \PDO $readConnection
     */
    public function __construct(\PDO $writeConnection = null, \PDO $readConnection = null)
    {
        $this->writeDb = $writeConnection;
        $this->readDb = $readConnection;
    }

    public static function init(\PDO $writeConnection = null, \PDO $readConnection = null): static
    {
        $instance = new static($writeConnection, $readConnection);
        return $instance;
    }

    /**
     * @return Connection\PdoInterface|\PDO
     */
    public function getWriter(): \PDO|Connection\PdoInterface
    {
        if (null === $this->writeDb) {
            $this->writeDb = Connection\Select::getWriteConnection();
            $this->readDb = $this->writeDb;
        }
        return $this->writeDb;
    }

    /**
     * @return Connection\PdoInterface|\PDO
     */
    public function getReader(): \PDO|Connection\PdoInterface
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

    public function fetchPreparedStatement(Query\Base $query)
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
                throw new Exception\Pdo\LockTimeout();
            }
            //@codeCoverageIgnoreStart
            if (stripos($pdoException->getMessage(), 'Deadlock found') !== false) {
                throw new Exception\Pdo\DeadLockFound();
            }
            //@codeCoverageIgnoreEnd
            $message = "SQL: "
                . " Err: "
                . $pdoException->getMessage()
                //. " || Statement: "
                //.$statement->queryString
                //." || Parameters=". var_export($parameters, true)
                ;
            throw new Exception\Pdo\PDOFailed($message, 0, $pdoException);
        }
        return $statement;
    }

    public function fetchStatement(Query\Base $query)
    {
        $parameters = $query->getParameters();
        $statement = $this->startExecute($this->fetchPreparedStatement($query), $parameters);
        return $statement;
    }

    public function fetchOne(Query\Base $query, \BO\Zmsentities\Schema\Entity $entity): \BO\Zmsentities\Schema\Entity
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

    /**
     * @param (mixed|string)[]|null $parameters
     *
     * @psalm-param array{scopeID?: mixed, availabilityID?: mixed, year?: string, month?: string, day?: string, time?: mixed}|null $parameters
     */
    public function fetchRow(string $query, array|null $parameters = null)
    {
        return static::pdoExceptionHandler(function () use ($query, $parameters) {
            $prepared = $this->fetchPreparedStatement($query);
            $prepared->execute($parameters);
            $row = $prepared->fetch(\PDO::FETCH_ASSOC);
            $prepared->closeCursor();
            return $row;
        });
    }

    /**
     * @param (int|mixed|string)[]|null $parameters
     *
     * @psalm-param array{0?: mixed, from?: string, until?: string, entityName?: mixed, entityId?: mixed, groupName?: mixed, name?: mixed, processId?: int}|null $parameters
     */
    public function fetchValue(string $query, array|null $parameters = null)
    {
        return static::pdoExceptionHandler(function () use ($query, $parameters) {
            $prepared = $this->fetchPreparedStatement($query);
            $prepared->execute($parameters);
            $value = $prepared->fetchColumn();
            $prepared->closeCursor();
            return $value;
        });
    }

    /**
     * @param (mixed|string)[]|null $parameters
     *
     * @psalm-param array{scopeid?: string, generalSearch?: string, start?: string, end?: string, ua_yes?: '%Sachbearbeiter*in%', ua_system?: '%Sachbearbeiter*in\":\"_system_%', from?: string, until?: string, updatedAfter?: string, scopeID?: mixed, year?: string, month?: string, day?: string}|null $parameters
     */
    public function fetchAll(string $query, array|null $parameters = null)
    {
        return static::pdoExceptionHandler(function () use ($query, $parameters) {
            $prepared = $this->fetchPreparedStatement($query);
            $prepared->execute($parameters);
            $list = $prepared->fetchAll(\PDO::FETCH_ASSOC);
            $prepared->closeCursor();
            return $list;
        });
    }

    /**
     * @param (int|mixed)[]|null $parameters
     *
     * @psalm-param array{slotType: mixed, forceRequiredSlots: int}|null $parameters
     */
    public function fetchHandle(string $query, array|null $parameters = null)
    {
        return static::pdoExceptionHandler(function () use ($query, $parameters) {
            $prepared = $this->fetchPreparedStatement($query);
            $prepared->execute($parameters);
            return $prepared;
        });
    }

    /**
     * @param \BO\Zmsentities\Collection\EventLogList|\BO\Zmsentities\Collection\QueueList|array $resultList
     */
    public function fetchList(Query\Base $query, \BO\Zmsentities\Schema\Entity $entity, array|\BO\Zmsentities\Collection\EventLogList|\BO\Zmsentities\Collection\QueueList $resultList = [])
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

    /**
     * @param (mixed|string)[]|null $parameters
     *
     * @psalm-param array{email?: mixed, departmentId?: mixed, sendEmailReminderEnabled?: mixed, sendEmailReminderMinutesBefore?: mixed, availabilityID?: mixed, providedDate?: mixed|string}|null $parameters
     */
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
     * @SuppressWarnings (Param)
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
     * @param callable|NULL $c (function to be used for hashing)
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
