<?php

namespace BO\Slim;

class Version
{
    const string UNKNOWN = 'version.unknown';

    public static function getString(): string
    {
        $file = \App::APP_PATH . '/VERSION';
        if (is_file($file)) {
            $contents = file_get_contents($file);
            return is_string($contents) ? trim($contents) : static::UNKNOWN;
        }
        return static::UNKNOWN;
    }

    /**
     * @return string[]
     * @psalm-api
     */
    public static function getArray(): array
    {
        $version = static::getString();
        $array = [];
        if (preg_match('#^v(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)$#', $version, $array)) {
            return [
                'major' => $array['major'],
                'minor' => $array['minor'],
                'patch' => $array['patch'],
            ];
        }
        return [
            'major' => "unknown",
            'minor' => "0",
            'patch' => "0",
        ];
    }
}
