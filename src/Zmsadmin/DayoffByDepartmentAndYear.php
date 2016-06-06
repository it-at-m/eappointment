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
class DayoffByDepartmentAndYear extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        \BO\Slim\Render::html('page/dayoffByDepartmentAndYear.twig', array(
            'title' => 'Feiertage für Behörde',
            'year' => '2016',
            'menuActive' => 'owner'
        ));
    }
}
