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
    public static function create(\Psr\Http\Message\RequestInterface $request)
    {
        $homeUrl = null;
        $validator = $request->getAttribute('validator');
        $ticketprinter = $validator->getParameter('ticketprinter')->isArray()->getValue();
        if ($ticketprinter && array_key_exists('home', $ticketprinter)) {
            $homeUrl = Validator::value($ticketprinter['home'])->isUrl()->getValue();
        } elseif (!$homeUrl) {
            $homeUrl = $request->getRequestTarget();
        }
        $homeUrl = static::sanitizeUrl($homeUrl);
        \BO\Zmsclient\Ticketprinter::setHomeUrl($homeUrl, $request);
        return $homeUrl;
    }

    /**
     * Drop rewrite PATH_INFO stuffed into the query string (no "="), e.g.
     * /ticketprinter/scope/127/?/scope/127/&/scope/127/
     */
    public static function sanitizeUrl(mixed $url): string
    {
        if (! is_string($url) || $url === '') {
            return is_string($url) ? $url : '';
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return $url;
        }

        $kept = [];
        foreach (explode('&', $parts['query'] ?? '') as $pair) {
            if ($pair !== '' && str_contains($pair, '=')) {
                $kept[] = $pair;
            }
        }

        $sanitized = '';
        if (isset($parts['scheme'], $parts['host'])) {
            $sanitized = $parts['scheme'] . '://' . $parts['host'];
            if (isset($parts['port'])) {
                $sanitized .= ':' . $parts['port'];
            }
        }
        $sanitized .= $parts['path'] ?? '';
        if ($kept !== []) {
            $sanitized .= '?' . implode('&', $kept);
        }

        return $sanitized;
    }
}
