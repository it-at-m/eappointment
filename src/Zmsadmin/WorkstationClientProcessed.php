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
class WorkstationClientProcessed extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        \BO\Slim\Render::html('page/workstationClientProcessed.twig', array(
            'title' => 'Kundendaten für Statistik',
            'menuActive' => 'workstation'
        ));
    }
}
