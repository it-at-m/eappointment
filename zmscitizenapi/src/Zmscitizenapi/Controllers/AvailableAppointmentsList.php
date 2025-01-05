<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Models\AvailableAppointments;
use BO\Zmscitizenapi\Services\ValidationService;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AvailableAppointmentsList extends BaseController
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
            $result = $this->getAvailableAppointments($clientData);

            if (is_array($result) && !empty($result['errors'])) {
                $statusCode = ErrorMessages::getHighestStatusCode($result['errors']);
                return $this->createJsonResponse($response, $result, $statusCode);
            }

            return $result instanceof AvailableAppointments
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
            'date' => isset($queryParams['date']) ? (string) $queryParams['date'] : null,
            'officeId' => isset($queryParams['officeId']) ? (int) $queryParams['officeId'] : null,
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

    private function getAvailableAppointments(object $data): array|AvailableAppointments
    {
        return ZmsApiFacadeService::getAvailableAppointments(
            $data->date,
            $data->officeId,
            $data->serviceIds,
            $data->serviceCounts
        );
    }
}