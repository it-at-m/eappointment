<?php

namespace BO\Zmsbackend\Connection;

/**
 * @codeCoverageIgnore
 */
interface PdoInterface
{
    /**
     * @return bool
     *
     * @psalm-suppress PossiblyUnusedReturnValue PDO callers often ignore this bool
     */
    public function beginTransaction(): bool;

    /**
     * @return bool
     *
     * @psalm-suppress PossiblyUnusedReturnValue PDO callers often ignore this bool
     */
    public function commit(): bool;

    public function inTransaction(): bool;

    /**
     * @return bool
     *
     * @psalm-suppress PossiblyUnusedReturnValue PDO callers often ignore this bool
     */
    public function rollBack(): bool;
}
