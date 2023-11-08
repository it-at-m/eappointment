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
\App::$slim->map(['GET','POST'], '/', \BO\Zmsstatistic\Index::class)
    ->setName("index");

\App::$slim->get('/workstation/quicklogin/', \BO\Zmsstatistic\QuickLogin::class)
    ->setName("quickLogin");

\App::$slim->map(['GET','POST'], '/oidc/', \BO\Zmsstatistic\Oidc::class)
    ->setName("oidc")->add(new \BO\Slim\Middleware\OAuthMiddleware('login'));

\App::$slim->get('/overview/', '\BO\Zmsstatistic\Overview')
    ->setName("Overview");


    
/*
 * ---------------------------------------------------------------------------
 * Result views
 * -------------------------------------------------------------------------
 */

 \App::$slim->get('/report/client/scope/[{period}/]', '\BO\Zmsstatistic\ReportClientIndex')
     ->setName("ReportClientIndex");

 \App::$slim->get('/report/client/department/[{period}/]', '\BO\Zmsstatistic\ReportClientDepartment')
     ->setName("ReportClientDepartment");

 \App::$slim->get('/report/client/organisation/[{period}/]', '\BO\Zmsstatistic\ReportClientOrganisation')
     ->setName("ReportClientOrganisation");


 \App::$slim->get('/report/request/scope/[{period}/]', '\BO\Zmsstatistic\ReportRequestIndex')
     ->setName("ReportRequestIndex");

 \App::$slim->get('/report/request/department/[{period}/]', '\BO\Zmsstatistic\ReportRequestDepartment')
     ->setName("ReportRequestDepartment");

 \App::$slim->get('/report/request/organisation/[{period}/]', '\BO\Zmsstatistic\ReportRequestOrganisation')
     ->setName("ReportRequestOrganisation");

 \App::$slim->get('/report/waiting/scope/[{period}/]', '\BO\Zmsstatistic\ReportWaitingIndex')
     ->setName("ReportWaitingIndex");

\App::$slim->get('/report/waiting/department/[{period}/]', '\BO\Zmsstatistic\ReportWaitingDepartment')
    ->setName("ReportWaitingDepartment");

\App::$slim->get('/report/waiting/organisation/[{period}/]', '\BO\Zmsstatistic\ReportWaitingOrganisation')
    ->setName("ReportWaitingOrganisation");

/*
 * ---------------------------------------------------------------------------
 * Warehouse views
 * -------------------------------------------------------------------------
 */

\App::$slim->get('/warehouse/', '\BO\Zmsstatistic\WarehouseIndex')
    ->setName("WarehouseIndex");

\App::$slim->get('/warehouse/{subject}/', '\BO\Zmsstatistic\WarehouseSubject')
    ->setName("WarehouseSubject");

\App::$slim->get('/warehouse/{subject}/{subjectid}/', '\BO\Zmsstatistic\WarehousePeriod')
    ->setName("WarehousePeriod");

\App::$slim->get('/warehouse/{subject}/{subjectid}/{period}/', '\BO\Zmsstatistic\WarehouseReport')
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
 * Other Ajax Components
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/dialog/', '\BO\Zmsstatistic\Helper\DialogHandler')
    ->setName("dialogHandler");


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