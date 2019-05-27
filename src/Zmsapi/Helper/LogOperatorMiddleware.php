<?php

namespace BO\Zmsapi\Helper;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

/**
 * @codeCoverageIgnore
 *
 */
class LogOperatorMiddleware
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        $authority = $request->getUri()->getAuthority();
        \BO\Zmsdb\Log::$operator = $this->getAuthorityWithoutPassword($authority) . '@' . gethostname();
        if (null !== $next) {
            $response = $next($request, $response);
        }
        return $response;
    }

    private function getAuthorityWithoutPassword($authority)
    {
        $regex = '/((:)(.+)(?=@))/';
        return preg_replace($regex, '', $authority);
    }
}
