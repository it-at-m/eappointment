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
 * Login
 * -------------------------------------------------------------------------
 */
\App::$slim->map(['GET','POST'], '/', '\BO\Zmsstatistic\Index')
    ->setName("index");

\App::$slim->get('/workstation/quicklogin/', '\BO\Zmsstatistic\QuickLogin')
    ->setName("quickLogin");

\App::$slim->get('/overview/', '\BO\Zmsstatistic\Overview')
    ->setName("Overview");

/*
 * ---------------------------------------------------------------------------
 * Result views
 * -------------------------------------------------------------------------
 */
\App::$slim->map(['GET','POST'], '/report/client/', '\BO\Zmsstatistic\ReportClientIndex')
    ->setName("ReportClientIndex");

\App::$slim->map(['GET','POST'], '/report/request/', '\BO\Zmsstatistic\ReportRequestIndex')
    ->setName("ReportRequestIndex");

\App::$slim->map(['GET','POST'], '/report/waitingnumber/', '\BO\Zmsstatistic\ReportWaitingnumberIndex')
    ->setName("ReportWaitingnumberIndex");
/*
 * ---------------------------------------------------------------------------
 * Warehouse downloads
 * -------------------------------------------------------------------------
 */

\App::$slim->map(['GET','POST'], '/warehouse/', '\BO\Zmsstatistic\WarehouseIndex')
    ->setName("WarehouseIndex");

\App::$slim->map(['GET','POST'], '/warehouse/{subject}/', '\BO\Zmsstatistic\WarehouseSubject')
    ->setName("WarehouseSubject");

\App::$slim->map(['GET','POST'], '/warehouse/{subject}/{subjectid}/', '\BO\Zmsstatistic\WarehousePeriod')
    ->setName("WarehousePeriod");

\App::$slim->map(['GET','POST'], '/warehouse/{subject}/{subjectid}/{period}/', '\BO\Zmsstatistic\WarehouseReport')
    ->setName("WarehouseReport");

/*
 * ---------------------------------------------------------------------------
 * Logout
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/logout/', '\BO\Zmsstatistic\Logout')
    ->setName("logout");

/*
 * ---------------------------------------------------------------------------
 * Workstation
 * -------------------------------------------------------------------------
 */
\App::$slim->map(['GET','POST'], '/workstation/select/', '\BO\Zmsstatistic\WorkstationSelect')
    ->setName("workstationSelect");

/*
 * ---------------------------------------------------------------------------
 * maintenance
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/testpage/', '\BO\Zmsstatistic\Testpage')
    ->setName("testpage");

\App::$slim->get('/changelog/', '\BO\Zmsstatistic\Changelog')
    ->setName("changelog");

\App::$slim->get('/status/', '\BO\Zmsstatistic\Status')
    ->setName("status");

\App::$slim->get('/healthcheck/', '\BO\Zmsstatistic\Healthcheck')
    ->setName("healthcheck");

\App::$slim->getContainer()
    ->offsetSet('notFoundHandler',
    function ($container) {
        return function (RequestInterface $request, ResponseInterface $response) {
            return \BO\Slim\Render::withHtml($response, 'page/404.twig');
        };
    });

\App::$slim->getContainer()
    ->offsetSet('errorHandler',
    function ($container) {
        return function (RequestInterface $request, ResponseInterface $response, \Exception $exception) {
            return \BO\Zmsstatistic\Helper\TwigExceptionHandler::withHtml($request, $response, $exception);
        };
    });
