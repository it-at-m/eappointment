<?php

namespace BO\Zmsticketprinter\Helper;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class TwigExceptionHandler extends \BO\Slim\TwigExceptionHandler
{
    public static function withHtml(
        RequestInterface $request,
        ResponseInterface $response,
        \Throwable $exception,
        $status = 500
    ) {
        $ticketprinterHash = \BO\Zmsclient\Ticketprinter::getHash();
        if ($ticketprinterHash) {
            // @codeCoverageIgnoreStart
            $exception->templatedata = [
                'hash' => $ticketprinterHash,
            ];
            // @codeCoverageIgnoreEnd
        }
        return parent::withHtml($request, $response, $exception, $status);
    }
}
