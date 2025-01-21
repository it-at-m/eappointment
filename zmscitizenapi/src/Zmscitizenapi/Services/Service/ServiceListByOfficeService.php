<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Service;

use BO\Zmscitizenapi\Models\Collections\ServiceList;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;

class ServiceListByOfficeService
{
    public function getServiceList(array $queryParams): ServiceList|array
    {
        $clientData = $this->extractClientData($queryParams);

        $errors = $this->validateClientData($clientData);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        return $this->getServicesByOffice($clientData);
    }

    private function extractClientData(array $queryParams): object
    {
        return (object) [
            'officeId' => isset($queryParams['officeId']) && is_numeric($queryParams['officeId'])
                ? (int) $queryParams['officeId']
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