<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers\Office;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Services\Office\OfficesListService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class OfficesListController extends BaseController
{
    private OfficesListService $service;

    public function __construct()
    {
        $this->service = new OfficesListService();
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $requestErrors = ValidationService::validateServerGetRequest($request);
            if (!empty($requestErrors['errors'])) {
                return $this->createJsonResponse(
                    $response,
                    $requestErrors,
                    ErrorMessages::get('invalidRequest')['statusCode']
                );
            }

            $result = $this->service->getOfficesList();

            return is_array($result) && isset($result['errors'])
                ? $this->createJsonResponse(
                    $response,
                    $result,
                    ErrorMessages::getHighestStatusCode($result['errors'])
                )
                : $this->createJsonResponse($response, $result->toArray(), 200);

        } catch (\Exception $e) {
            return $this->createJsonResponse(
                $response,
                ErrorMessages::get('internalError'),
                ErrorMessages::get('internalError')['statusCode']
            );
        }
    }
}