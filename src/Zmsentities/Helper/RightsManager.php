<?php

namespace BO\Zmsentities\Helper;

/**
 * Special sort algorithm for DLDB
 */
class RightsManager
{

    /**
     * @todo check against ISO definition
     */
    public static function getPossibleRights()
    {
        return array(
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
    }
}
