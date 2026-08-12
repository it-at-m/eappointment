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

\App::$slim->get('/overview/', \BO\Zmsstatistic\Overview::class)
    ->setName("Overview");


    
/*
 * ---------------------------------------------------------------------------
 * Result views
 * -------------------------------------------------------------------------
 */

 \App::$slim->get('/report/client/scope/[{period}/]', \BO\Zmsstatistic\ReportClientIndex::class)
     ->setName("ReportClientIndex");

 \App::$slim->get('/report/client/department/[{period}/]', \BO\Zmsstatistic\ReportClientDepartment::class)
     ->setName("ReportClientDepartment");

 \App::$slim->get('/report/client/organisation/[{period}/]', \BO\Zmsstatistic\ReportClientOrganisation::class)
     ->setName("ReportClientOrganisation");


 \App::$slim->get('/report/request/scope/[{period}/]', \BO\Zmsstatistic\ReportRequestIndex::class)
     ->setName("ReportRequestIndex");

 \App::$slim->get('/report/request/department/[{period}/]', \BO\Zmsstatistic\ReportRequestDepartment::class)
     ->setName("ReportRequestDepartment");

 \App::$slim->get('/report/request/organisation/[{period}/]', \BO\Zmsstatistic\ReportRequestOrganisation::class)
     ->setName("ReportRequestOrganisation");

 \App::$slim->get('/report/waiting/scope/[{period}/]', \BO\Zmsstatistic\ReportWaitingIndex::class)
     ->setName("ReportWaitingIndex");

\App::$slim->get('/report/waiting/department/[{period}/]', \BO\Zmsstatistic\ReportWaitingDepartment::class)
    ->setName("ReportWaitingDepartment");

\App::$slim->get('/report/waiting/organisation/[{period}/]', \BO\Zmsstatistic\ReportWaitingOrganisation::class)
    ->setName("ReportWaitingOrganisation");

\App::$slim->get('/report/capacity/scope/[{period}/]', \BO\Zmsstatistic\ReportCapacityIndex::class)
    ->setName("ReportCapacityIndex");

/*
 * ---------------------------------------------------------------------------
 * Warehouse views
 * -------------------------------------------------------------------------
 */

\App::$slim->get('/warehouse/', \BO\Zmsstatistic\WarehouseIndex::class)
    ->setName("WarehouseIndex");

\App::$slim->get('/warehouse/{subject}/', \BO\Zmsstatistic\WarehouseSubject::class)
    ->setName("WarehouseSubject");

\App::$slim->get('/warehouse/{subject}/{subjectid}/', \BO\Zmsstatistic\WarehousePeriod::class)
    ->setName("WarehousePeriod");

\App::$slim->get('/warehouse/{subject}/{subjectid}/{period}/', \BO\Zmsstatistic\WarehouseReport::class)
    ->setName("WarehouseReport");

/*
 * ---------------------------------------------------------------------------
 * Logout
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/logout/', \BO\Zmsstatistic\Logout::class)
    ->setName("logout");

/*
 * ---------------------------------------------------------------------------
 * Workstation
 * -------------------------------------------------------------------------
 */
\App::$slim->map(['GET','POST'], '/workstation/select/', \BO\Zmsstatistic\WorkstationSelect::class)
    ->setName("workstationSelect");

/*
 * ---------------------------------------------------------------------------
 * Other Ajax Components
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/dialog/', \BO\Zmsstatistic\Helper\DialogHandler::class)
    ->setName("dialogHandler");


/*
 * ---------------------------------------------------------------------------
 * maintenance
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/changelog/', \BO\Zmsstatistic\Changelog::class)
    ->setName("changelog");

\App::$slim->get('/status/', \BO\Zmsstatistic\Status::class)
    ->setName("status");

\App::$slim->get('/healthcheck/', \BO\Zmsstatistic\Healthcheck::class)
    ->setName("healthcheck");