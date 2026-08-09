<?php

namespace BO\Zmsbackend\Connection;

/**
 * @codeCoverageIgnore
 */
interface PdoInterface
{
    public function beginTransaction(): bool;

    public function commit(): bool;

    public function inTransaction(): bool;

    public function rollBack(): bool;
}
