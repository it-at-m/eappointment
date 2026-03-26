<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers\Scope;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Services\Scope\ScopeByTimeslotService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
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
        $requestErrors = ValidationService::validateServerGetRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse(
                $response,
                $requestErrors,
                ErrorMessages::get('invalidRequest', $this->language)['statusCode']
            );
        }

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

        // Fallback: When query params aren't properly parsed by the request object
        // (e.g., in certain Slim routing configurations), extract them from REQUEST_URI.
        // The '/' prefix removal handles URL-encoded query strings that include the path.
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
