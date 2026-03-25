<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers\Scope;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Services\Scope\ScopeByTimeslotService;
use BO\Zmscitizenapi\Utils\ErrorMessages;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ScopeByTimeslotController extends BaseController
{
    private ScopeByTimeslotService $service;

    public function __construct()
    {
        $this->service = new ScopeByTimeslotService();
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $result = $this->service->getScopeByTimeslot($this->getNormalizedQueryParams($request));

        return is_array($result) && isset($result['errors'])
            ? $this->createJsonResponse(
                $response,
                $result,
                ErrorMessages::getHighestStatusCode($result['errors'])
            )
            : $this->createJsonResponse($response, $result->toArray(), 200);
    }

    private function getNormalizedQueryParams(RequestInterface $request): array
    {
        $queryParams = $request->getQueryParams();

        if (
            !empty($queryParams['officeId']) ||
            !empty($queryParams['timestamp']) ||
            !empty($queryParams['serviceId'])
        ) {
            return $queryParams;
        }

        $rawQuery = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY) ?? '';
        parse_str($rawQuery, $queryParams);

        foreach (array_keys($queryParams) as $key) {
            if (is_string($key) && str_starts_with($key, '/')) {
                unset($queryParams[$key]);
            }
        }

        return $queryParams;
    }
}
