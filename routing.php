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
\App::$slim->get('/home/', '\BO\Zmsticketprinter\Home')
    ->setName("Home");

\App::$slim->get('/', '\BO\Zmsticketprinter\Index')
    ->setName("Index");

\App::$slim->map(['GET','POST'], '/scope/{scopeId:\d+}/', '\BO\Zmsticketprinter\TicketprinterByScope')
    ->setName("TicketprinterByScope");

\App::$slim->post('/process/', '\BO\Zmsticketprinter\Process')
    ->setName("Process");

\App::$slim->map(['GET', 'POST'], '/message/{status}/', '\BO\Zmsticketprinter\Message')
    ->setName("Message");

//input queue number to get process for notification
\App::$slim->post('/notification/amendment/', '\BO\Zmsticketprinter\NotificationAmendment')
    ->setName("NotificationAmendment");

//set notification number and send to assign controller
\App::$slim->post('/notification/', '\BO\Zmsticketprinter\Notification')
    ->setName("Notification");

//assign notification number for process in notification queue
\App::$slim->post('/notification/assign/', '\BO\Zmsticketprinter\NotificationAssign')
    ->setName("NotificationAssign");

/*
 * ---------------------------------------------------------------------------
 * redirects from old to new
 * -------------------------------------------------------------------------
 */
    \App::$slim->get('/mehrfachkiosk.php', '\BO\Zmsticketprinter\RedirectOld')
    ->setName("RedirectOld");

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
