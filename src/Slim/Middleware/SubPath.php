<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Slim\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * this Handler allows routing independent of the base path
 */
class SubPath
{
    /**
     * @param Request|\BO\Slim\Request $request
     * @param RequestHandlerInterface $next
     * @return ResponseInterface
     */
    public function __invoke(Request $request, RequestHandlerInterface $next): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $appPath = substr($path, strlen($request->getBasePath()));

        return $next->handle($request->withUri($request->getUri()->withPath($appPath)));
    }
}
