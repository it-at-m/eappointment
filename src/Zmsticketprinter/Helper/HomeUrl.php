<?php

/**
 *
 * @package Zmsticketprinter
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter\Helper;

class HomeUrl
{
    /**
     * check if new home url is requested, if not check if url exists in cookie,
     * otherwise set current uri from request as new home url
     *
     **/

    public static function create($request)
    {
        $validator = $request->getAttribute('validator');
        $homeUrl = $validator->getParameter('home')->isUrl()->getValue();
        if ($homeUrl) {
            \BO\Zmsclient\Ticketprinter::setHomeUrl($homeUrl);
        } elseif (! \BO\Zmsclient\Ticketprinter::getHomeUrl()) {
            \BO\Zmsclient\Ticketprinter::setHomeUrl($request->getUri());
        }
    }
}
