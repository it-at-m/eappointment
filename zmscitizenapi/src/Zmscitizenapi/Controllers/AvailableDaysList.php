<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Models\AvailableDays;
use BO\Zmscitizenapi\Services\ValidationService;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AvailableDaysList extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $clientData = $this->extractClientData($request->getQueryParams());
        
        $errors = $this->validateClientData($clientData);
        if (is_array($errors) && !empty($errors['errors'])) {
            $statusCode = ErrorMessages::getHighestStatusCode($errors['errors']);
            return $this->createJsonResponse($response, $errors, $statusCode);
        }

        try {
            $result = $this->getAvailableDays($clientData);
            
            if (is_array($result) && !empty($result['errors'])) {
                $statusCode = ErrorMessages::getHighestStatusCode($result['errors']);
                return $this->createJsonResponse($response, $result, $statusCode);
            }

            return $result instanceof AvailableDays
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
            'officeId' => isset($queryParams['officeId']) ? (int)$queryParams['officeId'] : null,
            'serviceId' => isset($queryParams['serviceId']) ? (int)$queryParams['serviceId'] : null,
            'serviceCounts' => isset($queryParams['serviceCount']) 
                ? array_map('trim', explode(',', $queryParams['serviceCount'])) 
                : [],
            'startDate' => isset($queryParams['startDate']) ? (string)$queryParams['startDate'] : null,
            'endDate' => isset($queryParams['endDate']) ? (string)$queryParams['endDate'] : null
        ];
    }

    private function validateClientData(object $data): array
    {
        return ValidationService::validateGetBookableFreeDays(
            $data->officeId,
            $data->serviceId,
            $data->startDate,
            $data->endDate,
            $data->serviceCounts
        );
    }

    private function getAvailableDays(object $data): array|AvailableDays
    {
        return ZmsApiFacadeService::getBookableFreeDays(
            $data->officeId,
            $data->serviceId,
            $data->serviceCounts,
            $data->startDate,
            $data->endDate
        );
    }
}