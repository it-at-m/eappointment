<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;

class ScopesList extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $scopes = ZmsApiFacadeService::getScopes();

        return $this->createJsonResponse($response, $scopes->toArray(), statusCode: 200);
    }
}
