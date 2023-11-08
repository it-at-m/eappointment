<?php

namespace BO\Zmsentities\Helper;

/**
 * Special Class to manage rights by level for old db
 *
 * @todo Remove this class, keep no contraint on old DB schema in zmsentities
 */
class RightsLevelManager
{
    public static $possibleRights = array(
        'superuser' => 90,
        'organisation' => 70,
        'department' => 50,
        'cluster' => 40,
        'useraccount' => 40,
        'scope' => 30,
        'availability' => 20,
        'ticketprinter' => 15,
        'sms' => 10,
        'basic' => 0
    );

    public static $accessRights = array(
        'superuser' => 1,
        'organisation' => 1,
        'department' => 1,
        'scope' => 1
    );

    public static function getLevel($userRights)
    {
        $rightsLevel = 0;
        foreach ($userRights as $rightName => $isSelected) {
            $level = self::$possibleRights[$rightName];
            if ($isSelected && $level > $rightsLevel) {
                $rightsLevel = $level;
            }
        }
        return $rightsLevel;
    }
}
