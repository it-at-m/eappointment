<?php
/**
 * @package 115Mandant
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
    public static function render()
    {
        return parent::render('00');
    }
}
