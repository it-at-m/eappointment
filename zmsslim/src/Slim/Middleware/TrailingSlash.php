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

        $needsTrailingSlash = substr($path, -1) !== '/' && !pathinfo($path, PATHINFO_EXTENSION);
        if (!$needsTrailingSlash) {
            return $next->handle($request);
        }

        // API paths: rewrite in-place so slashless URLs match routes defined with a trailing slash.
        // Do not 301 — redirects break POST and are awkward for API clients.
        if (strpos($path, '/api/') !== false) {
            return $next->handle($request->withUri($uri->withPath($path . '/')));
        }

        // Non-API (HTML apps): permanently redirect to the trailing-slash URL.
        $uri = $uri->withPath($path . '/');
        if ($request->hasHeader('X-Ssl') && 'no' !== $request->getHeaderLine('X-Ssl')) {
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
}
