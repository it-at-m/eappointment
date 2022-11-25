<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Slim\Middleware;

use BO\Slim\Headers;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;

class ZmsSlimRequest
{
    /**
     * @param Request $request
     * @param RequestHandlerInterface $next
     * @return ResponseInterface
     */
    public function __invoke(Request $request, RequestHandlerInterface $next): ResponseInterface
    {
        $zmsSlimRequest = $request;

        if (!$request instanceof \BO\Slim\Request) {
            $zmsSlimRequest = new \BO\Slim\Request(
                $request->getMethod(),
                $request->getUri(),
                new Headers($request->getHeaders()),
                $request->getCookieParams(),
                $request->getServerParams(),
                $request->getBody(),
                $request->getUploadedFiles()
            );
        }

        return $next->handle($zmsSlimRequest);
    }
}
