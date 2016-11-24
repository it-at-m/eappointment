<?php
// @codingStandardsIgnoreFile
/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
use BO\Slim\Helper;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;
/*
 * ---------------------------------------------------------------------------
 * html, basic routes
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/', '\BO\Zmsticketprinter\Index')
    ->setName("Index");

\App::$slim->map(['GET','POST'], '/scope/{scopeId:\d+}/', '\BO\Zmsticketprinter\TicketprinterByScope')
    ->setName("TicketprinterByScope");

\App::$slim->post('/process/', '\BO\Zmsticketprinter\TicketprinterProcess')
    ->setName("TicketprinterProcess");

\App::$slim->get('/notification/{processId:\d+}/{authKey}/', '\BO\Zmsticketprinter\TicketprinterNotification')
    ->setName("TicketprinterNotification");

    \App::$slim->post('/notification/', '\BO\Zmsticketprinter\TicketprinterProcessNotification')
    ->setName("TicketprinterProcessNotification");
/*
 * ---------------------------------------------------------------------------
 * maintenance
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/healthcheck/', '\BO\Zmsticketprinter\Healthcheck')
    ->setName("healthcheck");
\App::$slim->getContainer()
    ->offsetSet('notFoundHandler',
    function ($container) {
        return function (RequestInterface $request, ResponseInterface $response) {
            return \BO\Slim\Render::withHtml($response, '404.twig');
        };
    });
\App::$slim->getContainer()
    ->offsetSet('errorHandler',
    function ($container) {
        return function (RequestInterface $request, ResponseInterface $response, \Exception $exception) {
            return \BO\Slim\TwigExceptionHandler::withHtml($request, $response, $exception);
        };
    });

