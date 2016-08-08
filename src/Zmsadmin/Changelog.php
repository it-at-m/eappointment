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
class Changelog extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        \BO\Slim\Render::html('page/changelog.twig', array(
            'title' => 'Changelog',
            'menuActive' => 'changelog'
        ));
    }
}
