<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Helper;

use BO\Zmscitizenbackend\Connection\Select;
use BO\Zmscitizenbackend\Repository\IcsQueries;

class ConfigPreferences
{
    /**
     * @return array<string, mixed>
     */
    public static function read(): array
    {
        $rows = Select::getReadConnection()->fetchAll(IcsQueries::QUERY_SELECT_CONFIG, []);
        $flat = [];
        foreach (is_array($rows) ? $rows : [] as $row) {
            $name = (string) ($row['name'] ?? '');
            if ($name === '') {
                continue;
            }
            $flat[$name] = $row['value'] ?? '';
        }

        return self::nest($flat);
    }

    /**
     * @param array<string, mixed> $flat
     * @return array<string, mixed>
     */
    public static function nest(array $flat): array
    {
        $hash = [
            'appointments' => [
                'urlChange' => 'https://service.berlin.de/terminvereinbarung/termin/manage/',
                'urlAppointments' => 'https://service.berlin.de/terminvereinbarung/',
            ],
        ];
        foreach ($flat as $key => $value) {
            if (!str_contains((string) $key, '__')) {
                $hash[$key] = $value;
                continue;
            }
            $current =& $hash;
            foreach (explode('__', (string) $key) as $part) {
                if (!isset($current[$part]) || !is_array($current[$part])) {
                    $current[$part] = [];
                }
                $current =& $current[$part];
            }
            $current = $value;
            unset($current);
        }

        return $hash;
    }
}
