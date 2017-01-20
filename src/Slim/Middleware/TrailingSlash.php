<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim\Middleware;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class TrailingSlash
{
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $uri = $request->getUri();
        $path = $uri->getPath();
        if (substr($path, -1) !== '/' && !pathinfo($path, PATHINFO_EXTENSION)) {
            // permanently redirect paths without a trailing slash
            // to their trailing counterpart
            $uri = $uri->withPath($path. '/');
            if ($request->getHeader('X-Ssl') && 'no' != $request->getHeader('X-Ssl')) {
                $uri = $uri->withScheme('https');
                $uriString = (string)$uri;
            } else {
                $uriString = preg_replace('#^https?:#', '', (string)$uri); //Do not force protocoll
            }
            return $response->withRedirect($uriString, 301);
        }
        return $next($request, $response);
    }
}
