<?php

namespace BO\Dldb;

abstract class PDOAccess extends AbstractAccess
{
    /**
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    protected $accessorClassName = [];

    protected $accessorNamesPlural = [
        'Authority' => 'Authorities',
        'Borough' => 'Boroughs',
        'Link' => 'Links',
        'Location' => 'Locations',
        'Office' => 'Offices',
        'Service' => 'Services',
        'Setting' => 'Settings',
        'Topic' => 'Topics'
    ];

    protected $pdo;

    protected $engine = 'SQLite';


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
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->postConnect();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function __call($method, $args = [])
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

    protected function postConnect()
    {
    }

    public function loadAccessor(string $name, string $locale = 'de')
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

    abstract protected function connect(array $options);

    public function getConnection()
    {
        return $this->pdo;
    }

    /**
     * parameters see https://www.php.net/manual/de/pdo.query.php
     */

    public function query(...$args)
    {
        try {
            return $this->pdo->query(...$args);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * parameters see https://www.php.net/manual/de/pdo.exec.php
     */

    public function exec(...$args)
    {
        try {
            return $this->pdo->exec(...$args);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * parameters see https://www.php.net/manual/de/pdo.prepare.php
     */

    public function prepare(...$args)
    {
        try {
            return $this->pdo->prepare(...$args);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected $transactionCount = 0;

    public function beginTransaction()
    {
        try {
            $this->transactionCount++;
            if ($this->inTransaction()) {
                return true;
            }
            return $this->pdo->beginTransaction();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function commit()
    {
        try {
            $this->transactionCount--;
            if ($this->transactionCount == 0 && $this->inTransaction()) {
                return $this->pdo->commit();
            } elseif (!$this->inTransaction()) {
                trigger_error(__METHOD__ . ' no transaction started');
            }
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function rollBack()
    {
        try {
            $this->transactionCount--;
            if ($this->transactionCount == 0 && $this->inTransaction()) {
                return $this->pdo->rollBack();
            } elseif (!$this->inTransaction()) {
                trigger_error(__METHOD__ . ' no transaction started');
            }
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function inTransaction()
    {
        try {
            return $this->pdo->inTransaction();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
