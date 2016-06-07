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
class Owner extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        Helper\Render::checkedHtml(self::$errorHandler, 'page/owner.twig', array(
            'title' => 'Behörden und Standorte',
            'menuActive' => 'owner',
        ));
    }
}
