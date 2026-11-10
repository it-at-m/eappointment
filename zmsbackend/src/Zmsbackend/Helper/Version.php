<?php

namespace BO\Zmsbackend\Helper;

class Version
{
    public static function getString(?string $path = null): string
    {
        $path = ($path) ? $path : \App::APP_PATH;
        $file = $path . '/VERSION';
        if (is_file($file)) {
            return trim(file_get_contents($file));
        }
        return "version.unknown";
    }

    /**
     * @return string[]
     *
     */
    public static function getArray(?string $path = null): array
    {
        $version = static::getString($path);
        $array = [];
        if (preg_match('#^(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+.*)$#', $version, $array)) {
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
