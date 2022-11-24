<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim\Middleware;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;

class TrailingSlash
{
    public function __invoke(Request $request, RequestHandlerInterface $next)
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
                $uriString = preg_replace('#^https?:#', '', (string)$uri); //Do not force protocol
            }

            $redirects = \App::$slim->redirect(
                (string) $request->getUri(),
                $uriString,
                StatusCodeInterface::STATUS_MOVED_PERMANENTLY
            )->getCallable();

            return $redirects();
        }

        return $next->handle($request);
    }
}
