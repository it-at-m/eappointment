<?php

/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @SuppressWarnings(NumberOfChildren)
 *
 */
abstract class BaseController extends Helper\Access
{
    protected function hasStatisticPermission(): bool
    {
        return $this->workstation
            && $this->workstation->getUseraccount()->hasPermissions(['statistic']);
    }

    protected function getAdminBaseUrl($request): string
    {
        $basePath = method_exists($request, 'getBasePath') ? (string) $request->getBasePath() : '';
        $adminBasePath = preg_replace('#/statistic/?$#', '/admin', $basePath);
        return rtrim((string) $adminBasePath, '/') . '/';
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $request = $this->initRequest($request);
        if ($this->withAccess) {
            $this->initAccessRights($request);
            if (!$this->hasStatisticPermission()) {
                return $response->withRedirect($this->getAdminBaseUrl($request));
            }
        }
        $noCacheResponse = \BO\Slim\Render::withLastModified($response, time(), '0');
        return $this->readResponse($request, $noCacheResponse, $args);
    }

    /**
     * @codeCoverageIgnore
     *
     */
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        return parent::__invoke($request, $response, $args);
    }
}
