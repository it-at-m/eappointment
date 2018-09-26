<?php

/**
 *
 * @package Zmsticketprinter
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter\Helper;

use BO\Mellon\Validator;

class HomeUrl
{
    /**
     * check if new home url is requested, if not check if url exists in cookie,
     * otherwise set current uri from request as new home url
     *
     **/

    public static function create($request)
    {
        $homeUrl = null;
        $validator = $request->getAttribute('validator');
        $ticketprinter = $validator->getParameter('ticketprinter')->isArray()->getValue();
        if ($ticketprinter && array_key_exists('home', $ticketprinter)) {
            $homeUrl = Validator::value($ticketprinter['home'])->isUrl()->getValue();
        } elseif (!$homeUrl) {
            $homeUrl = $request->getRequestTarget();
        }
        //\App::$log->debug("HOMEURL", [$homeUrl, $request->getRequestTarget()]);
        \BO\Zmsclient\Ticketprinter::setHomeUrl($homeUrl, $request);
        return $homeUrl;
    }
}
