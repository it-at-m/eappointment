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
class PickupHandheld extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        \BO\Slim\Render::html('page/pickupHandheld.twig', array(
            'title' => 'Abholer verwalten',
            'menuActive' => 'pickup'
        ));
    }
}
