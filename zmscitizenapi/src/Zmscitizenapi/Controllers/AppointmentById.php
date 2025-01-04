<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Services\ValidationService;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;
use BO\Zmscitizenapi\Models\ThinnedProcess;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AppointmentById extends BaseController
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
            $result = $this->getAppointment($clientData->processId, $clientData->authKey);
            
            if (is_array($result) && !empty($result['errors'])) {
                $statusCode = ErrorMessages::getHighestStatusCode($result['errors']);
                return $this->createJsonResponse($response, $result, $statusCode);
            }

            return $result instanceof ThinnedProcess
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
            'processId' => isset($queryParams['processId']) && is_numeric($queryParams['processId']) 
                ? (int) $queryParams['processId'] 
                : null,
            'authKey' => isset($queryParams['authKey']) && is_string($queryParams['authKey']) && trim($queryParams['authKey']) !== '' 
                ? $queryParams['authKey'] 
                : null
        ];
    }

    private function validateClientData(object $data): array
    {
        return ValidationService::validateGetProcessById($data->processId, $data->authKey);
    }

    private function getAppointment(?int $processId, ?string $authKey): ThinnedProcess|array
    {
        return ZmsApiFacadeService::getThinnedProcessById($processId, $authKey);
    }
}