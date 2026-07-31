<?php

declare(strict_types=1);

namespace BO\Zmsadmin\Helper;

/**
 * Queue exclude numbers are CSV on the HTTP wire; use arrays inside PHP.
 */
class ExcludeIds
{
    public static function fromQuery(?string $exclude): array
    {
        if ($exclude === null || $exclude === '') {
            return [];
        }

        $ids = array_map('trim', explode(',', $exclude));
        return array_values(array_filter($ids, static fn(string $id): bool => $id !== ''));
    }

    public static function toQuery(array $exclude): string
    {
        return implode(',', $exclude);
    }
}
