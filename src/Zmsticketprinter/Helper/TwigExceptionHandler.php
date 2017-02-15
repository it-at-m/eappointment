<?php

namespace BO\Zmsticketprinter\Helper;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

class TwigExceptionHandler extends \BO\Slim\TwigExceptionHandler
{
    public static function withHtml(
        RequestInterface $request,
        ResponseInterface $response,
        \Exception $exception,
        $status = 500
    ) {
        $ticketprinterHash = \BO\Zmsclient\Ticketprinter::getHash();
        if ($ticketprinterHash) {
            $exception->templatedata = [
                'hash' => $ticketprinterHash,
            ];
        }
        return parent::withHtml($request, $response, $exception, $status);
    }
}
