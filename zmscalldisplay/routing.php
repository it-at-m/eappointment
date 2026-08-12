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
\App::$slim->get('/', \BO\Zmscalldisplay\Index::class)
    ->setName("index");

\App::$slim->post('/queue/', \BO\Zmscalldisplay\Queue::class)
    ->setName("queue");

\App::$slim->post('/waitinginfo/', \BO\Zmscalldisplay\Info::class)
    ->setName("info");

/*
 * ---------------------------------------------------------------------------
 * redirects from old to new
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/mehrfachaufruf.php', \BO\Zmscalldisplay\RedirectOld::class)
    ->setName("RedirectOld");

/*
 * ---------------------------------------------------------------------------
 * maintenance
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/healthcheck/', \BO\Zmscalldisplay\Healthcheck::class)
    ->setName("healthcheck");