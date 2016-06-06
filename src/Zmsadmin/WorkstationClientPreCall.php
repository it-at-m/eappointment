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
class WorkstationClientPreCall extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        \BO\Slim\Render::html('page/workstationClientPreCall.twig', array(
            'title' => 'Sachbearbeiter',
            'menuActive' => 'workstation'
        ));
    }
}
