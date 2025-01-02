<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Services\ValidationService;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AvailableAppointmentsList extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $clientData = $this->extractClientData($request->getQueryParams());
        
        $errors = $this->validateClientData($clientData);
        if (!empty($errors['errors'])) {
            $statusCode = ErrorMessages::getHighestStatusCode($errors['errors']);
            return $this->createJsonResponse($response, $errors, $statusCode);
        }

        try {
            $result = $this->getAvailableAppointments($clientData);
            
            if (!empty($result['errors'])) {
                $statusCode = ErrorMessages::getHighestStatusCode($result['errors']);
                return $this->createJsonResponse($response, $result, $statusCode);
            }

            return $this->createJsonResponse($response, $result->toArray(), 200);
            
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
            'date' => $queryParams['date'] ?? null,
            'officeId' => isset($queryParams['officeId']) ? (int)$queryParams['officeId'] : null,
            'serviceIds' => isset($queryParams['serviceId']) 
                ? array_map('trim', explode(',', $queryParams['serviceId'])) 
                : [],
            'serviceCounts' => isset($queryParams['serviceCount']) 
                ? array_map('trim', explode(',', $queryParams['serviceCount'])) 
                : []
        ];
    }

    private function validateClientData(object $data): array
    {
        return ValidationService::validateGetAvailableAppointments(
            $data->date,
            $data->officeId,
            $data->serviceIds,
            $data->serviceCounts
        );
    }

    private function getAvailableAppointments(object $data): mixed
    {
        return ZmsApiFacadeService::getAvailableAppointments(
            $data->date,
            $data->officeId,
            $data->serviceIds,
            $data->serviceCounts
        );
    }
}