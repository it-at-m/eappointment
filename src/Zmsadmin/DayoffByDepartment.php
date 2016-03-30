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
class DayoffByDepartment extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        \BO\Slim\Render::html('page/dayoffByDepartment.twig', array(
            'title' => 'Feiertage für Behörde - Jahresauswahl',
            'menuActive' => 'owner'
        ));
    }
}
