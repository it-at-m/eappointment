<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Slim\Factory;

use Slim\Psr7\Request as PsrRequest;
use BO\Slim\Request;
use Slim\Psr7\Headers;

class ServerRequestFactory extends \Slim\Psr7\Factory\ServerRequestFactory
{
    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function createFromGlobals(): PsrRequest
    {
        $psrRequest = parent::createFromGlobals();

        return new Request(
            $psrRequest->getMethod(),
            $psrRequest->getUri(),
            Headers::createFromGlobals(),
            $psrRequest->getCookieParams(),
            $_SERVER,
            $psrRequest->getBody(),
            $psrRequest->getUploadedFiles()
        );
    }
}
