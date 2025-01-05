<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Models\Collections\OfficeList;
use BO\Zmscitizenapi\Services\ValidationService;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class OfficeListByService extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $requestErrors = ValidationService::validateServerGetRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse(
                $response,
                $requestErrors,
                ErrorMessages::get('invalidRequest')['statusCode']
            );
        }

        $clientData = $this->extractClientData($request->getQueryParams());

        $errors = $this->validateClientData($clientData);
        if (is_array($errors) && !empty($errors['errors'])) {
            $statusCode = ErrorMessages::getHighestStatusCode($errors['errors']);
            return $this->createJsonResponse($response, $errors, $statusCode);
        }

        try {
            $result = $this->getOfficeListByService($clientData);

            if (is_array($result) && !empty($result['errors'])) {
                $statusCode = ErrorMessages::getHighestStatusCode($result['errors']);
                return $this->createJsonResponse($response, $result, $statusCode);
            }

            return $result instanceof OfficeList
                ? $this->createJsonResponse($response, $result->toArray(), 200)
                : $this->createJsonResponse(
                    $response,
                    ErrorMessages::get('invalidRequest'),
                    ErrorMessages::get('invalidRequest')['statusCode']
                );

        } catch (\Exception $e) {
            return $this->createJsonResponse(
                $response,
                ['errors' => [ErrorMessages::get('internalError')]],
                500
            );
        }
    }

    private function extractClientData(array $queryParams): object
    {
        return (object) [
            'serviceId' => isset($queryParams['serviceId']) && is_numeric($queryParams['serviceId'])
                ? (int) $queryParams['serviceId']
                : null
        ];
    }

    private function validateClientData(object $data): array
    {
        return ValidationService::validateGetOfficeListByServiceId($data->serviceId);
    }

    private function getOfficeListByService(object $data): array|OfficeList
    {
        return ZmsApiFacadeService::getOfficeListByServiceId($data->serviceId);
    }
}