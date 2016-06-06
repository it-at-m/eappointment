<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class UseraccountEdit extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        \BO\Slim\Render::html('page/useraccountEdit.twig', array(
            'title' => 'Nutzer: Einrichtung und Administration',
            'menuActive' => 'useraccount'
        ));
    }
}
