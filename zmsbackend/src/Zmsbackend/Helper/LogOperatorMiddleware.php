<?php

namespace BO\Zmsbackend\Helper;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @codeCoverageIgnore
 *
 */
class LogOperatorMiddleware
{
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $next)
    {
        $authority = $request->getUri()->getAuthority();
        \BO\Zmsbackend\Log\Service\Log::$operator = $this->getAuthorityWithoutPassword($authority) . '@' . gethostname();

        return $next->handle($request);
    }

    /**
     * @return null|string|string[]
     *
     */
    private function getAuthorityWithoutPassword(string $authority): array|string|null
    {
        $regex = '/((:)(.+)(?=@))/';
        return preg_replace($regex, '', $authority);
    }
}
