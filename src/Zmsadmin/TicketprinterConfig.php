<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class TicketprinterConfig extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        \BO\Slim\Render::html('page/ticketprinterConfig.twig', array(
            'title' => 'Anmeldung an Warteschlange',
            'menuActive' => 'ticketprinter'
        ));
    }
}
