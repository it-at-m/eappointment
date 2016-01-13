<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class TicketprinterStatusByScope extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        \BO\Slim\Render::html('page/ticketprinterStatus.twig', array(
            'title' => 'Wartenummernausgabe am Kiosk',
            'menuActive' => 'ticketprinterStatus'
        ));
    }
}
