<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Models\Collections\ServiceList;
use BO\Zmscitizenapi\Services\ValidationService;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ServicesByOfficeList extends BaseController
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
            $result = $this->getServicesByOffice($clientData);
            
            if (is_array($result) && !empty($result['errors'])) {
                $statusCode = ErrorMessages::getHighestStatusCode($result['errors']);
                return $this->createJsonResponse($response, $result, $statusCode);
            }

            return $result instanceof ServiceList
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
            'officeId' => isset($queryParams['officeId']) && is_numeric($queryParams['officeId'])
                ? (int)$queryParams['officeId']
                : null
        ];
    }

    private function validateClientData(object $clientData): array
    {
        return ValidationService::validateGetServicesByOfficeId($clientData->officeId);
    }

    private function getServicesByOffice(object $clientData): array|ServiceList
    {
        return ZmsApiFacadeService::getServicesByOfficeId($clientData->officeId);
    }
}