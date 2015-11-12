<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;

/**
  * Handle requests concerning services
  *
  */
class Index extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        Render::html('page/index.twig', array(
            'title' => 'Startseite'
        ));
    }
}
