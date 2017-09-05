<?php

namespace BO\Zmsapi\Helper;

class Version
{
    public static function getString()
    {
        $file = \App::APP_PATH . '/VERSION';
        if (is_file($file)) {
            return trim(file_get_contents($file));
        }
        return "version.unknown";
    }

    public static function getArray()
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
