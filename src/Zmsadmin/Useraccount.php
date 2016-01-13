<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class Useraccount extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        \BO\Slim\Render::html('page/useraccount.twig', array(
            'title' => 'Nutzer',
            'menuActive' => 'useraccount'
        ));
    }
}
