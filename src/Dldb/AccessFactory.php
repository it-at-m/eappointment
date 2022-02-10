<?php

/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Dldb;

class AccessFactory
{
    protected static $avilableAccessors = [
        'file' => 'File',
        'elastic' => 'Elastic',
        'sqlite' => 'SQLite',
        'mysql' => 'MySQL',
    ];

    public static function factory(string $type, array $arguments = [])
    {
        if (!isset(static::$avilableAccessors[$type])) {
            throw new \Exception('Invalid accessor');
        }
        $accessClass = '\\BO\\Dldb\\' . static::$avilableAccessors[$type] . 'Access';

        return new $accessClass(...$arguments);
    }
}
