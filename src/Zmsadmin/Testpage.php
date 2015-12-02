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
class Testpage extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        \BO\Slim\Render::html('page/testpage.twig', array(
            'title' => 'Testseite',
            'menuActive' => 'testpage'
        ));
    }
}
