<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Slim\Tests\Middleware;

use BO\Slim\Request;
use BO\Slim\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandlerMock implements RequestHandlerInterface
{
    private $request;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        return new Response();
    }

    /**
     * @return RequestInterface|Request
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}