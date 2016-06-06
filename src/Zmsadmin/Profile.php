<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class Profile extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        \BO\Slim\Render::html('page/profile.twig', array(
            'title' => 'Nutzerprofil',
            'menuActive' => 'profile'
        ));
    }
}
