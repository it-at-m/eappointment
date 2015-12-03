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
class Availability extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        \BO\Slim\Render::html('page/availability.twig', array(
            'title' => 'Öffnungszeiten',
            'menuActive' => 'availability'
        ));
    }
}
