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
            // Build a clean canonical home URL from the current request
            $uri = $request->getUri();
            $path = $uri->getPath();
            // collapse multiple slashes
            $path = preg_replace('#/+#', '/', $path);
            // remove stray ampersand segments ("/&" or "/%26")
            $path = preg_replace('#/(?:%26|&)(?=/|$)#', '', $path);
            // ensure trailing slash
            if (substr($path, -1) !== '/') {
                $path .= '/';
            }
            $cleanUri = $uri->withPath($path)->withQuery('')->withFragment('');
            $homeUrl = (string)$cleanUri;
        }
        //\App::$log->debug("HOMEURL", [$homeUrl, $request->getRequestTarget()]);
        \BO\Zmsclient\Ticketprinter::setHomeUrl($homeUrl, $request);
        return $homeUrl;
    }
}
