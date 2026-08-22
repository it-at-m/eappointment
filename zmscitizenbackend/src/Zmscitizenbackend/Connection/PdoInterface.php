<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Connection;

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
