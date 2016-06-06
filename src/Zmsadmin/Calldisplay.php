<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class Calldisplay extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        \BO\Slim\Render::html('page/calldisplay.twig', array(
            'title' => 'Aufrufanlage - Standortauswahl',
            'menuActive' => 'calldisplay'
        ));
    }
}
