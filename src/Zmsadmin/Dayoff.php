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
class Dayoff extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        \BO\Slim\Render::html('page/dayoff.twig', array(
            'title' => 'Allgemein gültige Feiertage - Jahresauswahl',
            'workstation' => $workstation,
            'menuActive' => 'dayoff'
        ));
    }
}
