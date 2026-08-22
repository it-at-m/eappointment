<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Utils;

class SourceNames
{
    /**
     * Accepts comma/semicolon/pipe/whitespace separated source names, e.g.
     * "dldb", "dldb,zms", "dldb; zms", "dldb zms", "dldb|zms".
     *
     * @return list<string>
     */
    public static function configured(): array
    {
        $raw = \App::$source_name;
        if ($raw === '') {
            $raw = 'dldb';
        }
        $names = preg_split('/[,\;\|\s]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        if ($names === false) {
            $names = [];
        }

        $out = [];
        foreach ($names as $name) {
            $name = trim((string) $name);
            if ($name !== '' && !in_array($name, $out, true)) {
                $out[] = $name;
            }
        }

        return $out !== [] ? $out : ['dldb'];
    }
}
