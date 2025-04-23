<?php

namespace BO\Dldb\Importer;

use BO\Dldb\PDOAccess;

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
     */
    public function query(...$args)
    {
        try {
            return $this->getPDOAccess()->query(...$args);
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
            return $this->getPDOAccess()->exec(...$args);
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
            return $this->getPDOAccess()->prepare(...$args);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function beginTransaction()
    {
        try {
            return $this->getPDOAccess()->beginTransaction();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function commit()
    {
        try {
            return $this->getPDOAccess()->commit();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function rollBack()
    {
        try {
            return $this->getPDOAccess()->rollBack();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function inTransaction()
    {
        try {
            return $this->getPDOAccess()->inTransaction();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
