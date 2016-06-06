<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

/**
  * Handle requests concerning services
  *
  */
class Availability extends ScopeAvailabilityDay
{
    /**
     * @return String
     */
    public static function render($scope_id = '00')
    {
        $scope_id = '00';
        return parent::render($scope_id);
    }
}
