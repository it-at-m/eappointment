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
\App::$slim->get('/home/', \BO\Zmsticketprinter\Home::class)
    ->setName("Home");

\App::$slim->get('/status/', \BO\Zmsticketprinter\Status::class)
    ->setName("Status");

\App::$slim->get('/', \BO\Zmsticketprinter\Index::class)
    ->setName("Index");

\App::$slim->map(['GET','POST'], '/scope/{scopeId:\d+}/', \BO\Zmsticketprinter\TicketprinterByScope::class)
    ->setName("TicketprinterByScope");

\App::$slim->post('/process/', \BO\Zmsticketprinter\Process::class)
    ->setName("Process");

\App::$slim->map(['GET', 'POST'], '/message/{status:[a-z_]+}/', \BO\Zmsticketprinter\Message::class)
    ->setName("Message");

\App::$slim->get('/dialog/', \BO\Zmsticketprinter\Helper\DialogHandler::class)
    ->setName("dialogHandler");

\App::$slim->get('/reset/', \BO\Zmsticketprinter\Reset::class)
->setName("reset");
/*
 * ---------------------------------------------------------------------------
 * redirects from old to new
 * -------------------------------------------------------------------------
 */
    \App::$slim->get('/mehrfachkiosk.php', \BO\Zmsticketprinter\RedirectOld::class)
    ->setName("RedirectOld");

/*
 * ---------------------------------------------------------------------------
 * maintenance
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/healthcheck/', \BO\Zmsticketprinter\Healthcheck::class)
    ->setName("healthcheck");
\App::$slim->getContainer()->offsetSet('notFoundHandler', function ($container) {
    return function (RequestInterface $request, ResponseInterface $response) {
        return \BO\Slim\Render::withHtml($response, '404.twig');
    };
});
