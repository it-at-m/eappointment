<?php

namespace BO\Zmsdb\Helper;

/**
 * Special Class to manage rights by level for old db
 */
class RightsLevelManager
{

    public static $possibleRights = array(
        90 => 'superuser',
        70 => 'organisation',
        50 => 'department',
        40 => 'cluster',
        30 => 'useraccount',
        20 => 'scope',
        15 => 'availability',
        10 => 'ticketprinter',
        0 => 'sms'
    );

    public static function getLevel($userRights)
    {
        $rightsLevel = null;
        foreach ($userRights as $rightName => $isSelected) {
            $level = array_search($rightName, self::$possibleRights, true);
            if ($isSelected && $level > $rightsLevel) {
                $rightsLevel = $level;
            }
        }
        return $rightsLevel;
    }
}
