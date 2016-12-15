<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Availability;
use BO\Zmsentities\Collection\AvailabilityList;

/**
  * Handle requests concerning services
  *
  */
class ScopeAvailabilityDayConflicts extends ScopeAvailabilityDay
{
    /**
     * @return String
     */
    public static function render($scope_id, $dateString)
    {
        $data = static::getAvailabilityData($scope_id, $dateString);
        \BO\Slim\Render::json($data);
    }
}
