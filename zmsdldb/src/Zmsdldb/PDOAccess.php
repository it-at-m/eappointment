<?php

namespace BO\Zmsdldb;

abstract class PDOAccess extends AbstractAccess
{
    /**
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    protected mixed $accessorClassName = [];

    protected array $accessorNamesPlural = [
        'Authority' => 'Authorities',
        'Borough' => 'Boroughs',
        'Link' => 'Links',
        'Location' => 'Locations',
        'Office' => 'Offices',
        'Service' => 'Services',
        'Setting' => 'Settings',
        'Topic' => 'Topics'
    ];

    protected ?\PDO $pdo;

    protected string $engine = 'SQLite';


    public function __construct(array $options)
    {
        try {
            $parts = explode("\\", static::class);
            $this->engine = str_replace('Access', '', end($parts));

            $accessorNameKeys = array_keys($this->accessorNamesPlural);
            $accessorClassName = array_flip($this->accessorNamesPlural);

            $this->accessorClassName = array_merge(
                $accessorClassName,
                array_combine($accessorNameKeys, $accessorNameKeys)
            );
            $this->accessorNamesPlural = array_merge(
                $this->accessorNamesPlural,
                array_flip($this->accessorNamesPlural)
            );

            if (isset($options['pdoConnection']) && $options['pdoConnection'] instanceof \PDO) {
                $this->pdo = $options['pdoConnection'];
            } else {
                $this->connect($options);
            }
            $this->requirePdo()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->postConnect();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    #[\Override]
    public function __call(mixed $method, array $args = [])
    {
        try {
            return parent::__call($method, $args);
        } catch (\Exception $e) {
            if ('loadFromPath' != $method && preg_match('/load(?P<accessor>[A-Za-z_0-9]+)/', $method, $matches)) {
                $locale = $args[0] ?? 'de';
                $instance = $this->loadAccessor($matches['accessor'], $locale);

                return $instance;
            }
        }
    }

    /**
     * @return void
     */
    protected function postConnect()
    {
    }

    public function loadAccessor(string $name, string $locale = 'de'): mixed
    {
        if (isset($this->accessorClassName[$name])) {
            if (null === $this->accessInstance[$locale][$this->accessorClassName[$name]]) {
                $accessorClass = __NAMESPACE__ . '\\' . $this->engine . '\\' . $this->accessorClassName[$name];

                $instance = new $accessorClass($this, $locale);
                $this->accessInstance[$locale][$name] = $instance;
                $this->accessInstance[$locale][$this->accessorNamesPlural[$name]] = $instance;
            }
            return $this->accessInstance[$locale][$name];
        }
        throw new \Exception('Invalid accessor');
    }

    abstract protected function connect(array $options): void;

    protected function requirePdo(): \PDO
    {
        if (!$this->pdo instanceof \PDO) {
            throw new \RuntimeException('PDO connection is not initialized');
        }
        return $this->pdo;
    }

    /** @psalm-api */
    public function getConnection(): mixed
    {
        return $this->pdo;
    }

    /**
     * parameters see https://www.php.net/manual/de/pdo.query.php
     */

    public function query(mixed ...$args): mixed
    {
        try {
            return $this->requirePdo()->query(...$args);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * parameters see https://www.php.net/manual/de/pdo.exec.php
     */
    public function exec(string ...$args): mixed
    {
        try {
            return $this->requirePdo()->exec(...$args);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * parameters see https://www.php.net/manual/de/pdo.prepare.php
     */
    public function prepare(mixed ...$args): mixed
    {
        try {
            return $this->requirePdo()->prepare(...$args);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected int $transactionCount = 0;

    public function beginTransaction(): mixed
    {
        try {
            $this->transactionCount++;
            if ($this->inTransaction()) {
                return true;
            }
            return $this->requirePdo()->beginTransaction();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function commit(): mixed
    {
        try {
            $this->transactionCount--;
            if ($this->transactionCount == 0 && $this->inTransaction()) {
                return $this->requirePdo()->commit();
            } elseif (!$this->inTransaction()) {
                trigger_error(__METHOD__ . ' no transaction started');
            }
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function rollBack(): mixed
    {
        try {
            $this->transactionCount--;
            if ($this->transactionCount == 0 && $this->inTransaction()) {
                return $this->requirePdo()->rollBack();
            } elseif (!$this->inTransaction()) {
                trigger_error(__METHOD__ . ' no transaction started');
            }
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function inTransaction(): mixed
    {
        try {
            return $this->requirePdo()->inTransaction();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
