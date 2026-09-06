<?php

namespace BO\Zmsdldb\Importer;

use BO\Zmsdldb\PDOAccess;

trait PDOTrait
{
    protected $pdoAccess;

    public function setPDOAccess(PDOAccess $pdoAccess): self
    {
        $this->pdoAccess = $pdoAccess;
        return $this;
    }

    public function getPDOAccess(): PDOAccess
    {
        return $this->pdoAccess;
    }

    /**
     * parameters see https://www.php.net/manual/de/pdo.query.php
     * @psalm-api
     */
    public function query(mixed ...$args): mixed
    {
        try {
            return $this->getPDOAccess()->query(...$args);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * parameters see https://www.php.net/manual/de/pdo.exec.php
     * @psalm-api
     */

    public function exec(mixed ...$args): mixed
    {
        try {
            return $this->getPDOAccess()->exec(...$args);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * parameters see https://www.php.net/manual/de/pdo.prepare.php
     * @psalm-api
     */
    public function prepare(mixed ...$args): mixed
    {
        try {
            return $this->getPDOAccess()->prepare(...$args);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function beginTransaction(): mixed
    {
        try {
            return $this->getPDOAccess()->beginTransaction();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function commit(): mixed
    {
        try {
            return $this->getPDOAccess()->commit();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function rollBack(): mixed
    {
        try {
            return $this->getPDOAccess()->rollBack();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /** @psalm-api */
    public function inTransaction(): mixed
    {
        try {
            return $this->getPDOAccess()->inTransaction();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
