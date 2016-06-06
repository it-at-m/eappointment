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
class DayoffEdit extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        \BO\Slim\Render::html('page/dayoffEdit.twig', array(
            'title' => 'Administration freie Tage',
            'menuActive' => 'dayoff'
        ));
    }
}
