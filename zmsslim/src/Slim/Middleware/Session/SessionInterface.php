<?php

namespace BO\Slim\Middleware\Session;

interface SessionInterface extends \JsonSerializable
{
    public function setGroup(array $group, bool $clear = false): void;

    public function set(int|string $key, mixed $value, int|string|null $groupIndex = null): void;

    public function get(int|string $key, int|string|null $groupIndex = null, mixed $default = null): mixed;

    public function getEntity(): mixed;

    public function remove(int|string $key, int|string|null $groupIndex = null): void;

    public function clear(): void;

    public function clearGroup(int|string|null $groupIndex = null): void;

    public function has(int|string $key, int|string|null $groupIndex = null): ?bool;

    public function isEmpty(): bool;
}
